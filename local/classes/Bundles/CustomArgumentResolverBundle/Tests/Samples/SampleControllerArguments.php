<?php

namespace Local\Bundles\CustomArgumentResolverBundle\Tests\Samples;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Class SampleControllerArguments
 * @package Tests\Events\Samples
 *
 * @since 06.09.2020
 */
class SampleControllerArguments extends AbstractController
{
    /**
     * @param Request $request
     * @param SerializerInterface $serializer
     * @param string $value
     *
     * @return string
     */
    public function action(
        Request $request,
        SerializerInterface $serializer,
        string $value
    ) {
        return new Response('OK');
    }
}