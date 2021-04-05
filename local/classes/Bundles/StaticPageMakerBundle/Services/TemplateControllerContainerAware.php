<?php

namespace Local\Bundles\StaticPageMakerBundle\Services;

use Local\Bundles\StaticPageMakerBundle\Services\ContextProcessors\DefaultContextProcessorsBag;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

/**
 * Class TemplateControllerContainerAware
 * @package Local\Bundles\StaticPageMakerBundle\Services
 *
 * @since 03.11.2020
 *
 * @see https://github.com/symfony/symfony/blob/5.x/src/Symfony/Bundle/FrameworkBundle/Controller/TemplateController.php
 * Из-за ограничений версии Symfony 4.4 приходится выносить класс локально.
 */
class TemplateControllerContainerAware extends TemplateController
{
    use ContainerAwareTrait;

    /**
     * @var DefaultContextProcessorsBag $contextProcessorsBag Набор процессоров контента по умолчанию.
     */
    private $contextProcessorsBag;

    /**
     * TemplateControllerContainerAware constructor.
     *
     * @param DefaultContextProcessorsBag $contextProcessorsBag  Набор процессоров контента по умолчанию.
     * @param Environment|null            $twig                  Твиг.
     */
    public function __construct(
        DefaultContextProcessorsBag $contextProcessorsBag,
        Environment $twig = null
    ) {
        parent::__construct($twig);
        $this->contextProcessorsBag = $contextProcessorsBag;
    }

    /**
     * Renders a template.
     *
     * @param string       $template  The template name.
     * @param integer|null $maxAge    Max age for client caching.
     * @param integer|null $sharedAge Max age for shared (proxy) caching.
     * @param boolean|null $private   Whether or not caching should apply for client caches only.
     * @param array        $context   The context (arguments) of the template.
     *
     * @return Response
     *
     * @throws LoaderError  Ошибки Твига.
     * @throws RuntimeError Ошибки Твига.
     * @throws SyntaxError  Ошибки Твига.
     */
    public function templateAction(
        string $template,
        int $maxAge = null,
        int $sharedAge = null,
        bool $private = null,
        array $context = []
    ): Response {

        $context = $this->resolveServices($context);
        $context = $this->applyProcessors($context);

        return parent::templateAction(
            $template,
            $maxAge,
            $sharedAge,
            $private,
            $context
        );
    }

    /**
     * Разрешить сервисы из контейнера.
     *
     * @param array $context Контекст.
     *
     * @return array
     */
    private function resolveServices(array $context): array
    {
        $result = $context;

        foreach ($context as $key => $item) {
            if (is_array($item)) {
                $result[$key] = $this->resolveServices($item);
                continue;
            }

            $resolvedService = $this->resolveFromContainer($item);

            if ($resolvedService !== null) {
                $result[$key] = $resolvedService;
            }
        }

        return $result;
    }

    /**
     * Разрешить все, что можно из контейнера.
     *
     * @param mixed $argItem Аргумент.
     *
     * @return mixed
     *
     */
    private function resolveFromContainer($argItem)
    {
        if (!$argItem || is_object($argItem)) {
            return $argItem;
        }

        $resolvedVariable = false;

        if (strpos($argItem, '%') === 0) {
            $containerVar = str_replace('%', '', $argItem);

            // Есть такой параметр в контейнере - действуем.
            if ($this->container->hasParameter($containerVar)) {
                $resolvedVarValue = $this->container->getParameter($containerVar);
                $resolvedVariable = true;

                if ($this->container->has((string)$resolvedVarValue)) {
                    $resolvedVarValue = '@' . $resolvedVarValue;
                }

                $argItem = $resolvedVarValue;
            }

            // Продолжаем дальше, потому что в переменной может быть алиас сервиса.
        }

        // Если использован алиас сервиса, то попробовать получить его из контейнера.
        if (strpos($argItem, '@') === 0) {
            $serviceId = ltrim($argItem, '@');

            $resolvedService = null;

            if ($this->container->has($serviceId)) {
                $resolvedService = $this->container->get(
                    $serviceId
                );
            } elseif (class_exists($serviceId)){
                $resolvedService = new $serviceId;
            }

            return $resolvedService;
        }

        return !$resolvedVariable ? null : $argItem;
    }

    /**
     * Применить процессоры контекста.
     *
     * @param array $context Контекст.
     *
     * @return array Модифицированный контекст.
     */
    private function applyProcessors(array $context) : array
    {
        if (!array_key_exists('_processors', $context)
            &&
            !$this->contextProcessorsBag->getProcessors()
        ) {
            return $context;
        }

        $processors = (array)$context['_processors'];

        // Если задан в конфиге бандла ID инфоблока, то пускать в дело процессоры по умолчанию.
        if ($this->container->hasParameter('static_page_maker.seo_iblock_id')
            &&
            (int)$this->container->getParameter('static_page_maker.seo_iblock_id') > 0) {
            $processors = array_merge($this->contextProcessorsBag->getProcessors(), $processors);
        }

        /**
         * @var ContextProcessorInterface $processor Процессор контекста.
         */
        foreach ($processors as $processor) {
            // Проверка соответствия интерфейс-класс.
            $interfaces = class_implements($processor);
            if ($interfaces && in_array(ContextProcessorInterface::class, $interfaces, true)) {
                $processor->setContext($context);
                $context = $processor->handle();
            }
        }

        return $context;
    }
}
