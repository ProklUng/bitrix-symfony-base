<?php

namespace Local\Bundles\CustomArgumentResolverBundle\Annotation;

use Doctrine\Common\Annotations\Annotation;

/**
 * When used on controller's action, it requires the presence of a valid CSRF token in order to process the request.
 *
 * @Annotation
 *
 * @since 13.12.2020
 */
class CsrfTokenRequired extends Annotation
{
    /** @var string */
    public $id;

    /** @var string */
    public $header = 'X-CSRF-Token';

    /** @var string */
    public $param = 'token';
}
