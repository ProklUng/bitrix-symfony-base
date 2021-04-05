<?php

namespace Local\Seo;

/**
 * Class Clearizer
 * @package Local\SEO
 */
class Clearizer
{
    /**
     * Зачистка HTML комментариев.
     *
     * @param mixed $buffer Буфер.
     *
     * @return void
     */
    public function clearHtmlComments(&$buffer) : void
    {
        global $USER;

        if ($USER->IsAuthorized()) {
            return;
        }

        $buffer = trim(preg_replace(
            [
                '/<!--(?![^<]*noindex)(.*?)-->/s',
                '/<!-(?![^<]*noindex)(.*?)->/s',
                '/<!--(?![^<]*noindex)(.*?)->/s',
            ],
            [
                '', '', ''
            ],
            $buffer
        ));
    }
}
