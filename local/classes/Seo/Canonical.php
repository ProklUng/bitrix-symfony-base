<?php

namespace Local\Seo;

use CBitrixComponent;

/**
 * Class Canonical
 * Вывод канонических ссылок компонента из свойства.
 * @package Local\Lotus
 */
class Canonical
{
    /** @const string TARGET_ID ID отложенной функции */
    private const TARGET_ID = 'canonicalLink';
    /** @const string PAGE_PROPERTY_CANONICAL_NAME Название свойства папки для канонических страниц. */
    private const PAGE_PROPERTY_CANONICAL_NAME = 'canonical-link';
    /**
     * @param CBitrixComponent $obBitrixComponent Объект Битрикс-компонента.
     * @param string           $canonicalLink     Каноническая ссылка.
     *
     * @return void
     */
    public static function passLink(CBitrixComponent $obBitrixComponent, string $canonicalLink = ''): void
    {
        if ($canonicalLink) {
            // Канононические URL из свойства CANONICAL_URL
            $obBitrixComponent->__parent->__template->SetViewTarget(self::TARGET_ID); ?>
            <link rel="canonical" href="<?php echo self::getFullUrl($canonicalLink); ?>"/>
            <?php $obBitrixComponent->__parent->__template->EndViewTarget();
        }
    }

    /**
     * Канонические ссылки для статических страниц. Берутся из
     * свойства папки canonical.
     *
     * @param string $canonicalUrl URL (для списковых страниц).
     *
     * @return void
     */
    public static function staticPage(string $canonicalUrl = ''): void
    {
        if (!$canonicalUrl) {
            $canonicalUrl = $GLOBALS['APPLICATION']->GetDirProperty(self::PAGE_PROPERTY_CANONICAL_NAME);
        }

        if (!$canonicalUrl) {
            $canonicalUrl = $GLOBALS['APPLICATION']->GetPageProperty(self::PAGE_PROPERTY_CANONICAL_NAME);
        }

        if ($canonicalUrl) {
            $sResultLine = sprintf('<link rel="canonical" href="%s" />', self::getFullUrl($canonicalUrl));
            $GLOBALS['APPLICATION']->AddViewContent(self::TARGET_ID, $sResultLine);
        }
    }

    /**
     * Насильно выставить каноническую ссылку.
     *
     * @param string $canonicalUrl Каноническая ссылка.
     *
     * @return void
     */
    public static function forceCanonical(string $canonicalUrl = '') : void
    {
        if (!$canonicalUrl) {
            return;
        }

        $sResultLine = sprintf('<link rel="canonical" href="%s" />', $canonicalUrl);
        $GLOBALS['APPLICATION']->AddViewContent(self::TARGET_ID, $sResultLine);
    }

    /**
     * Вывод канонической ссылки в header.
     *
     * @return void
     */
    public static function show(): void
    {
        $GLOBALS['APPLICATION']->ShowViewContent(self::TARGET_ID);
    }

    /**
     * Проверка - HTTP или HTTPS.
     *
     * @return boolean
     */
    private static function isSecureConnection(): bool
    {
        return
            (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || $_SERVER['SERVER_PORT'] == 443;
    }

    /**
     * Получить полный (включая https, домен) путь к канонической странице.
     *
     * @param string $url Укороченный URL (без домена).
     *
     * @return string
     */
    private static function getFullUrl(string $url = ''): string
    {
        $canonicalLink = $url ?: $_SERVER['REQUEST_URI'];
        $schema = self::isSecureConnection() ? 'https://' : 'http://';

        return $schema . $_SERVER['HTTP_HOST'] . $canonicalLink;
    }
}
