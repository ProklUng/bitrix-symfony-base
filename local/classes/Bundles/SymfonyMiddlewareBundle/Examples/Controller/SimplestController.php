<?php

namespace Local\Bundles\SymfonyMiddlewareBundle\Examples\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class SimplestController
 * @package Local\Controller
 *
 * @since 19.11.2020
 */
class SimplestController extends AbstractController
{
    public function action(Request $request)
    {
        return 'OK';
    }
}
