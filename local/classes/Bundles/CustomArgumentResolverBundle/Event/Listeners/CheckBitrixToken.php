<?php

namespace Local\Bundles\CustomArgumentResolverBundle\Event\Listeners;

use Local\Bundles\CustomArgumentResolverBundle\Event\Exceptions\WrongSecurityTokenException;
use Local\Bundles\CustomArgumentResolverBundle\Event\Interfaces\OnControllerRequestHandlerInterface;
use Local\Bundles\CustomArgumentResolverBundle\Event\Traits\UseTraitChecker;
use Local\Controllers\Traits\ValidatorTraits\BitrixSecurityTokenTrait;
use Symfony\Component\HttpKernel\Event\ControllerEvent;

/**
 * Class CheckBitrixToken
 * @package Local\SymfonyTools\Events\OnControllerRequest\Subscribers
 *
 * @since 11.09.2020
 * @since 05.12.2020 Убрал EventSubscriberInterface, чтобы предотвратить дублирующий запуск листенера.
 */
class CheckBitrixToken implements OnControllerRequestHandlerInterface
{
    use UseTraitChecker;

    /**
     * Обработчик события kernel.controller.
     *
     * Валидация токена Битрикс при наличии трэйта BitrixSecurityTokenTrait в контроллере.
     * Предполагается, что токен прилетит в POST запросе, поле - sessid.
     *
     * @param ControllerEvent $event Объект события.
     *
     * @return void
     *
     * @throws WrongSecurityTokenException Невалидный токен.
     */
    public function handle(ControllerEvent $event): void
    {
        $controller = $event->getController();

        if (!is_array($controller) || !$event->isMasterRequest()) {
            return;
        }

        /**
         * needCheckToken() -> BitrixSecurityTokenTrait.
         */
        if (!$this->useTrait($event, BitrixSecurityTokenTrait::class)
            ||
            (is_object($controller[0]) && !$controller[0]->needCheckToken())
        ) {
            return;
        }

        $token = $event->getRequest()->request->get('sessid');

        if (empty($token)
            ||
            !check_bitrix_sessid()
        ) {
            throw new WrongSecurityTokenException('Secirity error: invalid Bitrix token.');
        }
    }
}
