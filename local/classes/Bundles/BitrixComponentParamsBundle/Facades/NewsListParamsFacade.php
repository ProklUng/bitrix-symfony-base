<?php

namespace Local\Bundles\BitrixComponentParamsBundle\Facades;

use Prokl\FacadeBundle\Services\AbstractFacade;

/**
 * Class NewsListParamsFacade
 * @package Local\Bundles\BitrixComponentParamsBundle\Facades
 *
 * @method static make(array $params)
 */
class NewsListParamsFacade extends AbstractFacade
{
    /**
     * Сервис фасада.
     *
     * @return string
     */
    protected static function getFacadeAccessor() : string
    {
        return 'bitrix_component_params_bundle.news_list_arparams';
    }
}
