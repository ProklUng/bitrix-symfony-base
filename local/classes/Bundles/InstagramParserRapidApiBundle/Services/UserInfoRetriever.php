<?php

namespace Local\Bundles\InstagramParserRapidApiBundle\Services;

use Exception;
use Local\Bundles\InstagramParserRapidApiBundle\Services\Exceptions\InstagramTransportException;
use Local\Bundles\InstagramParserRapidApiBundle\Services\Transport\InstagramTransportInterface;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\InvalidArgumentException;
use Symfony\Contracts\Cache\CacheInterface;

/**
 * Class UserIdRetriever
 * @package Local\Bundles\InstagramParserRapidApiBundle\Services
 *
 * @since 23.02.2021
 */
class UserInfoRetriever
{
    /**
     * @const string CACHE_KEY Ключ кэша.
     */
    private const CACHE_KEY = 'instagram_parser_rapid_api.parser_cache_key_user_name';

    /**
     * @var CacheInterface $cacher Кэшер.
     */
    private $cacher;

    /**
     * @var InstagramTransportInterface Транспорт.
     */
    private $instagramTransport;

    /**
     * @var string $userName Instagram user name.
     */
    private $userName = '';

    /**
     * @var boolean $useMock Использовать мок? (для отладки)
     */
    private $useMock = false;

    /**
     * @var string $fixture Фикстура.
     */
    private $fixture = '';

    /**
     * UserInfoRetriever constructor.
     *
     * @param CacheInterface              $cacher             Кэшер.
     * @param InstagramTransportInterface $instagramTransport Транспорт.
     */
    public function __construct(
        CacheInterface $cacher,
        InstagramTransportInterface $instagramTransport
    ) {
        $this->cacher = $cacher;
        $this->instagramTransport = $instagramTransport;
    }

    /**
     * @param string $userName Имя пользователя.
     *
     * @return UserInfoRetriever
     */
    public function setUserName(string $userName): UserInfoRetriever
    {
        $this->userName = $userName;

        return $this;
    }

    /**
     * Получить ID пользователя.
     *
     * @throws InstagramTransportException Ошибки транспорта.
     * @throws InvalidArgumentException    Ошибки кэшера.
     */
    public function getUserId() : string
    {
        $data = $this->query();

        if (!array_key_exists('id', $data)) {
            throw new InstagramTransportException(
                'ID пользователя с именем ' . $this->userName . ' получить не удалось.'
            );
        }

        return $data['id'];
    }

    /**
     * Получить все данные на пользователя.
     *
     * @return array
     * @throws InstagramTransportException Ошибки транспорта.
     * @throws InvalidArgumentException    Ошибки кэшера.
     */
    public function getAllData() : array
    {
        return $this->query();
    }

    /**
     * @param boolean $useMock     Использовать мок.
     * @param string  $fixturePath Путь к фикстуре.
     *
     * @return $this
     */
    public function setUseMock(bool $useMock, string $fixturePath = ''): self
    {
        $this->useMock = $useMock;
        if ($useMock && $fixturePath !== '') {
            $this->fixture = (string)file_get_contents(
                $_SERVER['DOCUMENT_ROOT'] . $fixturePath
            );
        }

        return $this;
    }

    /**
     * Запрос.
     *
     * @throws InstagramTransportException Ошибки транспорта.
     * @throws InvalidArgumentException    Ошибки кэшера.
     */
    private function query(): array
    {
        if ($this->useMock && trim($this->fixture)) {
            $result =  json_decode($this->fixture, true);
            return $result ? $this->clearResult($result) : $result;
        }

        $keyCache = self::CACHE_KEY. $this->userName;

        $result = $this->cacher->get(
            $keyCache,
            /**
             * @param CacheItemInterface $item
             * @return mixed
             */
            function (CacheItemInterface $item) {
                $queryString = '/account-info?username=' . $this->userName;
                try {
                    $response = $this->instagramTransport->get($queryString);
                } catch (Exception $e) {
                    return null;
                }

                $result =  json_decode($response, true);

                return $result ? $this->clearResult($result) : $result;
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
     * Очистить результат от лишнего.
     *
     * @param array $result Результат.
     *
     * @return array
     */
    private function clearResult(array $result) : array
    {
        unset(
            $result['edge_felix_video_timeline'],
            $result['edge_owner_to_timeline_media'],
            $result['edge_related_profiles'],
            $result['edge_media_collections'],
            $result['edge_saved_media']
        );

        return $result;
    }
}
