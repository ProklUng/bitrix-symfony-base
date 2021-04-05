<?php

namespace Local\Seo;

/**
 * Class SchemaOrg
 * @package Local\Seo
 */
class SchemaOrg
{
    /**
     * Вставка нужного itemprop в description.
     *
     * @param mixed $buffer Буфер.
     *
     * @return void
     */
    public function descriptionItemprop(&$buffer) : void
    {
        $buffer = str_replace('<meta name="description"', '<meta itemprop="description" name="description"', $buffer);
    }
}
