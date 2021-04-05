<?php

namespace Local\Services\Bitrix\Psr16Cache;

use Closure;
use DateInterval;
use Exception;
use Bitrix\Main\Data\Cache as BitrixCache;
use Bitrix\Main\Result;
use Bitrix\Main\Error;
use Generator;
use Psr\SimpleCache\CacheInterface;

/**
 * Class BitrixCacher
 * @package Local\Services\Bitrix\Psr16Cache
 *
 * @since 15.11.2020
 */
class BitrixCacher implements CacheInterface
{
    /** @const string DEFAULT_BASE_DIR Базовая директория по умолчанию. */
    private const DEFAULT_BASE_DIR = 'cache/';

    /**
     * @var BitrixCache $bitrixCache Кэшер
     */
    private $bitrixCache;

    /** @var integer $ttl Время жизни кэша. */
    private $ttl = 3600; // 30 days

    /** @var string */
    private $initDir = '/';

    /** @var string $baseDir Базовая директория кэша */
    private $baseDir = 'cache/';

    /**
     * @param BitrixCache $bitrixCache Кэшер.
     * @param string|null $initDir     Общая директория кэша.
     * @param string|null $baseDir     Базовая директория кэша.
     */
    public function __construct(BitrixCache $bitrixCache, string $initDir = null, string $baseDir = null)
    {
        $this->bitrixCache = $bitrixCache;

        if ($initDir !== null) {
            $this->initDir = $initDir;
        }

        if ($baseDir !== null) {
            $this->baseDir = $baseDir;
        }
    }

    /**
     * @param string $key     Ключ кэша.
     * @param mixed  $default Значение по-умолчанию.
     *
     * @return mixed
     */
    public function get($key, $default = null)
    {
        $result = $this->getResult($key);

        return $result->isSuccess() ? $result->getData()['value'] : $default;
    }

    /**
     * @param string $key Ключ кэша.
     *
     * @return boolean
     */
    public function has($key) : bool
    {
        return $this->getResult($key)->isSuccess();
    }

    /**
     * @param string                    $key   Ключ кэша.
     * @param mixed                     $value Значение.
     * @param null|integer|DateInterval $ttl   Время жизни кэша.
     *
     * @return boolean
     * @throws Exception
     */
    public function set($key, $value, $ttl = null) : bool
    {
        $this->delete($key);

        if ($ttl instanceof DateInterval) {
            $ttl = $this->dateIntervalToSeconds($ttl);
        }

        try {
            $ttl = $ttl ?? $this->ttl;

            if ($this->bitrixCache->startDataCache($ttl, $key, $this->initDir, [], $this->baseDir)) {
                $this->bitrixCache->endDataCache([
                    'expire' => time() + $ttl,
                    'value' => $value,
                ]);
            }
        } catch (Exception $e) {
            $this->bitrixCache->abortDataCache();
            throw $e;
        }

        return true;
    }

    /**
     * Сеттер времени жизни кэша.
     *
     * @param integer $ttl Время жизни кэша.
     *
     * @return $this
     */
    public function setTtl(int $ttl) : self
    {
        $this->ttl = $ttl;

        return $this;
    }

    /**
     * Сеттер базовой директории кэша.
     *
     * @param string $dir Директория кэша.
     *
     * @return $this
     */
    public function setBaseDir(string $dir) : self
    {
        $this->baseDir = self::DEFAULT_BASE_DIR . SITE_ID . $dir;

        return $this;
    }

    /**
     * @param string                    $key      Ключ кэша.
     * @param callable|Closure          $callable Callable.
     * @param null|integer|DateInterval $ttl      Время жизни кэша.
     *
     * @return mixed
     * @throws Exception
     */
    public function getOrSet(string $key, callable $callable, $ttl = null)
    {
        $result = $this->getResult($key);

        if ($result->isSuccess()) {
            $value = $result->getData()['value'];
        } else {
            $value = $callable($this);
            $this->set($key, $value, $ttl);
        }

        return $value;
    }

    /**
     * Удалить ключ кэша.
     *
     * @param string $key Ключ кэша.
     *
     * @return boolean
     */
    public function delete($key) : bool
    {
        $this->bitrixCache->clean($key, $this->initDir, $this->baseDir);

        return true;
    }

    /**
     * Очистить весь кэш в базовой директории.
     *
     * @return boolean
     */
    public function clear() : bool
    {
        $this->bitrixCache->cleanDir($this->initDir, $this->baseDir);

        return true;
    }

    /**
     * Получить несколько ключей кэша за раз.
     *
     * @param iterable $keys    Ключи кэша.
     * @param mixed    $default Значение по умолчанию.
     *
     * @return iterable|Generator
     */
    public function getMultiple($keys, $default = null)
    {
        foreach ($keys as $key) {
            yield $key => $this->get($key, $default);
        }
    }

    /**
     * Установить несколько ключей кэша за раз.
     *
     * @param iterable                  $values Ключи кэша.
     * @param null|integer|DateInterval $ttl    Время жизни.
     *
     * @return boolean
     * @throws Exception
     */
    public function setMultiple($values, $ttl = null) : bool
    {
        foreach ($values as $key => $value) {
            $this->set($key, $value, $ttl);
        }

        return true;
    }

    /**
     * Удалить несколько ключей за раз.
     *
     * @param iterable $keys Ключи кэша.
     *
     * @return boolean
     */
    public function deleteMultiple($keys) : bool
    {
        foreach ($keys as $key) {
            $this->delete($key);
        }

        return true;
    }

    /**
     * Преобразовать интервал в секунды.
     *
     * @param DateInterval $interval Интервал.
     *
     * @return integer
     *
     * @throws Exception
     */
    private function dateIntervalToSeconds(DateInterval $interval): int
    {
        $now = new \DateTimeImmutable();
        $endTime = $now->add($interval);

        return $endTime->getTimestamp() - $now->getTimestamp();
    }

    /**
     * Результат из кэша.
     *
     * @param string $key Ключ кэша.
     *
     * @return Result
     */
    private function getResult(string $key): Result
    {
        $result = new Result();

        if ($this->bitrixCache->initCache($this->ttl, $key, $this->initDir, $this->baseDir)) {
            $data = $this->bitrixCache->getVars();

            if (!isset($data['expire'])) {
                $result->addError(new Error('Expire not found'));

                return $result;
            }

            $data['expire'] = (int)$data['expire'];

            if ($data['expire'] < time()) {
                $result->addError(new Error('Expired'));

                return $result;
            }

            if (!array_key_exists('value', $data)) {
                $result->addError(new Error('Value not found'));

                return $result;
            }

            $result->setData(['value' => $data['value']]);
        } else {
            $result->addError(new Error('Key not found'));
        }

        return $result;
    }
}
