<?php

namespace Local\Bundles\CustomArgumentResolverBundle\Tests\Cases\ArgumentResolvers\Tools;

use Local\Bundles\CustomArgumentResolverBundle\Annotation\QueryParams;
use Local\Bundles\CustomArgumentResolverBundle\Examples\RequestBodyConverted;
use Local\Bundles\CustomArgumentResolverBundle\Examples\RequestBodyConvertedSpatie;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class SampleController
 * @package Local\Bundles\CustomArgumentResolverBundle\Tests\Cases\ArgumentResolvers\Tools
 *
 * @since 03.04.2021
 */
class SampleController extends AbstractController
{
    /**
     * Controller
     *
     * Параметры аннотации необязательны!
     *
     * @param RequestBodyConverted $unserialized
     *
     * @return JsonResponse $content
     * @QueryParams(
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
     *
     * @param RequestBodyConverted $unserialized
     *
     * @return JsonResponse $content
     * @QueryParams(
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
     * @QueryParams()
     */
    public function actionShort(
        RequestBodyConverted $unserialized
    ): JsonResponse {
        return new JsonResponse();
    }

    /**
     * Controller
     *
     * Параметры аннотации необязательны!
     *
     * @param RequestBodyConvertedSpatie $unserialized
     *
     * @return JsonResponse $content
     * @QueryParams(
            var="unserialized",
            class="Local\Bundles\CustomArgumentResolverBundle\Examples\RequestBodyConvertedSpatie",
          validate=true
    )
     */
    public function actionSpatie(
        RequestBodyConvertedSpatie $unserialized
    ): JsonResponse {
        return new JsonResponse();
    }
}