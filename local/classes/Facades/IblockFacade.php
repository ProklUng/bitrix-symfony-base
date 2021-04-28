<?php

namespace Local\Facades;

use Prokl\FacadeBundle\Services\AbstractFacade;

/**
 * Class IblockFacade
 * @package Local\Facades
 *
 * @method static getIBlockIdByCodeCached(string $type, string $code)
 * @method static getIBlockIdByCode(string $type, string $code)
 * @method static getImageIbId(int $idIblock)
 * @method static getImageIbIdCached(int $idIblock)
 * @method static getNameById(int $idIblock)
 * @method static getNameByIdCached(int $idIblock)
 * @method static getDescriptionById(int $idIblock)
 * @method static getImageByCode(string $iblockCode)
 * @method static getNameByCode(string $iblockCode)
 * @method static getDescriptionByIdCached(int $idIblock)
 * @method static getIBlockDescriptionByCode(string $type, string $code)

 */
class IblockFacade extends AbstractFacade
{
    /**
     * Сервис фасада.
     *
     * @return string
     */
    protected static function getFacadeAccessor() : string
    {
        return 'iblock.manager';
    }
}
