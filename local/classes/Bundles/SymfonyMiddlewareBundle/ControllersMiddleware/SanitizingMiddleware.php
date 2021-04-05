<?php

namespace Local\Bundles\SymfonyMiddlewareBundle\ControllersMiddleware;

use Local\Bundles\SymfonyMiddlewareBundle\MiddlewareInterface;
use Local\Services\Sanitizing\SanitizableTrait;
use ReflectionClass;
use ReflectionException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Controller\ContainerControllerResolver;

/**
 * Class SanitizingMiddleware
 * Валидация через LaravelValidator.
 * @package App\Middlewares
 *
 * @since 26.11.2020
 */
class SanitizingMiddleware implements MiddlewareInterface
{
    use SanitizableTrait;

    /**
     * @var ContainerInterface $container Контейнер.
     */
    private $container;

    /**
     * SanitizingMiddleware constructor.
     *
     * @param ContainerInterface $container Контейнер.
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param Request $request Request.
     *
     * @return Response|null
     *
     * @throws ReflectionException
     */
    public function handle(Request $request): ?Response
    {
        $controllerResolver = new ContainerControllerResolver($this->container);
        $controller = $controllerResolver->getController($request);

        // Метод, содержащий правила санации.
        $sanationRules = 'getSanitizingRules' . ucfirst($controller[1]);

        if (!method_exists($controller[0], $sanationRules)) {
            return null;
        }

        // Тип запроса.
        $typeRequest = $request->getMethod();

        $data = ($typeRequest !== 'GET') ?
            $request->request->all()
            :
            $request->query->all();

        $rules = $this->callMethod(
            $controller[0],
            $sanationRules
        );

        if (empty($rules)) {
            return null;
        }

        $sanitizedData = $this->sanitize($data, $rules);

        if ($typeRequest !== 'GET') {
            $request->request->replace($sanitizedData);
        } else {
            $request->query->replace($sanitizedData);
        }

        return null;
    }

    /**
     * Вызвать метод.
     *
     * @param mixed  $object      Объект.
     * @param string $name        Метод.
     * @param array  $arArguments Аргументы.
     *
     * @return mixed
     * @throws ReflectionException
     */
    private function callMethod($object, string $name, array $arArguments = [])
    {
        $class = new ReflectionClass($object);
        $method = $class->getMethod($name);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $arArguments);
    }
}
