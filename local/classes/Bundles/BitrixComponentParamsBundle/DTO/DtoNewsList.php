<?php

namespace Local\Bundles\BitrixComponentParamsBundle\DTO;

use Spatie\DataTransferObject\DataTransferObject;
use Local\Bundles\BitrixComponentParamsBundle\Services\Contracts\BitrixParameterInterface;

/**
 * Class DtoNewsList
 * @package Local\Bundles\BitrixComponentParamsBundle\DTO
 */
class DtoNewsList extends DataTransferObject implements BitrixParameterInterface
{
    /** @var int $IBLOCK_ID */
    public $IBLOCK_ID = 0;

    /** @var string $IBLOCK_TYPE */
    public $IBLOCK_TYPE = '';

    /** @var int $NEWS_COUNT */
    public $NEWS_COUNT = 9999;

    /** @var string $SORT_BY1 */
    public $SORT_BY1 = 'SORT';

    /** @var string $SORT_ORDER1 */
    public $SORT_ORDER1 = 'ASC';

    /** @var string $SORT_BY2 */
    public $SORT_BY2 = 'ACTIVE_FROM';

    /** @var string $SORT_ORDER2 */
    public $SORT_ORDER2 = 'DESC';

    /** @var string $CACHE_TYPE */
    public $CACHE_TYPE = 'A';

    /** @var string $CACHE_TIME */
    public $CACHE_TIME = '';

    /** @var string $CACHE_FILTER */
    public $CACHE_FILTER = 'N';

    /** @var string $CACHE_GROUPS */
    public $CACHE_GROUPS = 'N';

    /** @var string $SET_TITLE */
    public $SET_TITLE = 'N';

    /** @var string $SET_BROWSER_TITLE */
    public $SET_BROWSER_TITLE = 'N';

    /** @var string $SET_META_KEYWORDS */
    public $SET_META_KEYWORDS = 'N';

    /** @var string $SET_META_DESCRIPTION */
    public $SET_META_DESCRIPTION = 'N';

    /** @var string $SET_STATUS_404 */
    public $SET_STATUS_404 = 'Y';

    /** @var string $SET_LAST_MODIFIED */
    public $SET_LAST_MODIFIED = 'N';

    /** @var string $INCLUDE_IBLOCK_INTO_CHAIN */
    public $INCLUDE_IBLOCK_INTO_CHAIN = 'N';

    /** @var string $ADD_SECTIONS_CHAIN */
    public $ADD_SECTIONS_CHAIN = 'N';

    /** @var string $PARENT_SECTION */
    public $PARENT_SECTION = '';

    /** @var string $PARENT_SECTION_CODE */
    public $PARENT_SECTION_CODE = '';

    /** @var string $INCLUDE_SUBSECTIONS */
    public $INCLUDE_SUBSECTIONS = 'Y';

    /** @var array $PROPERTY_CODE */
    public $PROPERTY_CODE = ['TITLE'];


}
