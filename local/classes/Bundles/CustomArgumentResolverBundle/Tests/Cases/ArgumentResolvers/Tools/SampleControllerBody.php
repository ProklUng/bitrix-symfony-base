<?php

namespace Local\Bundles\CustomArgumentResolverBundle\Tests\Cases\ArgumentResolvers\Tools;

use Local\Bundles\CustomArgumentResolverBundle\Annotation\RequestBody;
use Local\Bundles\CustomArgumentResolverBundle\Examples\RequestBodyConverted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class SampleControllerBody
 * @package Local\Bundles\CustomArgumentResolverBundle\Tests\Cases\ArgumentResolvers\Tools
 *
 * @since 03.04.2021
 */
class SampleControllerBody extends AbstractController
{
    /**
     * Параметры аннотации необязательны!
     *
     * @param RequestBodyConverted $unserialized
     *
     * @return JsonResponse $content
     * @RequestBody(
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
     * Параметры аннотации необязательны!
     *
     * @param RequestBodyConverted $unserialized
     *
     * @return JsonResponse $content
     * @RequestBody(
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
     * @RequestBody()
     */
    public function actionShort(
        RequestBodyConverted $unserialized
    ): JsonResponse {
        return new JsonResponse();
    }
}