<?php

namespace Local\Bundles\SymfonyMailerBundle\Services;

use LogicException;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\BodyRendererInterface;
use Symfony\Component\Mime\Email;

/**
 * Class EmailService
 * Сервис отправки писем.
 * @package Local\Services\Email
 *
 * @since 01.11.2020
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
class EmailService
{
    /**
     * @var MailerInterface $mailer Отправитель почты.
     */
    private $mailer;

    /**
     * @var BodyRendererInterface $bodyRenderer Рендер тела письма из Твига.
     */
    private $bodyRenderer;

    /**
     * @var TemplatedEmail|null $email Параметры письма.
     */
    private $email;

    /** @var string|null $template Твиговский шаблон письма. */
    private $template;

    /** @var array $context Твиговский контекст. */
    private $context = [];

    /**
     * EmailService constructor.
     *
     * @param MailerInterface       $mailer       Отправитель почты.
     * @param BodyRendererInterface $bodyRenderer Рендер тела письма из Твига.
     */
    public function __construct(
        MailerInterface $mailer,
        BodyRendererInterface $bodyRenderer
    ) {
        $this->mailer = $mailer;
        $this->bodyRenderer = $bodyRenderer;
    }

    /**
     * Отправить письмо.
     *
     * @throws TransportExceptionInterface Не удалась отправка почты.
     * @throws LogicException              Не инициализированы необходимые параметры.
     *
     * @return boolean
     */
    public function send() : bool
    {
        if ($this->email === null
            ||
            $this->template === null
        ) {
            throw new LogicException(
                'Email params not initialized.'
            );
        }

        $this->email->htmlTemplate($this->template);

        if (count($this->context) > 0) {
            $this->email->context($this->context);
        }

        $this->bodyRenderer->render($this->email);

        $this->mailer->send($this->email);

        return true;
    }

    /**
     * Инстанц DTO письма.
     *
     * @return TemplatedEmail
     */
    public function email(): TemplatedEmail
    {
        if ($this->email === null) {
            $this->email = new TemplatedEmail();
        }

        return $this->email;
    }

    /**
     * Насильно задать параметры письма снаружи.
     *
     * @param TemplatedEmail $email DTO письма.
     *
     * @return $this
     */
    public function setEmail(TemplatedEmail $email) : self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Отправить принудительно.
     *
     * @param Email $message Сообщение.
     *
     * @throws TransportExceptionInterface
     *
     * @since 04.01.2021
     */
    public function sendImmediately(Email $message) : void
    {
        $this->mailer->send($message);
    }

    /**
     * Шаблон письма.
     *
     * @param string $template Шаблон.
     *
     * @return $this
     */
    public function setTemplate(string $template): self
    {
        $this->template = $template;

        return $this;
    }

    /**
     * Контекст письма.
     *
     * @param array $context Контекст.
     *
     * @return $this
     */
    public function setContext(array $context): self
    {
        $this->context = $context;

        return $this;
    }
}
