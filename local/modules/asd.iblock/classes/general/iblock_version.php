<?php

/**
 * Class CASDiblockVersion
 */
class CASDiblockVersion
{

    /**
     * @return bool|int
     */
    public static function isIblockNewGridv18()
    {
        return self::checkMinVersion('18.0.0');
    }

    /**
     * @return mixed
     */
    public static function getIblockVersion()
    {
        return CASDModuleVersion::getModuleVersion('iblock');
    }

    /**
     * @param $checkVersion
     *
     * @return bool|int
     */
    public static function checkMinVersion($checkVersion)
    {
        return CASDModuleVersion::checkMinVersion('iblock', $checkVersion);
    }
}
