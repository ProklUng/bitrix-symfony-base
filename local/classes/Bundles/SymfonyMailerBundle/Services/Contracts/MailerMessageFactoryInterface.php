<?php

namespace Local\Bundles\SymfonyMailerBundle\Services\Contracts;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mime\Email;

/**
 * Interface MailerMessageFactoryInterface
 * @package Local\Bundles\SymfonyMailerBundle\Servicesl\Contracts
 *
 * @since 04.01.2021
 */
interface MailerMessageFactoryInterface
{
    /**
     * Creates simple text message.
     *
     * @param mixed  $to         Поле to.
     * @param string $subject    Поле subject.
     * @param string $body       Тело письма.
     * @param array $attachments Файлы-аттачменты. Массив ссылок без DOCUMENT_ROOT.
     *                           Если задан ключ текстом - используется в качестве названия.
     *
     * @return Email
     */
    public function createMessage($to, string $subject, string $body, array $attachments = []): Email;

    /**
     * Creates message that uses template.
     *
     * @param mixed  $to          Поле to.
     * @param string $subject     Поле subject.
     * @param string $template    Твиговский шаблон.
     * @param array  $context     Контекст.
     * @param array  $attachments Файлы-аттачменты. Массив ссылок без DOCUMENT_ROOT.
     *                            Если задан ключ текстом - используется в качестве названия.
     *
     * @return TemplatedEmail
     */
    public function createMessageTemplate($to, string $subject, string $template, array $context = [], array $attachments = []): TemplatedEmail;
}
