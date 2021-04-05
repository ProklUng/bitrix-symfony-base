<?php

namespace Local\Bundles\StaticPageMakerBundle\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Twig_ExtensionInterface;

/**
 * Class BitrixPiecesExtension
 * @package Local\Bundles\StaticPageMakerBundle\Twig
 *
 * @since 21.10.2020
 */
class BitrixPiecesExtension extends AbstractExtension implements Twig_ExtensionInterface
{
    /**
     * @var string $documentRoot DOCUMENT_ROOT
     */
    private $documentRoot;

    /**
     * BitrixPiecesExtension constructor.
     *
     * @param string $documentRoot DOCUMENT_ROOT.
     */
    public function __construct(
        string $documentRoot
    ) {
        $this->documentRoot = $documentRoot;
    }

    /**
     * Return extension name
     *
     * @return string
     */
    public function getName()
    {
        return 'static_pages_bundle.bitrix_pieces_extension';
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions() : array
    {
        return [
            new TwigFunction('header', [$this, 'getHeader']),
            new TwigFunction('footer', [$this, 'getFooter']),
        ];
    }

    /**
     * Битриксовый header.php текущего шаблона.
     *
     * @return void
     */
    public function getHeader() : void
    {
        global $SiteExpireDate; // Убираем надпись о просрочке сайта.
        global $APPLICATION;

        require($this->documentRoot . '/bitrix/modules/main/include/prolog.php');
    }

    /**
     * Битриксовый footer.php текущего шаблона.
     *
     * @return void
     */
    public function getFooter()
    {
        global $APPLICATION;
        // Так исключается shutdown в системе обработки футеров Битрикса.
        require($this->documentRoot . SITE_TEMPLATE_PATH .'/footer.php');
    }
}
