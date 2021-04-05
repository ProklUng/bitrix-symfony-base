<?php
namespace Local\Util\Router;

use GuzzleHttp\Exception\GuzzleException;
use Instagram\Exception\InstagramCacheException;
use Instagram\Exception\InstagramException;
use Local\Constants;
use Local\Instagram\FeedRetriever;
use Local\Instagram\FeedRetrieverSimple;
use Local\Instagram\Parser;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use \Bitrix\Main\Data\Cache;

/**
 * Class InstagramParserController
 * @package Local\Util\Router
 */
class InstagramParserController
{
    /**
     * Controller
     *
     * @internal Instagram логин: xxxxx / Web30~99
     *
     * @param Request $obRequest      Объект Request.
     * @param integer $count_pictures Количество картинок.
     * @param string  $instagram      Инстаграм эккаунт.
     *
     * @return Response $content
     */
    public function action(Request $obRequest, int $count_pictures, string $instagram) : Response
    {
        /** @var integer $paramCountPicture GET параметр количество картинок. */
        $paramCountPicture = $obRequest->query->get('count_pictures');
        /** @var string $paramInstagram GET параметр пользователь Инстаграма. */
        $paramInstagram = $obRequest->query->get('instagram');

        if ($paramInstagram === null) {
            $paramInstagram = $instagram;
        }

        if ($paramCountPicture === null) {
            $paramCountPicture = $count_pictures;
        }

        // Получить данные (на продакшене - из кэша).
        $arResult = $this->cacheData(
            $paramInstagram,
            $paramCountPicture,
            env('DEBUG', false) ? 0 : Constants::SECONDS_IN_HOUR
        );

        return new Response(
            json_encode($arResult),
            Response::HTTP_OK,
            ['content-type' => 'application/json']
        );
    }

    /**
     * Распарсить фид Инстаграма.
     *
     * @param string  $instagram     Юзер Инстаграма.
     * @param integer $countPictures Количество постов.
     *
     * @return array
     */
    public function parseFeed(
        string $instagram,
        int $countPictures = 3
    ) : array {
        $handler = env('DEBUG', false) ? new FeedRetrieverSimple() : new FeedRetriever();
        // $handler = new FeedRetrieverSimple();
        /** @var $obInstagramParser $obInstagramParser Парсер Инстаграма. */
        $obInstagramParser = new Parser(
            $handler,
            $instagram,
            $countPictures
        );

        try {
            $arResult = $obInstagramParser->data();
        } catch (GuzzleException | InstagramCacheException | InstagramException $e) {
            $arResult = [
                'error' => $e->getMessage()
            ];
        }

        return $arResult;
    }

    /**
     * Кэширование.
     *
     * @param string  $instagram     Юзер Инстаграма.
     * @param integer $countPictures Количество постов.
     * @param integer $ttlCache      Время жизни кэша.
     *
     * @return array
     */
    public function cacheData(
        string $instagram,
        int $countPictures,
        int $ttlCache = Constants::SECONDS_IN_HOUR
    ) : array {

        /** Результат. */
        $arData = [];
        /** Суффикс кэша. */
        $cacheKeySuffix = md5($instagram).md5($countPictures);

        $cache = Cache::createInstance();
        if ($cache->initCache(
            $ttlCache,
            'instagram.news.parser' . $cacheKeySuffix
        )
        ) {
            $arData = $cache->getVars();
        } elseif ($cache->startDataCache()) {
            $arData = $this->parseFeed(
                $instagram,
                $countPictures
            );

            // Если ошибка - закругляемся с кэшированием.
            if (!empty($arData['error'])) {
                $cache->abortDataCache();
                return $arData;
            }

            $cache->endDataCache($arData);
        }

        return $arData;
    }
}
