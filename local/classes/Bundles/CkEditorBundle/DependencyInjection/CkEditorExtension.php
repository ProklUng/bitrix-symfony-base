<?php

namespace Local\Bundles\CkEditorBundle\DependencyInjection;

use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Exception;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * Class CkEditorExtension
 * @package Local\Bundles\CkEditor\DependencyInjection
 *
 * @since 18.04.2021
 */
final class CkEditorExtension extends Extension
{
    private const DIR_CONFIG = '/../Resources/config';

    private const BITRIX_MODULE_ID = 'prokl.ckeditor';

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function load(array $configs, ContainerBuilder $container) : void
    {
        if (!$this->checkDepends()) {
            return;
        }

        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__ . self::DIR_CONFIG)
        );

        $loader->load('services.yaml');
    }

    /**
     * @inheritDoc
     */
    public function getAlias() : string
    {
        return 'ckeditor';
    }

    /**
     * Проверка на конфликты - не установлен ли уже соответствующий модуль, среда.
     *
     * @return boolean
     * @throws LoaderException Когда что-то с Битриксом не так.
     */
    private function checkDepends() : bool
    {
        $moduleInstalled = Loader::includeModule(self::BITRIX_MODULE_ID);
        if ($moduleInstalled) {
            return false;
        }

        return true;
    }
}
