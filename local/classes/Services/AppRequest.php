<?php

namespace Local\Services;

use Symfony\Component\HttpFoundation\Request;

/**
 * Class AppRequest
 * @package Local\Services
 */
class AppRequest
{
    /**
     * @var Request $request Объект Request.
     */
    private $request;

    /**
     * AppRequest constructor.
     */
    public function __construct()
    {
        $this->initGlobals();
        $this->request = Request::createFromGlobals();
    }

    /**
     * Объект Request.
     *
     * @return Request
     */
    public function getRequest(): Request
    {
        return $this->request;
    }

    /**
     * Установить ключ в массиве $_SERVER.
     *
     * @param string $key   Ключ.
     * @param mixed  $value Значение.
     *
     * @return void
     */
    public function setServer(string $key, $value): void
    {
        $this->request->server->set($key, $value);
    }

    /**
     * DOCUMENT_ROOT.
     *
     * @return string|null
     */
    public function getDocumentRoot() : ?string
    {
        return $this->request->server->get('DOCUMENT_ROOT');
    }

    /**
     * HTTP_HOST.
     *
     * @return string|null
     */
    public function getHttpHost() : ?string
    {
        return $this->request->server->get('HTTP_HOST');
    }

    /**
     * REQUEST_URI.
     *
     * @return string|null
     */
    public function getRequestUri() : ?string
    {
        return $this->request->server->get('REQUEST_URI');
    }

    /**
     * Инициализировать супер-глобальное, если оно еще не инициализировано.
     *
     * @return void
     */
    private function initGlobals() : void
    {
        $_GET = !empty($_GET) ? $_GET : [];
        $_POST = !empty($_POST) ? $_POST : [];
        $_COOKIE = !empty($_COOKIE) ? $_COOKIE : [];
        $_FILES = !empty($_FILES) ? $_FILES : [];
        $_SERVER = !empty($_SERVER) ? $_SERVER : [];
    }
}
