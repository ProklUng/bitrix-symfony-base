<?php

namespace Local\Bundles\SymfonyMiddlewareBundle\Examples\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class SimplestValidateController
 * @package Local\Controller
 *
 * @since 26.11.2020
 */
class SimplestValidateController extends AbstractController
{
    public function action(Request $request)
    {
        return 'OK';
    }

    /**
     * Санация.
     *
     * @return array
     */
    protected function getSanitizingRulesAction() : array
    {
        return [
            'url' => 'trim|escape|strip_tags|cast:string',
        ];
    }

    /**
     * Валидация.
     *
     * @return array
     */
    protected function getRulesAction() : array
    {
        return [
            'id' => 'numeric',
            'url' => 'string|required_without:id',
        ];
    }
}
