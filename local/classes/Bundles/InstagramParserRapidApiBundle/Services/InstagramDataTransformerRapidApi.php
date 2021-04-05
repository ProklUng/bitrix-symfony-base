<?php

namespace Local\Bundles\InstagramParserRapidApiBundle\Services;

use Local\Bundles\InstagramParserRapidApiBundle\Services\Interfaces\InstagramDataTransformerInterface;
use RuntimeException;

/**
 * Class InstagramDataTransformerRapidApi
 * @package Local\Bundles\InstagramParserRapidApiBundle\Services
 *
 * @since 21.02.2021
 */
class InstagramDataTransformerRapidApi implements InstagramDataTransformerInterface
{
    /**
     * @var array $arMedias Результат.
     */
    private $arMedias = [];

    /**
     * @inheritDoc
     */
    public function processMedias(array $arDataFeed, int $count = 3): array
    {
        /**
         * @internal $arDataFeed['page_info'] =>
         * ['has_next_page' => true, 'end_cursor' => 'XXXXX']
         */
        $countPicture = 1;
        $data = $arDataFeed['edges'] ?? [];

        if (count($data) === 0) {
            throw new RuntimeException('Ничего не получили из Инстаграма.');
        }

        foreach ($data as $item) {
            $item = $item['node'];

            if ($countPicture > $count || !$item) {
                break;
            }

            if ($item['is_video']) {
                continue;
            }

            $this->arMedias [] = [
                'link' => $item['shortcode'] ? 'https://www.instagram.com/p/' . $item['shortcode'] : '',
                'image' => $item['display_url'] ?? '',
                'description' => $item['edge_media_to_caption']['edges'][0]['node']['text'] ?? '',
            ];

            $countPicture++;
        }

        return $this->arMedias;
    }

    /**
     * @inheritDoc
     */
    public function getNextPageCursor(array $arDataFeed) : string
    {
        if (!array_key_exists('page_info', $arDataFeed)) {
            return '';
        }

        return $arDataFeed['page_info']['has_next_page']
            ?
            $arDataFeed['page_info']['end_cursor'] : '';
    }

}
