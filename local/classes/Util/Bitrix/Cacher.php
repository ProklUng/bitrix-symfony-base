<?php

namespace Local\Util\Bitrix;

use CPHPCache;
use Local\Constants;
use Local\Facades\KernelFacade;

/**
 * Class Cacher
 * Кэширование.
 * @package Local\Util\Bitrix
 */
class Cacher
{
    /** @var CPHPCache $cacheHandler Обработчик кэша. */
    protected $cacheHandler;

    /**
     * @var string ID кэша.
     */
    protected $cacheId;

    /**
     * @var callable $callback Обработчик, он же получатель данных.
     */
    protected $callback;

    /**
     * @var array $timeSeconds Параметры обработчика.
     */
    protected $arCallbackParams;

    /**
     * @var integer $timeSeconds Время жизни кэша.
     */
    protected $timeSeconds;

    /**
     * @var string $currentUrl Текущий URL.
     */
    protected $currentUrl;

    /**
     * Cacher constructor.
     *
     * @param CPHPCache $cacheHandler     Обработчик кэша.
     * @param string    $cacheId          Ключ кэша.
     * @param mixed     $callback         Callback функция.
     * @param array     $arCallbackParams Параметры callback функции.
     * @param integer   $timeSeconds      Время кэширования.
     * @param string    $currentUrl       Текущий URL.
     */
    public function __construct(
        CPHPCache $cacheHandler,
        string $cacheId = '',
        $callback = null,
        array $arCallbackParams = [],
        int $timeSeconds = Constants::SECONDS_IN_WEEK,
        string $currentUrl = ''
    ) {
        $this->cacheHandler = $cacheHandler;
        $this->currentUrl = $currentUrl;

        // ID кэша формируется из переданного и соли от callback и параметров.
        $this->cacheId = $cacheId . md5($callback) . $this->hashCache($arCallbackParams);

        $this->callback = $callback;
        $this->arCallbackParams = $arCallbackParams;

        // Отрубить кэш для окружения dev.
        /** @noinspection PhpUndefinedMethodInspection */
        $this->timeSeconds = KernelFacade::isProduction() ? $timeSeconds : 0 ;
    }

    /**
     * Фасад.
     *
     * @param string  $cacheId          Ключ кэша.
     * @param mixed   $callback         Callback функция.
     * @param array   $arCallbackParams Параметры callback функции.
     * @param integer $timeSeconds      Время кэширования.
     * @param string  $currentUrl       Текущий URL.
     *
     * @return mixed
     */
    public static function cacheFacade(
        string $cacheId,
        $callback,
        array $arCallbackParams = [],
        int $timeSeconds = 86400,
        string $currentUrl = ''
    ) {
        $instance = new static(
            new CPHPCache(),
            $cacheId,
            $callback,
            $arCallbackParams,
            $timeSeconds,
            $currentUrl
        );

        return $instance->returnResultCache();
    }

    /**
     * @return array|mixed
     */
    public function returnResultCache()
    {
        /** Результат. */
        $arResult = [];

        $cachePath = '/' . SITE_ID . '/' . $this->cacheId;

        if ($this->cacheHandler->InitCache($this->timeSeconds, $this->cacheId, $cachePath)) {
            $vars = $this->cacheHandler->GetVars();
            $arResult = $vars['result'];
        } elseif ($this->cacheHandler->StartDataCache()) {
            $callback = $this->callback;
            $arResult = $callback(...$this->arCallbackParams);
            $this->cacheHandler->EndDataCache(['result' => $arResult]);
        }

        return $arResult;
    }

    /**
     * ID кэша.
     *
     * @param string $cacheId
     *
     * @return Cacher
     */
    public function setCacheId(string $cacheId): Cacher
    {
        $this->cacheId = $cacheId;

        return $this;
    }

    /**
     * Callback.
     *
     * @param callable $callback
     *
     * @return Cacher
     */
    public function setCallback(callable $callback): Cacher
    {
        $this->callback = $callback;

        return $this;
    }

    /**
     * Параметры callback.
     *
     * @return Cacher
     */
    public function setCallbackParams(): Cacher
    {
        $this->arCallbackParams = func_get_args();

        return $this;
    }

    /**
     * Время кэширования.
     *
     * @param int $timeSeconds
     *
     * @return Cacher
     */
    public function setTTL(int $timeSeconds): Cacher
    {
        $this->timeSeconds = $timeSeconds;

        return $this;
    }


    /**
     * Задать текущий URL.
     *
     * @param string $currentUrl
     *
     * @return Cacher
     */
    public function setCurrentUrl(string $currentUrl): Cacher
    {
        $this->currentUrl = $currentUrl;

        return $this;
    }

    /**
     * Salt кэша.
     *
     * @param array $arParams Параметры callback.
     *
     * @return string
     */
    protected function hashCache(array $arParams = []) : string
    {
        return md5(serialize($arParams));
    }
}
