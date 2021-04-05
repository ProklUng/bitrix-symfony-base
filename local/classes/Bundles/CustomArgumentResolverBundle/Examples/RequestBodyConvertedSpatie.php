<?php

namespace Local\Bundles\CustomArgumentResolverBundle\Examples;

use Local\Bundles\CustomArgumentResolverBundle\ArgumentsResolver\Contracts\UnserializableRequestInterface;
use Spatie\DataTransferObject\DataTransferObject;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class RequestBodyConvertedSpatie
 * @package Local\Bundles\CustomArgumentResolverBundle\Examples
 *
 * @since 03.04.2021
 */
class RequestBodyConvertedSpatie extends DataTransferObject implements UnserializableRequestInterface
{
    /**
     * @var string $email
     *
     * @Assert\Length(
     *  min=3,
     *  minMessage="Email must be at least {{ limit }} characters long"
     * )
     */
    public $email;

    /**
     * @var integer
     */
    public $numeric;
}
