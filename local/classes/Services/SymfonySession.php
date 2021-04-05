<?php

namespace Local\Services;

use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Session\Storage\PhpBridgeSessionStorage;

/**
 * Class SymfonySession
 * @package Local\Services
 *
 * @since 28.10.2020 Change to SessionInterface.
 * @since 18.02.2021 Support flash messages.
 */
class SymfonySession
{
    /**
     * @var Session|null $session Сессии Symfony.
     */
    private $session;

    /**
     * Инициализация.
     *
     * @return void
     */
    public function init(): void
    {
        $this->session = new Session(new PhpBridgeSessionStorage());
        // Если сессия не запущена, то запустить и мигрировать.
        if (!$this->session->isStarted()) {
            $this->session->start();
            $this->migrateSession();
        }
    }

    /**
     * Объект Session.
     *
     * @return SessionInterface
     *
     * @since 28.10.2020 Change to interface.
     */
    public function session(): SessionInterface
    {
        if ($this->session === null) {
            $this->init();
        }

        return $this->session;
    }

    /**
     * FlashBag.
     *
     * @return FlashBagInterface
     */
    public function getFlashBag() : FlashBagInterface
    {
        if ($this->session === null) {
            $this->init();
        }

        return $this->session->getFlashBag();
    }

    /**
     * Флэш сообщения из сессии.
     *
     * @param string $message ID сообщения.
     *
     * @return array
     */
    public function getFlashMessages(string $message): array
    {
        if ($this->session === null) {
            $this->init();
        }

        return $this->session->getFlashBag()->get($message);
    }

    /**
     * Установить - получить значение ключа.
     *
     * @param string $key   Ключ.
     * @param mixed  $value Значение.
     *
     * @return null|mixed
     */
    public function value(string $key, $value = null)
    {
        if ($this->session === null) {
            $this->init();
        }

        if ($value === null) {
            return $this->session->get($key);
        }

        $this->session->set($key, $value);

        return null;
    }

    /**
     * Миграция $_SESSION в сессии Symfony.
     *
     * @return void
     */
    public function migrateSession() : void
    {
        foreach ($_SESSION as $key => $item) {
            $this->value($key, $item);
        }
    }

    /**
     * Csrf токен приложения.
     *
     * @return string
     */
    public function csrfTokenApp() : string
    {
        if ($this->session === null) {
            $this->init();
        }

        return (string)$this->session->get('csrf_token');
    }
}
