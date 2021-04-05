<?php

namespace Local\Services;

/**
 * Class AssetPathService
 * @package Local\Services
 *
 * @since 07.09.2020
 */
class AssetPathService
{
    /**
     * Путь к сборке Webpack в зависимости от окружения.
     *
     * @param string $debug         Прод или нет.
     * @param string $pathDevBuild  Dev сборка.
     * @param string $pathProdBuild Продакшен сборка.
     *
     * @return string
     */
    public function pathBuild(
        string $debug,
        string $pathDevBuild,
        string $pathProdBuild
    ) {

        return $debug ? $pathDevBuild : $pathProdBuild;
    }
}
