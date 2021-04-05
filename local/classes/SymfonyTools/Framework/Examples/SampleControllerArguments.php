<?php

namespace Local\SymfonyTools\Framework\Examples;

use Fedy\Transport\Interfaces\HttpTransportInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;

class SampleControllerArguments extends AbstractController
{
    /**
     * @param Request $request
     * @param HttpTransportInterface $httpClient
     *
     * @param SerializerInterface $serializer
     * @param ContainerBagInterface $parameterBag
     * @param string $projectDir
     * @return string
     */
    public function action(
        Request $request,
        HttpTransportInterface $httpClient,
        SerializerInterface $serializer,
        ContainerBagInterface $parameterBag,
        string $projectDir
    ) {

        return new Response('OK');
    }
}
