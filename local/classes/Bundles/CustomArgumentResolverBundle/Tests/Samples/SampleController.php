<?php
/** @noinspection PhpUnusedParameterInspection */

namespace Local\Bundles\CustomArgumentResolverBundle\Tests\Samples;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Class SampleController
 * @package Tests\Events\Samples
 * @codeCoverageIgnore
 */
class SampleController extends AbstractController
{
    public const TEST_CONSTANT = 'OK3';

    public function action(
        Request $request,
        SampleControllerDependency $obj
    ) {
        return new Response('OK');
    }

    public function action2(
        Request $request,
        SessionInterface $session
    ) {
        return new Response('OK');
    }

    public function action3(
        Request $request,
        string $value = 'OK'
    ) {
        return new Response('OK');
    }

    public function action4(
        Request $request,
        string $value = self::TEST_CONSTANT,
        array $array = [1, 2, 3]
    ) {
        return new Response('OK');
    }

    public function action5(
        Request $request,
        string $value = self::TEST_CONSTANT,
        array $array = [1, 2, ['%kernel.cache_dir%'], ['@session.instance']]
    ) {
        return new Response('OK');
    }

    public function action6(
        Request $request,
        string $value = '@invalid.service'
    ) {
        return new Response('OK');
    }

    public function action7(
        Request $request,
        string $value = '%invalid.variable%'
    ) {
        return new Response('OK');
    }

    public function action8(
        Request $request,
        string $value,
        int $id
    ) {
        return new Response('OK');
    }
}
