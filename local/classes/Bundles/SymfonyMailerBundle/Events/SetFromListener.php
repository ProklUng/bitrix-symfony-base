<?php

namespace Local\Bundles\SymfonyMailerBundle\Events;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mailer\Event\MessageEvent;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

/**
 * Class SetFromListener
 * Поле From писем по умолчанию.
 * @package Local\Bundles\SymfonyMailerBundle\Events
 *
 * @since 01.11.2020
 */
class SetFromListener implements EventSubscriberInterface
{
    /** @var string $defaultFromEmail Дефолтное поле from. */
    private $defaultFromEmail;
    /** @var string $defaultFromTitle Дефолтное поле from. Заголовок. */
    private $defaultFromTitle;

    /**
     * SetFromListener constructor.
     *
     * @param string $defaultFromEmail Дефолтное поле from.
     * @param string $defaultFromTitle Дефолтное поле from. Заголовок.
     */
    public function __construct(
        string $defaultFromEmail,
        string $defaultFromTitle
    ) {
        $this->defaultFromEmail = $defaultFromEmail;
        $this->defaultFromTitle = $defaultFromTitle;
    }

    /**
     * Обработчик события.
     *
     * @param MessageEvent $event Объект события.
     */
    public function onMessage(MessageEvent $event): void
    {
        $email = $event->getMessage();
        if (!$email instanceof Email) {
            return;
        }

        $email->from(new Address($this->defaultFromEmail, $this->defaultFromTitle));
    }

    /**
     * Событие.
     *
     * @return string[]
     */
    public static function getSubscribedEvents() : array
    {
        return [
            MessageEvent::class => 'onMessage',
        ];
    }
}
