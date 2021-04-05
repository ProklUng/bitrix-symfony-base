<?php

namespace Local\Util;

use CHTTP;

/**
 * Class Handler404
 * @package Local\Util
 *
 * @since 15.09.2020
 * @since 11.10.2020 Изменен механизм проверки роутов на более точный.
 */
class Handler404
{
    /**
     * Чтобы Битрикс не рубил вызовы к API через Symfony Router.
     *
     * @return void
     */
    public function apiHandler()
    {
        if (defined("ERROR_404")
            ||
            CHTTP::GetLastStatus() == "404 Not Found"
            &&
            container()->get('route.checker')->isLiveRoute($_SERVER['REQUEST_URI']))
        {
            CHTTP::SetStatus("The HTTP 200 OK");
            @define('ERROR_404', "N");
        }
    }
}
