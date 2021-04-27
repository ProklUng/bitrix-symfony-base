<?php

namespace Local\Bundles\SymfonyMiddlewareBundle\ControllersMiddleware;

use Local\Bundles\SymfonyMiddlewareBundle\MiddlewareInterface;
use Prokl\RequestValidatorSanitizer\Validation\Controllers\ValidateableTrait;
use Prokl\RequestValidatorSanitizer\Validation\Exceptions\ValidateErrorException;
use ReflectionClass;
use ReflectionException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Controller\ContainerControllerResolver;

/**
 * Class ExampleMiddleware
 * @package Local\Services\ControllerMiddleware
 *
 * @since 19.11.2020
 */
class ValidatorMiddleware implements MiddlewareInterface
{
    use ValidateableTrait;

    /**
     * @var ContainerInterface $container Контейнер.
     */
    private $container;

    /**
     * ValidateMiddleware constructor.
     *
     * @param ContainerInterface $container Контейнер.
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param Request $request
     * @return Response|null
     *
     * @throws ReflectionException|ValidateErrorException
     */
    public function handle(Request $request): ?Response
    {
        $controllerResolver = new ContainerControllerResolver($this->container);
        $controller = $controllerResolver->getController($request);

        // Метод, содержащий правила валидации.
        $validationRules = 'getRules' . ucfirst($controller[1]);

        if (!method_exists($controller[0], $validationRules)) {
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
            $validationRules
        );

        $this->validate($data, $rules);

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
