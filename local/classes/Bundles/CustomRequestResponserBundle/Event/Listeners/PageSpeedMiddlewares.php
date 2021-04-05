<?php

namespace Local\Bundles\CustomRequestResponserBundle\Event\Listeners;

use Local\Bundles\CustomRequestResponserBundle\Event\Interfaces\OnKernelResponseHandlerInterface;
use Local\Bundles\CustomRequestResponserBundle\Services\PageSpeed\AbstractPageSpeed;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

/**
 * Class PageSpeedMiddlewares
 * @package Local\Bundles\CustomRequestResponserBundle\Event\Listeners
 *
 * @since 18.02.2021
 * @since 21.02.2021 Новый параметр - ключ конфига, отвечающий за исключенные middlewares.
 */
class PageSpeedMiddlewares implements OnKernelResponseHandlerInterface
{
    /**
     * @var AbstractPageSpeed[] $middlewaresBag Middlewares.
     */
    protected $middlewaresBag = [];

    /**
     * @var AbstractPageSpeed[] $reserveMiddlewaresBag Резервное хранилище Middlewares.
     */
    private $reserveMiddlewaresBag;

    /**
     * @var array $enabledDisabledMiddlewares Параметры бандла.
     */
    private $enabledDisabledMiddlewares;

    /**
     * @var ContainerInterface $container Контейнер.
     */
    private $container;

    /**
     * PageSpeedMiddlewares constructor.
     *
     * @param ContainerInterface $container           Контейнер.
     * @param array              $params              Параметры бандла.
     * @param string             $keyConfigMiddleware Ключ конфига, отвечающий за исключенные middlewares.
     * @param mixed              ...$middlewares      Middlewares.
     *
     */
    public function __construct(
        ContainerInterface $container,
        array $params,
        string $keyConfigMiddleware,
        ...$middlewares
    ) {
        $this->container = $container;
        $this->enabledDisabledMiddlewares = array_key_exists($keyConfigMiddleware, $params)
                              ? $params[$keyConfigMiddleware] : [];

        $handlers = [];

        foreach ($middlewares as $middleware) {
            $iterator = $middleware->getIterator();
            $handlers[] = iterator_to_array($iterator);
        }

        $this->reserveMiddlewaresBag = $this->middlewaresBag = array_merge($this->middlewaresBag, ...$handlers);

        $this->processConfigMiddleware($this->enabledDisabledMiddlewares);
    }

    /**
     * @inheritDoc
     */
    public function handle(ResponseEvent $event): void
    {
        $request = $event->getRequest();
        $response = $event->getResponse();

        // Фильтрация внешних нативных маршрутов.
        if (!$event->isMasterRequest()
            ||
            $response->getStatusCode() === 404) {
            return;
        }

        $this->routeMiddlewares($request);

        foreach ($this->middlewaresBag as $middleware) {
            if (!$middleware->shouldProcessPageSpeed(
                $request,
                $response
            )) {
                continue;
            }

            $content = $response->getContent();
            $content = $middleware->apply($content);
            $response->setContent($content);
        }
    }

    /**
     * Обработка параметра _request_middlewares в defaults роутера.
     * Если перед названием сервиса стоит !, то эта middleware отключается
     * для этого роута.
     *
     * @param Request $request Request.
     *
     * @return void
     */
    private function routeMiddlewares(Request $request) : void
    {
        $result = [];
        $param = $request->get('_request_middlewares');

        if (!$param || !is_array($param)) {
            return;
        }

        foreach ($param as $middleware) {
            if (strpos($middleware, '!') === 0) {
                // Проверка сервиса на существование проходит при обработке конфигурации
                // в методе processConfigMiddleware.
                $serviceId = ltrim($middleware, '!');
                $result[$serviceId] = true; // Флаг, что middleware - disabled.
                continue;
            }

            $result[$middleware] = false; // Флаг, что middleware - enabled.
        }

        $result = array_merge($this->enabledDisabledMiddlewares, $result);
        $this->middlewaresBag = $this->reserveMiddlewaresBag;

        $this->processConfigMiddleware($result);
    }

    /**
     * Удалить disabled middlewares из набора.
     *
     * @param array $config Конфигурация.
     *
     * @return void
     */
    private function processConfigMiddleware(array $config) : void
    {
        foreach ($config as $key => $disableParam) {
            if ($disableParam === true && $this->container->has($key)) {
                /** @var AbstractPageSpeed $service */
                $service = $this->container->get($key);
                if ($service !== null) {
                    foreach ($this->middlewaresBag as $keyMiddleware => $middleware) {
                        if ($middleware === $service) {
                            unset($this->middlewaresBag[$keyMiddleware]);
                        }
                    }
                }
            }
        }
    }
}
