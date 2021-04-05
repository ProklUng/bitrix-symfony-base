<?php

namespace Local\Bundles\SymfonyMailerBundle\Services\Factory;

use Local\Bundles\SymfonyMailerBundle\Services\Contracts\MailerMessageFactoryInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mime\Email;

/**
 * Class MailerMessageFactory
 * @package Local\Bundles\SymfonyMailerBundle\Services\Factory
 *
 * @since 04.01.2021
 */
class MailerMessageFactory implements MailerMessageFactoryInterface
{
    /**
     * @var array $from Поле from.
     */
    private $from;

    /**
     * @var array $cc Поле cc.
     */
    private $cc;

    /**
     * @var array $bcc Поле bcc.
     */
    private $bcc;

    /**
     * @var array $replyTo Поле reply-to.
     */
    private $replyTo;

    /**
     * @var array $headers Заголовки письма.
     */
    private $headers;

    /**
     * @var string $documentRoot DOCUMENT_ROOT.
     */
    private $documentRoot;

    /**
     * MailerMessageFactory constructor.
     *
     * @param string $documentRoot    DOCUMENT_ROOT.
     * @param array  $defaultSettings Настройки по умолчанию.
     */
    public function __construct(
        string $documentRoot,
        array $defaultSettings = []
    ) {
        $this->from = $this->extractArrayFromConfigs('from', $defaultSettings);
        $this->cc = $this->extractArrayFromConfigs('cc', $defaultSettings);
        $this->bcc = $this->extractArrayFromConfigs('bcc', $defaultSettings);
        $this->replyTo = $this->extractArrayFromConfigs('replyTo', $defaultSettings);
        $this->headers = $this->extractAssociativeArrayFromConfigs('headers', $defaultSettings);

        $this->documentRoot = $documentRoot;
    }

    /**
     * {@inheritDoc}
     */
    public function createMessage($to, string $subject, string $body, array $attachments = []): Email
    {
        $email = new Email();

        $this->setupDefaultSettings($email);

        array_map([$email, 'addTo'], is_array($to) ? $to : [$to]);
        $email->subject($subject);
        $email->text($body);

        // Аттачменты.
        if ($attachments) {
            foreach ($attachments as $nameAttachment => $attachment) {
                $email->attachFromPath(
                    $this->documentRoot.$attachment,
                    is_string($nameAttachment) ? $nameAttachment : null
                );
            }
        }

        return $email;
    }

    /**
     * {@inheritDoc}
     */
    public function createMessageTemplate(
        $to,
        string $subject,
        string $template,
        array $context = [],
        array $attachments = []
    ): TemplatedEmail {
        $email = new TemplatedEmail();

        $this->setupDefaultSettings($email);

        array_map([$email, 'addTo'], is_array($to) ? $to : [$to]);

        $email->subject($subject);
        $email->htmlTemplate($template);
        $email->context($context);

        // Аттачменты.
        if ($attachments) {
            foreach ($attachments as $nameAttachment => $attachment) {
                $email->attachFromPath(
                    $this->documentRoot.$attachment,
                    is_string($nameAttachment) ? $nameAttachment : null
                );
            }
        }

        return $email;
    }

    /**
     * Sets up default data to set message.
     *
     * @param Email $email
     */
    private function setupDefaultSettings(Email $email): void
    {
        array_map([$email, 'addFrom'], $this->from);
        array_map([$email, 'addCc'], $this->cc);
        array_map([$email, 'addBcc'], $this->bcc);
        array_map([$email, 'addReplyTo'], $this->replyTo);

        foreach ($this->headers as $name => $value) {
            $email->getHeaders()->addTextHeader($name, $value);
        }
    }

    /**
     * Extracts list of settings or settings string from config array.
     *
     * @param string $name Ключ.
     * @param array $configs Конфиг.
     *
     * @return array
     */
    private function extractArrayFromConfigs(string $name, array $configs): array
    {
        if (!array_key_exists($name, $configs) || !$configs[$name]) {
            return [];
        }

        return is_array($configs[$name]) ? array_values($configs[$name]) : [$configs[$name]];
    }

    /**
     * Extracts associative array from configs.
     *
     * @param string $name Ключ.
     * @param array $configs Конфиг.
     *
     * @return array
     */
    private function extractAssociativeArrayFromConfigs(string $name, array $configs): array
    {
        if ((!array_key_exists($name, $configs) || !$configs[$name]) || !is_array($configs[$name])) {
            return [];
        }

        return $configs[$name];
    }
}
