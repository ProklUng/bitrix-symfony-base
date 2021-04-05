<?php

namespace Local\Bundles\CustomArgumentResolverBundle\Tests\Cases\ArgumentResolvers\Tools;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class ExampleRequestClass
 * @package Local\Bundles\CustomArgumentResolverBundle\Tests\Cases\ArgumentResolvers\Tools
 *
 * @since 03.04.2021
 */
class ExampleRequestClass
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
}