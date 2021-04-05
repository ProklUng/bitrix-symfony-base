<?php

namespace Local\Bundles\BitrixComponentParamsBundle\DTO;

use Spatie\DataTransferObject\DataTransferObject;
use Local\Bundles\BitrixComponentParamsBundle\Services\Contracts\BitrixParameterInterface;

/**
 * Class DtoNews
 * @package Local\Bundles\BitrixComponentParamsBundle\DTO
 */
class DtoNews extends DataTransferObject implements BitrixParameterInterface
{
    /** @var int $IBLOCK_ID */
    public $IBLOCK_ID = 0;

    /** @var string $IBLOCK_TYPE */
    public $IBLOCK_TYPE = '';

    /** @var string $ADD_ELEMENT_CHAIN */
    public $ADD_ELEMENT_CHAIN = 'Y';

    /** @var string $ADD_SECTIONS_CHAIN */
    public $ADD_SECTIONS_CHAIN = 'Y';

    /** @var string $INCLUDE_IBLOCK_INTO_CHAIN */
    public $INCLUDE_IBLOCK_INTO_CHAIN = 'N';

    /** @var string $AJAX_MODE */
    public $AJAX_MODE = 'N';

    /** @var string $AJAX_OPTION_ADDITIONAL */
    public $AJAX_OPTION_ADDITIONAL = '';

    /** @var string $AJAX_OPTION_HISTORY */
    public $AJAX_OPTION_HISTORY = 'N';

    /** @var string $AJAX_OPTION_JUMP */
    public $AJAX_OPTION_JUMP = 'N';

    /** @var string $AJAX_OPTION_STYLE */
    public $AJAX_OPTION_STYLE = 'Y';

    /** @var string $BROWSER_TITLE */
    public $BROWSER_TITLE = '-';

    /** @var string $CACHE_FILTER */
    public $CACHE_FILTER = 'N';

    /** @var string $CACHE_GROUPS */
    public $CACHE_GROUPS = 'N';

    /** @var string $CACHE_TYPE */
    public $CACHE_TYPE = 'A';

    /** @var string $CACHE_TIME */
    public $CACHE_TIME = '';

    /** @var string $CHECK_DATES */
    public $CHECK_DATES = 'N';

    /** @var string $DETAIL_ACTIVE_DATE_FORMAT */
    public $DETAIL_ACTIVE_DATE_FORMAT = 'd.m.Y';

    /** @var string $DETAIL_DISPLAY_BOTTOM_PAGER */
    public $DETAIL_DISPLAY_BOTTOM_PAGER = 'Y';

    /** @var string $DETAIL_DISPLAY_TOP_PAGER */
    public $DETAIL_DISPLAY_TOP_PAGER = 'N';

    /** @var array $DETAIL_FIELD_CODE */
    public $DETAIL_FIELD_CODE = ['ID', 'PREVIEW_TEXT', 'PREVIEW_PICTURE', 'DETAIL_PICTURE', 'ACTIVE_FROM'];

    /** @var string $DETAIL_PAGER_SHOW_ALL */
    public $DETAIL_PAGER_SHOW_ALL = 'Y';

    /** @var string $DETAIL_PAGER_TEMPLATE */
    public $DETAIL_PAGER_TEMPLATE = '';

    /** @var string $DETAIL_PAGER_TITLE */
    public $DETAIL_PAGER_TITLE = 'Страница';

    /** @var array $DETAIL_PROPERTY_CODE */
    public $DETAIL_PROPERTY_CODE = [
        'ID',
        'PREVIEW_TEXT',
        'PREVIEW_PICTURE',
        'DETAIL_PICTURE',
        'ACTIVE_FROM',
        'CANONICAL_URL',
    ];

    /** @var string $DETAIL_SET_CANONICAL_URL */
    public $DETAIL_SET_CANONICAL_URL = 'N';

    /** @var string $DISPLAY_BOTTOM_PAGER */
    public $DISPLAY_BOTTOM_PAGER = 'Y';

    /** @var string $DISPLAY_DATE */
    public $DISPLAY_DATE = 'Y';

    /** @var string $DISPLAY_NAME */
    public $DISPLAY_NAME = 'Y';

    /** @var string $DISPLAY_PICTURE */
    public $DISPLAY_PICTURE = 'Y';

    /** @var string $DISPLAY_PREVIEW_TEXT */
    public $DISPLAY_PREVIEW_TEXT = 'Y';

