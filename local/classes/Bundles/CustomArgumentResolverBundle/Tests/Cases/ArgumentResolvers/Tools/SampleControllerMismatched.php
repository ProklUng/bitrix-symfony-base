<?php

namespace Local\Bundles\CustomArgumentResolverBundle\Tests\Cases\ArgumentResolvers\Tools;

use Local\Bundles\CustomArgumentResolverBundle\Annotation\QueryParams;
use Local\Bundles\CustomArgumentResolverBundle\Annotation\RequestBody;
use Local\Bundles\CustomArgumentResolverBundle\Annotation\RequestParams;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class SampleControllerMismatched
 * @package Local\Bundles\CustomArgumentResolverBundle\Tests\Cases\ArgumentResolvers\Tools
 *
 * @since 03.04.2021
 */
class SampleControllerMismatched extends AbstractController
{
    /**
     * Параметры аннотации необязательны!
     *
     * @param string $unserialized
     *
     * @return JsonResponse $content
     * @RequestParams(
            var="unserialized",
            class="Local\Bundles\CustomArgumentResolverBundle\Examples\RequestBodyConverted",
            validate=true
        )
     */
    public function action(
        string $unserialized
    ): JsonResponse {
        return new JsonResponse();
    }

    /**
     * Параметры аннотации необязательны!
     *
     * @param string $unserialized
     *
     * @return JsonResponse $content
     * @QueryParams(
            var="unserialized",
            class="Local\Bundles\CustomArgumentResolverBundle\Examples\RequestBodyConverted",
            validate=true
    )
     */
    public function action2(
        string $unserialized
    ): JsonResponse {
        return new JsonResponse();
    }

    /**
     * Параметры аннотации необязательны!
     *
     * @param string $unserialized
     *
     * @return JsonResponse $content
     * @RequestBody(
    var="unserialized",
    class="Local\Bundles\CustomArgumentResolverBundle\Examples\RequestBodyConverted",
    validate=true
    )
     */
    public function action3(
        string $unserialized
    ): JsonResponse {
        return new JsonResponse();
    }
}