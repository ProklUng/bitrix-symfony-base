<?php

namespace Local\Bundles\CustomArgumentResolverBundle\Event\Listeners;

use Exception;
use Local\Bundles\CustomArgumentResolverBundle\Event\Exceptions\WrongCsrfException;
use Local\Bundles\CustomArgumentResolverBundle\Event\Exceptions\WrongSecurityTokenException;
use Local\Bundles\CustomArgumentResolverBundle\Event\Interfaces\OnControllerRequestHandlerInterface;
use Local\Bundles\CustomArgumentResolverBundle\Event\Traits\UseTraitChecker;
use Local\Bundles\CustomArgumentResolverBundle\Event\Traits\ValidatorTraits\SecurityTokenTrait;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManager;

/**
 * Class SecurityToken
 * @package Local\Bundles\CustomArgumentResolverBundle\Event\Listeners
 *
 * @since 10.09.2020
 * @since 11.09.2020 Упрощение.
 * @since 05.12.2020 Убрал EventSubscriberInterface, чтобы предотвратить дублирующий запуск листенера.
 */
class SecurityToken implements OnControllerRequestHandlerInterface
{
    use UseTraitChecker;

    /**
     * @var CsrfTokenManager $csrfRequestHandler Проверка токена.
     */
    private $csrfRequestHandler;

    /**
     * SecurityToken constructor.
     *
     * @param CsrfTokenManager $csrfRequestHandler Проверка токена.
     */
    public function __construct(
        CsrfTokenManager $csrfRequestHandler
    ) {
        $this->csrfRequestHandler = $csrfRequestHandler;
    }

    /**
     * Обработчик события kernel.controller.
     *
     * Валидация токена при наличии трэйта SecurityTokenTrait в контроллере.
     *
     * @param ControllerEvent $event Объект события.
     *
     * @return void
     *
     * @throws WrongSecurityTokenException Ошибки по токену.
     *
     * @since 05.12.2020 Борьба с повторными запусками. Трэйт SupportCheckerCallResolverTrait.
     */
    public function handle(ControllerEvent $event): void
    {
        if (!$this->useTrait($event, SecurityTokenTrait::class)
            ||
            !$event->isMasterRequest()) {
            return;
        }

        $token = $event->getRequest()->request->get('security.token');

        if (!$token) {
            throw new WrongSecurityTokenException('Security error: empty token.');
        }

        /**
         * Валидировать токен, для примера, так.
         *
         * @psalm-suppress PossiblyNullReference
         */
        $bValidToken = $this->csrfRequestHandler->isTokenValid(
            new CsrfToken('app', $token)
        );

        if (!$bValidToken) {
            throw new WrongSecurityTokenException('Security error: Invalid security token.');
        }

        $event->getRequest()->attributes->set('security.token.validated', true);
    }
}
