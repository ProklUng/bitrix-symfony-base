<?php

namespace Local\Seo;

use CHTTP;
use Local\Facades\KernelFacade;
use Local\Facades\LastModifiedFacade;

/**
 * Class CMainHandlers
 * @package Local\SEO
 */
class CMainHandlers
{
    /**
     * Обработчик LastModified заголовков.
     *
     * @return boolean
     */
    public static function checkIfModifiedSince() : bool
    {
         // Для админов - выключить.
        if ($GLOBALS['USER']->IsAdmin()
            ||
            !KernelFacade::isProduction()
        ) {
            return true;
        }

        /** @noinspection PhpUndefinedMethodInspection */
        /** @var integer $iLastModified Таймстамп последнего изменения страницы. */
        $iLastModified = LastModifiedFacade::getNewestModified();

        if (!empty($iLastModified) && !headers_sent()) {
            header('Cache-Control: public');
            header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $iLastModified) . ' GMT');

            if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])
                && strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) >= $iLastModified) {
                $GLOBALS['APPLICATION']->RestartBuffer();
                CHTTP::SetStatus('304 Not Modified');
                exit();
            }
        }

        return false;
    }
}
