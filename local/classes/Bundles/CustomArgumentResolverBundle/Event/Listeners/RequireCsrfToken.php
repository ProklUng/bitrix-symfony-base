<?php

namespace Local\Bundles\CustomArgumentResolverBundle\Event\Listeners;

use Doctrine\Common\Annotations\Reader;
use Local\Bundles\CustomArgumentResolverBundle\Annotation\CsrfTokenRequired;
use Local\Bundles\CustomArgumentResolverBundle\Event\Exceptions\WrongCsrfException;
use Local\Bundles\CustomArgumentResolverBundle\Event\Interfaces\OnControllerRequestHandlerInterface;
use ReflectionException;
use ReflectionMethod;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

/**
 * Class RequireCsrfToken
 * Handles @CsrfTokenRequired annotation.
 * @package Local\Bundles\CustomArgumentResolverBundle\Event\Listeners
 *
 * @since 13.12.2020
 *
 * @internal Пример аннотации action контроллера: @CsrfTokenRequired(id="app")
 * id - id токена csrf.
 */
class RequireCsrfToken implements OnControllerRequestHandlerInterface
{
    /**
     * @var Reader $reader Читатель аннотаций.
     */
    private $reader;

    /**
     * @var CsrfTokenManagerInterface $csrfTokenManager Менеджер CSRF токенов.
     */
    private $csrfTokenManager;

    /**
     * RequireCsrfToken constructor.
     *
     * @param Reader                    $reader           Читатель аннотаций.
     * @param CsrfTokenManagerInterface $csrfTokenManager Менеджер CSRF токенов.
     */
    public function __construct(Reader $reader, CsrfTokenManagerInterface $csrfTokenManager)
    {
        $this->reader = $reader;
        $this->csrfTokenManager = $csrfTokenManager;
    }

    /**
     * Обработчик.
     *
     * @param ControllerEvent $event Событие kernel.controller.
     *
     * @return void
     *
     * @throws WrongCsrfException  Невалидный токен.
     * @throws ReflectionException Ошибки рефлексии.
     */
    public function handle(ControllerEvent $event): void
    {
        $controller = $event->getController();
        $action = '__invoke';

        if (is_array($controller)) {
            [$controller, $action] = $event->getController();
        }

        $method = new ReflectionMethod($controller, $action);
        $annotation = $this->reader->getMethodAnnotation($method, CsrfTokenRequired::class);

        if (!$annotation instanceof CsrfTokenRequired) {
            return;
        }

        $request = $event->getRequest();
        $token = null;

        if ($annotation->header) {
            $token = $request->headers->get($annotation->header);
        }

        if ($token === null && $annotation->param) {
            $token = $request->get($annotation->param);
        }

        $csrfToken = new CsrfToken($annotation->id, $token);

        if (!$this->csrfTokenManager->isTokenValid($csrfToken)) {
            throw new WrongCsrfException('Invalid csrf token.');
        }
    }
}