    /** @var string $DISPLAY_TOP_PAGER */
    public $DISPLAY_TOP_PAGER = 'N';

    /** @var string $HIDE_LINK_WHEN_NO_DETAIL */
    public $HIDE_LINK_WHEN_NO_DETAIL = 'N';

    /** @var string $LIST_ACTIVE_DATE_FORMAT */
    public $LIST_ACTIVE_DATE_FORMAT = 'd.m.Y';

    /** @var array $LIST_FIELD_CODE */
    public $LIST_FIELD_CODE = ['ID', 'NAME', 'PREVIEW_TEXT', 'PREVIEW_PICTURE', 'H1'];

    /** @var array $LIST_PROPERTY_CODE */
    public $LIST_PROPERTY_CODE = ['', 'TYPE'];

    /** @var string $MEDIA_PROPERTY */
    public $MEDIA_PROPERTY = '';

    /** @var string $MESSAGE_404 */
    public $MESSAGE_404 = '';

    /** @var string $META_DESCRIPTION */
    public $META_DESCRIPTION = '-';

    /** @var string $META_KEYWORDS */
    public $META_KEYWORDS = '-';

    /** @var int $NEWS_COUNT */
    public $NEWS_COUNT = 20;

    /** @var string $PAGER_BASE_LINK_ENABLE */
    public $PAGER_BASE_LINK_ENABLE = 'N';

    /** @var string $PAGER_DESC_NUMBERING */
    public $PAGER_DESC_NUMBERING = 'N';

    /** @var int $PAGER_DESC_NUMBERING_CACHE_TIME */
    public $PAGER_DESC_NUMBERING_CACHE_TIME = 36000;

    /** @var string $PAGER_SHOW_ALL */
    public $PAGER_SHOW_ALL = 'N';

    /** @var string $PAGER_SHOW_ALWAYS */
    public $PAGER_SHOW_ALWAYS = 'N';

    /** @var string $PAGER_TEMPLATE */
    public $PAGER_TEMPLATE = '.default';

    /** @var string $PAGER_TITLE */
    public $PAGER_TITLE = '';

    /** @var string $PREVIEW_TRUNCATE_LEN */
    public $PREVIEW_TRUNCATE_LEN = '';

    /** @var string $SEF_FOLDER */
    public $SEF_FOLDER = '';

    /** @var string $SEF_MODE */
    public $SEF_MODE = 'Y';

    /** @var array $SEF_URL_TEMPLATES */
    public $SEF_URL_TEMPLATES = ['detail' => '#ELEMENT_CODE#/', 'news' => '', 'section' => ''];

    /** @var string $SET_LAST_MODIFIED */
    public $SET_LAST_MODIFIED = 'N';

    /** @var string $SET_STATUS_404 */
    public $SET_STATUS_404 = 'Y';

    /** @var string $SET_TITLE */
    public $SET_TITLE = 'N';

    /** @var string $SHOW_404 */
    public $SHOW_404 = 'Y';

    /** @var string $SLIDER_PROPERTY */
    public $SLIDER_PROPERTY = '';

    /** @var string $SORT_BY1 */
    public $SORT_BY1 = 'SORT';

    /** @var string $SORT_BY2 */
    public $SORT_BY2 = 'ACTIVE_FROM';

    /** @var string $SORT_ORDER1 */
    public $SORT_ORDER1 = 'ASC';

    /** @var string $SORT_ORDER2 */
    public $SORT_ORDER2 = 'DESC';

    /** @var string $STRICT_SECTION_CHECK */
    public $STRICT_SECTION_CHECK = 'N';

    /** @var string $TEMPLATE_THEME */
    public $TEMPLATE_THEME = 'blue';

    /** @var string $USE_CATEGORIES */
    public $USE_CATEGORIES = 'N';

    /** @var string $USE_FILTER */
    public $USE_FILTER = 'N';

    /** @var string $USE_PERMISSIONS */
    public $USE_PERMISSIONS = 'N';

    /** @var string $USE_RATING */
    public $USE_RATING = 'N';

    /** @var string $USE_REVIEW */
    public $USE_REVIEW = 'N';

    /** @var string $USE_RSS */
    public $USE_RSS = 'N';

    /** @var string $USE_SEARCH */
    public $USE_SEARCH = 'N';

    /** @var string $USE_SHARE */
    public $USE_SHARE = 'N';

    /** @var string $H1_PROPERTY */
    public $H1_PROPERTY = '';


}
