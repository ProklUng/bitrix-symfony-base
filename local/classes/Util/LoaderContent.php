<?php

namespace Local\Util;

use Symfony\Component\HttpFoundation\Request;

/**
 * Class LoaderContent
 * Загрузчик контента.
 * @package Local\Util
 */
class LoaderContent
{
    /**
     * Загрузить контент страницы.
     *
     * @param Request $obRequest Объект Request.
     * @param string $url
     *
     * @return string
     */
    public function getContentPage(Request $obRequest, string $url) : string
    {
        // Пробрасываем куки.
        $arOptions = [
            'http' =>
                ['header'=> 'Cookie: ' . $obRequest->server->get('HTTP_COOKIE')."\r\n"]
        ];

        $context = stream_context_create($arOptions);

        session_write_close(); // Unlock the file (иначе не получится подменить страницу)

        // Протокол.
        $sProtocol = ($obRequest->server->get('HTTPS')
                      &&
                      $obRequest->server->get('HTTPS') !== 'off') ? 'https' : 'http';

        // Путь к файлу.
        $sUrlFile = $sProtocol.'://'.$obRequest->server->get('SERVER_NAME'). $url;
        // Получить контент.
        $sContent = @file_get_contents($sUrlFile, false, $context);

        session_start();

        return $sContent;
    }
}
