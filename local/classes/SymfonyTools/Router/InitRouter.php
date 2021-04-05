<?php

namespace Local\SymfonyTools\Router;

use CHTTP;
use Exception;
use Local\SymfonyTools\Framework\Controllers\ErrorControllerInterface;
use Local\SymfonyTools\Framework\Listeners\StringResponseListener;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Controller\ArgumentResolverInterface;
use Symfony\Component\HttpKernel\Controller\ControllerResolverInterface;
use Symfony\Component\HttpKernel\EventListener\ErrorListener;
use Symfony\Component\HttpKernel\EventListener\ResponseListener;
use Symfony\Component\HttpKernel\EventListener\RouterListener;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;

/**
 * Class InitRouter
 * @package Local\Router
 *
 * @since 07.09.2020
 * @since 09.09.2020 Проброс Error Controller снаружи.
 * @since 11.09.2020 Переработка.
 * @since 16.09.2020 Доработка. RequestContext.
 * @since 30.10.2020 ArgumentResolver пробрасывается снаружи.
 * @since 19.11.2020 RequestStack пробрасывается снаружи.
 * @since 06.03.2021 Инициация события kernel.terminate.
 */
class InitRouter
{
    /**
     * @var RouteCollection[] $bundlesRoutes Роуты бандлов.
     */
    private static $bundlesRoutes = [];

    /**
     * @var RouteCollection $routeCollection Коллекция роутов.
     */
    private $routeCollection;

    /**
     * @var Request $request Request.
     */
    private $request;

    /**
     * @var ErrorControllerInterface $errorController Error Controller.
     */
    private $errorController;

    /**
     * @var EventDispatcherInterface $dispatcher Диспетчер событий.
     */
    private $dispatcher;

    /**
     * @var ControllerResolverInterface $controllerResolver Разрешитель контроллеров.
     */
    private $controllerResolver;

    /**
     * @var ArgumentResolverInterface $argumentResolver Argument Resolver.
     */
    protected $argumentResolver;

    /**
     * @var RequestStack $requestStack RequestStack.
     */
    protected $requestStack;

    /** @var array $defaultSubscribers Подписчики на события по умолчанию. */
    private $defaultSubscribers;

    /**
     * InitRouter constructor.
     *
     * @param RouteCollection             $routeCollection    Коллекция роутов.
     * @param ErrorControllerInterface    $errorController    Error controller.
     * @param EventDispatcher             $dispatcher         Event dispatcher.
     * @param ControllerResolverInterface $controllerResolver Controller resolver.
     * @param ArgumentResolverInterface   $argumentResolver   Argument resolver.
     * @param RequestStack                $requestStack       Request stack.
     * @param Request|null                $request            Request.
     *
     * @since 16.09.2020 Инициализация RequestContext.
     * @since 19.11.2020 RequestStack пробрасывается снаружи.
     */
    public function __construct(
        RouteCollection $routeCollection,
        ErrorControllerInterface $errorController,
        EventDispatcher $dispatcher,
        ControllerResolverInterface $controllerResolver,
        ArgumentResolverInterface $argumentResolver,
        RequestStack $requestStack,
        Request $request = null
    ) {
        $this->request = $request ?? Request::createFromGlobals();
        $this->errorController = $errorController;
        $this->dispatcher = $dispatcher;
        $this->controllerResolver = $controllerResolver;
        $this->argumentResolver = $argumentResolver;
        $this->routeCollection = $routeCollection;
        $this->requestStack = $requestStack;
        $this->requestStack->push($this->request);

        // RequestContext init.
        $requestContext = new RequestContext();
        $requestContext->fromRequest($this->request);

        // Роуты бандлов.
        $this->mixRoutesBundles();

        $matcher = new UrlMatcher($this->routeCollection, $requestContext);
        // Подписчики на события по умолчанию.
        $this->defaultSubscribers = [
            new RouterListener($matcher, $this->requestStack),
            new StringResponseListener(),
            new ErrorListener(
                [$this->errorController, 'exceptionAction']
            ),
            new ResponseListener('UTF-8')
        ];

        $this->addSubscribers($this->defaultSubscribers);
    }

    /**
     * Процесс обработки роутов.
     *
     * @return void
     * @throws Exception Ошибки роутера.
     */
    public function handle(): void
    {
        // Setup framework kernel
        $framework = new HttpKernel(
            $this->dispatcher,
            $this->controllerResolver,
            null,
            $this->argumentResolver
        );

        try {
            $response = $framework->handle($this->request);
            // Инициирует событие kernel.terminate.
            $framework->terminate($this->request, $response);
        } catch (Exception $e) {
            return;
        }

        // Handle if no route match found
        if ($response->getStatusCode() === 404) {
            // If no route found do noting and let continue.
            return;
        }

        // Перебиваю битриксовый 404 для роутов.
        CHTTP::SetStatus('200 OK');

        // Send the response to the browser and exit app.
        $response->send();

        exit;
    }

    /**
     * Подмес роутов бандлов к общим роутам.
     *
     * @return void
     */
    public function mixRoutesBundles() : void
    {
        if (!self::$bundlesRoutes) {
            return;
        }

        foreach (self::$bundlesRoutes as $collection) {
            if ($collection instanceof RouteCollection) {
                $this->routeCollection->addCollection($collection);
            }
        }
    }

    /**
     * Добавить роуты бандлов.
     *
     * @param RouteCollection $routeCollection Коллкция роутов.
     *
     * @return void
     */
    public static function addRoutesBundle(RouteCollection $routeCollection) : void
    {
        self::$bundlesRoutes[] = $routeCollection;
    }

    /**
     * Кучно добавить слушателей событий.
     *
     * @param array $subscribers Подписчики.
     *
     * @return void
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
     * Задать Request.
     *
     * @param Request $request Request.
     *
     * @return InitRouter
     */
    public function setRequest(Request $request): self
    {
        $this->request = $request;

        return $this;
    }
}
