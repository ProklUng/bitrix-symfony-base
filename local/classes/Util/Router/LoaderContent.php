<?php

namespace Local\Util\Router;

use Symfony\Component\HttpFoundation\Request;

/**
 * Class LoaderContent
 * Загрузчик контента.
 * @package Local\Util\Router
 */
class LoaderContent
{
    /**
     * Загрузить контент страницы.
     *
     * @param string  $sXmlCode  XML_ID свойства 'MENU_SHOW_OPTIONS'.
     * @param Request $obRequest Объект Request.
     *
     * @return string
     */
    public static function getContentPage(string $sXmlCode, Request $obRequest) : string
    {
        // Пробрасываем куки.
        $arOptions = ['http' => ['header'=> 'Cookie: ' . $obRequest->server->get('HTTP_COOKIE')."\r\n"]];
        $context = stream_context_create($arOptions);

        session_write_close(); // Unlock the file (иначе не получится подменить страницу)

        // Протокол.
        $sProtocol = ($obRequest->server->get('HTTPS') && $obRequest->server->get('HTTPS') !== 'off') ? 'https' : 'http';
        // Путь к файлу.
        $sUrlFile = $sProtocol.'://'.$obRequest->server->get('SERVER_NAME'). $sXmlCode . '/index.php';
        // Получить контент.
        $sContent = @file_get_contents($sUrlFile, false, $context);

        session_start();

        return $sContent;
    }
}
