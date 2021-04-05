<?php

namespace Local\SymfonyTools\Framework\Examples;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SampleController extends AbstractController
{
    /**
     * @param Request $request
     * @return string
     */
    public function action(
        Request $request
    ) {
        return new Response('OK');
    }
}
