<?php

namespace Local\Bundles\CustomArgumentResolverBundle\Tests\Cases\ArgumentResolvers\Tools;

use Local\Bundles\CustomArgumentResolverBundle\Annotation\RequestParams;
use Local\Bundles\CustomArgumentResolverBundle\Examples\RequestBodyConverted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class SampleControllerPost
 * @package Local\Bundles\CustomArgumentResolverBundle\Tests\Cases\ArgumentResolvers\Tools
 *
 * @since 03.04.2021
 */
class SampleControllerPost extends AbstractController
{
    /**
     * Параметры аннотации необязательны!
     *
     * @param RequestBodyConverted $unserialized
     *
     * @return JsonResponse $content
     * @RequestParams(
            var="unserialized",
            class="Local\Bundles\CustomArgumentResolverBundle\Examples\RequestBodyConverted",
            validate=true
        )
     */
    public function action(
        RequestBodyConverted $unserialized
    ): JsonResponse {
        return new JsonResponse();
    }

    /**
     * @param RequestBodyConverted $unserialized
     *
     * @return JsonResponse $content
     * @RequestParams(
            var="unserialized",
            class="Local\Bundles\CustomArgumentResolverBundle\Examples\RequestBodyConverted",
            validate=false
    )
     */
    public function actionNoValidate(
        RequestBodyConverted $unserialized
    ): JsonResponse {
        return new JsonResponse();
    }

    /**
     * @param RequestBodyConverted $unserialized
     *
     * @return JsonResponse $content
     * @RequestParams()
     */
    public function actionShort(
        RequestBodyConverted $unserialized
    ): JsonResponse {
        return new JsonResponse();
    }
}