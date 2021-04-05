<?php

namespace Local\Bundles\BitrixComponentParamsBundle\Facades;

use Local\ServiceProvider\BaseFacade\Facade;

/**
 * Class NewsListParamsFacade
 * @package Local\Bundles\BitrixComponentParamsBundle\Facades
 *
 * @method static make(array $params)
 */
class NewsListParamsFacade extends Facade
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
