<?php

namespace Local\Bundles\InstagramParserRapidApiBundle\Services;

use Exception;
use Local\Bundles\InstagramParserRapidApiBundle\Services\Exceptions\InstagramTransportException;
use Local\Bundles\InstagramParserRapidApiBundle\Services\Interfaces\RetrieverInstagramDataInterface;
use Local\Bundles\InstagramParserRapidApiBundle\Services\Transport\InstagramTransportInterface;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\InvalidArgumentException;
use Symfony\Contracts\Cache\CacheInterface;

/**
 * Class RetrieverInstagramDataRapidApi
 * Парсер Инстаграма через RapidAPI.
 * @see https://rapidapi.com/restyler/api/instagram40
 *
 * @package Local\Bundles\InstagramParserRapidApiBundle\Services
 *
 * @since 21.02.2021
 */
class RetrieverInstagramDataRapidApi implements RetrieverInstagramDataInterface
{
    /**
     * @const string CACHE_KEY Ключ кэша.
     */
    private const CACHE_KEY = 'instagram_parser_rapid_api.parser_cache_key';

    /**
     * @var CacheInterface $cacher Кэшер.
     */
    private $cacher;

    /**
     * @var InstagramTransportInterface Транспорт.
     */
    private $instagramTransport;

    /**
     * @var string $userId Instagram ID user. @see См. https://codeofaninja.com/tools/find-instagram-user-id/
     */
    private $userId;

    /**
     * @var string $after Параметр after RapidAPI. Для постраничного получения.
     */
    private $after;

    /**
     * @var integer $count Сколько картинок запрашивать.
     */
    private $count = 12;

    /**
     * @var boolean $useMock Использовать мок? (для отладки)
     */
    private $useMock = false;

    /**
     * @var string $fixture Фикстура.
     */
    private $fixture = '';

    /**
     * RetrieverInstagramDataRapidApi constructor.
     *
     * @param CacheInterface              $cacher             Кэшер.
     * @param InstagramTransportInterface $instagramTransport Транспорт.
     * @param string                      $userId             Instagram ID user.
     */
    public function __construct(
        CacheInterface $cacher,
        InstagramTransportInterface $instagramTransport,
        string $userId
    ) {
        $this->cacher = $cacher;
        $this->instagramTransport = $instagramTransport;
        $this->userId = $userId;
    }

    /**
     * @inheritDoc
     * @throws InstagramTransportException Ошибки транспорта.
     * @throws InvalidArgumentException     Ошибки кэшера.
     */
    public function query(): array
    {
        if ($this->useMock && trim($this->fixture)) {
            return json_decode($this->fixture, true);
        }

        $keyCache = self::CACHE_KEY. $this->userId;
        if ($this->after) {
            $keyCache .= md5($this->after);
        }

        if ($this->after) {
            $keyCache .= $this->after;
        }

        $result = $this->cacher->get(
            $keyCache,
            /**
             * @param CacheItemInterface $item
             * @return mixed
             */
            function (CacheItemInterface $item) {
                $query = '/account-medias?userid=' . $this->userId . '&first=' . $this->count;
                // Постраничные запросы.
                if ($this->after) {
                    $query .= '&after='.$this->after;
                }

                try {
                    $response = $this->instagramTransport->get($query);
                } catch (Exception $e) {
                    return null;
                }

                return json_decode($response, true);
            }
        );

        // Ошибки API. Неверный ключ и т.д.
        if (array_key_exists('message', $result)
            && $result['message'] !== ''
        ) {
            $this->cacher->delete($keyCache);
            throw new InstagramTransportException(
                $result['message'],
                400
            );
        }

        // В ответ не пришел json.
        if (!$result) {
            $this->cacher->delete($keyCache);
            throw new InstagramTransportException(
                'Get Request Error: answer not json!',
                400
            );
        }

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function setUserId(string $userId): RetrieverInstagramDataInterface
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setAfterMark(string $after): RetrieverInstagramDataInterface
    {
        $this->after = $after;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setCount(int $count): RetrieverInstagramDataInterface
    {
        $this->count = $count;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setUseMock(bool $useMock, string $fixturePath = ''): RetrieverInstagramDataInterface
    {
        $this->useMock = $useMock;
        if ($useMock && $fixturePath !== '') {
            $this->fixture = (string)file_get_contents(
                $_SERVER['DOCUMENT_ROOT'] . $fixturePath
            );
        }

        return $this;
    }
}
