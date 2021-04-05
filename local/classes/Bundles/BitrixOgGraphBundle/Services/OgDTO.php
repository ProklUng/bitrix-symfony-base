<?php

namespace Local\Bundles\BitrixOgGraphBundle\Services;

use Spatie\DataTransferObject\DataTransferObject;

/**
 * Class OgDTO
 * DTO для класса OG.
 * @package Local\Bundles\BitrixOgGraphBundle\Services
 *
 * @since 13.10.2020 Доработка.
 */
class OgDTO extends DataTransferObject
{
    /** @var string $url URL. */
    public $url = '';
    /** @var string $title Тайтл. */
    public $title = '';
    /** @var string $img Картинка. */
    public $img = '';
    /** @var string $description Дескрипшен. */
    public $description = '';
    /** @var string $site_name Название сайта. */
    public $site_name = '';
    /** @var string $type */
    public $type = 'website';
    /** @var string $fb_admins */
    public $fb_admins = '';
    /** @var string $article_publisher */
    public $article_publisher = '';
    /** @var string $timePublished */
    public $timePublished = '';
    /** @var string $mainDescription */
    public $mainDescription = '';

    /**
     * Создать DTO из конфига.
     *
     * @param array $config Конфиг.
     *
     * @return static
     */
    public static function fromConfig(array $config) : self
    {
        unset($config['enabled']);

        return new self($config);
    }

    /**
     * Обновить из массива.
     *
     * @param array $config Конфиг.
     *
     * @return void
     */
    public function update(array $config) : void
    {
        foreach ($config as $key => $value) {
            $this->{$key} = $value;
        }
    }
}
