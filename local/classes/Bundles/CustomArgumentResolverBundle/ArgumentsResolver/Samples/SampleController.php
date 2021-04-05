<?php

namespace Local\Bundles\CustomArgumentResolverBundle\ArgumentsResolver\Samples;

use Local\Bundles\CustomArgumentResolverBundle\Annotation\BitrixFile;
use Local\Bundles\CustomArgumentResolverBundle\Annotation\BitrixFileUrl;
use Local\Bundles\CustomArgumentResolverBundle\Annotation\QueryParams;
use Local\Bundles\CustomArgumentResolverBundle\Annotation\RequestBody;
use Local\Bundles\CustomArgumentResolverBundle\ArgumentsResolver\Services\BitrixFileParam;
use Local\Bundles\CustomArgumentResolverBundle\Examples\RequestBodyConverted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class SampleController
 * @package Local\Bundles\CustomArgumentResolverBundle\ArgumentsResolver\Samples
 */
class SampleController extends AbstractController
{
    /**
     * Параметры аннотации необязательны!
     *
     * @param RequestBodyConverted $unserialized
     *
     * @return JsonResponse $content
     *
     * @RequestBody(
     *          var="unserialized",
     *          class="Local\Bundles\CustomArgumentResolverBundle\Examples\RequestBodyConverted",
     *          validate=false
     * )
     */
    public function action(
        RequestBodyConverted $unserialized
    ): JsonResponse {
        var_dump($unserialized);

        return new JsonResponse(

        );
    }

    /**
     * Controller
     *
     * Параметры аннотации необязательны!
     *
     * @param RequestBodyConverted $unserialized
     * @return JsonResponse $content
     * @QueryParams(
    var="unserialized",
    class="Local\Bundles\CustomArgumentResolverBundle\Examples\RequestBodyConverted",
    validate=true
    )
     */
    public function action2(
        RequestBodyConverted $unserialized
    ): JsonResponse {
        var_dump($unserialized);

        return new JsonResponse(

        );
    }

    /**
     * Controller
     *
     * Параметры аннотации необязательны!
     *
     * @param BitrixFileParam $file
     * @return JsonResponse $content
     * @BitrixFile(
     *    var="file"
     * )
     */
    public function action4(
        BitrixFileParam $file
    ): JsonResponse {

        return new JsonResponse([
            'url' => $file->url()
        ]);
    }

    /**
     * Controller
     *
     * Параметры аннотации необязательны!
     *
     * @param string $file
     * @return JsonResponse $content
     * @BitrixFileUrl(
     *    var="file"
     * )
     */
    public function action5(
        string $file
    ): JsonResponse {

        return new JsonResponse([
            'url' => $file
        ]);
    }
}
