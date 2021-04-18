<?php

namespace Local\Bundles\CkEditorBundle;

use COption;
use Local\Bundles\CkEditorBundle\DependencyInjection\CkEditorExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class CkEditorBundle
 * @package Local\Bundles\CkEditorBundle
 *
 * @since 18.04.2021
 */
final class CkEditorBundle extends Bundle
{
    /**
     * @inheritDoc
     */
    public function getContainerExtension()
    {
        if ($this->extension === null) {
            $this->extension = new CkEditorExtension();
        }

        return $this->extension;
    }

    /**
     * @inheritDoc
     */
    public function boot() : void
    {
        parent::boot();
        if (COption::GetOptionString('iblock', 'use_htmledit') === 'Y') {
            COption::SetOptionString('iblock', 'use_htmledit', 'N');
        }
    }
}
