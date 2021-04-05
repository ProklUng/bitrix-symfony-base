<?php

namespace Local\Bundles\BitrixOgGraphBundle\Services;

use Symfony\Component\String\UnicodeString;

/**
 * Class AbstractProcessor
 * @package Local\Bundles\BitrixOgGraphBundle\Services
 *
 * @since 19.02.2021
 */
class AbstractProcessor
{
    /**
     * @const int OG_IMAGE_WIDTH Макcимальная ширина картинки og:image.
     */
    protected const OG_IMAGE_WIDTH = 1200;

    /**
     * @const int OG_IMAGE_WIDTH Макcимальная высота картинки og:image.
     */
    protected const OG_IMAGE_HEIGHT = 627;

    /**
     * @const int MAX_LENGTH_OG_DESCRIPTION Макcимальная длина текста og:description.
     */
    protected const MAX_LENGTH_OG_DESCRIPTION = 200;

    /**
     * Проверка - HTTP или HTTPS.
     *
     * @return boolean
     */
    protected function isSecureConnection(): bool
    {
        return
            (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || $_SERVER['SERVER_PORT'] === 443;
    }

    /**
     * Получить полный (включая https, домен) путь к канонической странице.
     *
     * @param string $url Укороченный URL (без домена).
     *
     * @return string
     */
    protected function getFullUrl(string $url = ''): string
    {
        if (!$url) {
            return '';
        }

        $typeHttp = $this->isSecureConnection() ? 'https://' : 'http://';

        return $typeHttp . $_SERVER['HTTP_HOST'] . $url;
    }

    /**
     * Отрезать текст по максимальному ограничению длины.
     *
     * @param string $text Текст.
     *
     * @return string
     */
    protected function cutDescription(string $text) : string
    {
        $string = new UnicodeString($text);

        return $string->collapseWhitespace()
                      ->truncate(self::MAX_LENGTH_OG_DESCRIPTION, '...');
    }
}
