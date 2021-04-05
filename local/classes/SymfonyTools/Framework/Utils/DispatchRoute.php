<?php

namespace Local\SymfonyTools\Framework\Utils;

use Exception;
use InvalidArgumentException;
use Local\SymfonyTools\Framework\Controllers\ErrorControllerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpKernel\Controller\ArgumentResolverInterface;
use Symfony\Component\HttpKernel\Controller\ControllerResolverInterface;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\EventListener\ErrorListener;
use Symfony\Component\HttpKernel\EventListener\ResponseListener;
use Local\SymfonyTools\Framework\Listeners\StringResponseListener;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;

/**
 * Class DispatchRoute
 * Исполнить роут.
 * @package Local\SymfonyTools\Framework
 *
 * @since 17.09.2020
 * @since 18.09.2020 Доработки.
 * @since 21.10.2020 Убрал лишний catch.
 * @since 31.10.2020 ArgumentResolverInterface пробрасывается снаружи.
 */
class DispatchRoute
{
    /**
     * @var Request $request Request.
     */
    private $request;

    /**
     * @var Response $response Response.
     */
    private $response;

    /**
     * @var EventDispatcherInterface $dispatcher Диспетчер событий.
     */
    private $dispatcher;

    /**
     * @var ControllerResolverInterface $controllerResolver Разрешитель контроллеров.
     */
    private $controllerResolver;

    /**
     * @var RouteCollection
     */
    private $routes;

    /**
     * @var RequestContext
     */
    private $requestContext;

    /**
     * @var ArgumentResolverInterface $argumentResolver Argument Resolver.
     */
    protected $argumentResolver;

    /** @var array $defaultSubscribers Подписчики на события по умолчанию. */
    private $defaultSubscribers;

    /** @var string $method POST|GET. */
    private $method = 'POST';

    /** @var string[] $headers Заголовки запроса. */
    private $headers = ['x-requested-with' => 'XMLHttpRequest'];

    /**
     * DispatchRoute constructor.
     *
     * @param EventDispatcherInterface    $dispatcher         Диспетчер событий.
     * @param ErrorControllerInterface    $errorController    Error controller.
     * @param ControllerResolverInterface $controllerResolver Разрешитель контроллеров.
     * @param ArgumentResolverInterface   $argumentResolver   Argument resolver.
     */
    public function __construct(
        EventDispatcherInterface $dispatcher,
        ErrorControllerInterface $errorController,
        ControllerResolverInterface $controllerResolver,
        ArgumentResolverInterface $argumentResolver
    ) {
        $this->dispatcher = $dispatcher;
        $this->controllerResolver = $controllerResolver;
        $this->argumentResolver = $argumentResolver;

        $this->request = Request::createFromGlobals();
        $this->initContext();

        // Подписчики на события по умолчанию.
        $this->defaultSubscribers = [
            new StringResponseListener(),
            new ErrorListener(
                [$errorController, 'exceptionAction']
            ),
            new ResponseListener('UTF-8')
        ];
    }

    /**
     * POST запрос.
     *
     * @param string $url    URL роута.
     * @param array  $payload Параметры запроса.
     *
     * @return false | Response
     *
     * @since 18.09.2020
     */
    public function post(string $url, array $payload = [])
    {
        $this->setMethod('POST')
            ->initContext()
            ->setParams($payload);

        return $this->dispatch($url);
    }

    /**
     * GET запрос.
     *
     * @param string $url    URL роута.
     * @param array  $payload Параметры запроса.
     *
     * @return false | Response
     *
     * @since 18.09.2020
     */
    public function get(string $url, array $payload = [])
    {
        $this->setMethod('GET')
            ->initContext()
            ->setParams($payload);

        return $this->dispatch($url);
    }

    /**
     * Исполнить роут.
     *
     * @param string|array $url URL роута.
     *
     * @return false | Response
     *
     * @since 31.10.2020 ArgumentResolverInterface пробрасывается снаружи.
     *
     */
    public function dispatch(string $url)
    {
        // Данные на роут.
        $routeInfo = $this->getRouteInfo($url);
        if (empty($routeInfo)) {
            throw new InvalidArgumentException('Роут не существует.');
        }

        // Контроллер.
        $this->request->attributes->add(
            $routeInfo
        );

        $this->addSubscribers($this->defaultSubscribers);

        $framework = new HttpKernel(
            $this->dispatcher,
            $this->controllerResolver,
            null,
            $this->argumentResolver
        );

        try {
            $this->response = $framework->handle($this->request);
        } catch (Exception $e) {
            return false;
        }

        return $this->response;
    }

    /**
     * Инициализировать контекст.
     *
     * @return $this
     */
    public function initContext() : self
    {
        $this->requestContext = new RequestContext($this->request);

        $this->requestContext->setMethod($this->method);

        $this->request->setMethod($this->method);
        $this->request->headers->add(
            $this->headers
        );

        return $this;
    }

    /**
     * Задать параметры Request.
     *
     * @param array $arParams Параметры (лягут в аттрибуты Request).
     *
     * @return $this
     *
     * @since 18.09.2020
     */
    public function setParams(array $arParams): self
    {
        if (empty($arParams)) {
            return $this;
        }

        if ($this->method === 'GET') {
            $this->request->query->add($arParams);
        } else {
            $this->request->request->add($arParams);
        }

        // Переинциализировать RequestContext.
        $this->initContext();

        return $this;
    }

    /**
     * Задать роуты.
     *
     * @param RouteCollection $routes Коллекция роутов.
     *
     * @return DispatchRoute
     */
    public function setRoutes(RouteCollection $routes): self
    {
        $this->routes = $routes;

        return $this;
    }

    /**
     * Способ вызова - POST, GET итд.
     *
     * @param string $method
     *
     * @return $this
     */
    public function setMethod(string $method): self
    {
        $this->method = $method;
        // Переинциализировать RequestContext.
        $this->initContext();

        return $this;
    }

    /**
     * Заголовки запроса.
     *
     * @param array $headers Заголовки.
     *
     * @return $this
     */
    public function setHeaders(array $headers) : self
    {
        $this->headers = $headers;

        // Переинциализировать RequestContext.
        $this->initContext();

        return $this;
    }

    /**
     * Кучно добавить слушателей событий.
     *
     * @param array $subscribers
     */
    private function addSubscribers(array $subscribers = []) : void
    {
        foreach ($subscribers as $subscriber) {
            if (!is_object($subscriber)) {
                continue;
            }
            $this->dispatcher->addSubscriber($subscriber);
        }
    }

    /**
     * Получить информацию о роуте.
     *
     * @param string $uri URL.
     *
     * @return array
     *
     * @since 21.10.2020 Убрал лишний catch.
     */
    private function getRouteInfo(string $uri) : array
    {
        // Удалить служебные роуты.
        $this->routes->remove(['index', 'remove_trailing_slash', 'not-found']);
        $matcher = new UrlMatcher($this->routes, $this->requestContext);

        try {
            return $matcher->match($uri);
        } catch (ResourceNotFoundException | MethodNotAllowedException $e) {
            return [];
        }
    }
}