<?php

namespace Local\Bundles\CustomRequestResponserBundle\Event\Listeners;

use Local\Bundles\CustomRequestResponserBundle\Event\Interfaces\OnKernelResponseHandlerInterface;
use Local\Bundles\CustomRequestResponserBundle\Services\Contracts\IndexRouteManagerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

/**
 * Class Robots
 * @package Local\Bundles\CustomRequestResponserBundle\Event\Listeners
 *
 * Управление индексацией роута.
 *
 * @since 18.02.2021
 */
final class Robots implements OnKernelResponseHandlerInterface
{
    use ContainerAwareTrait;

    /**
     * @inheritDoc
     */
    public function handle(ResponseEvent $event): void
    {
        $request = $event->getRequest();
        $response = $event->getResponse();

        $contents = $this->shouldIndex($request) ? 'all' : 'none';
        $response->headers->set('x-robots-tag', $contents, false);
    }

    /**
     * Нужно индексировать? Параметр роута _no_index.
     *
     * @param Request $request Request.
     *
     * @return boolean
     */
    private function shouldIndex(Request $request) : bool
    {
        $value = $request->get('_no_index');
        if (is_bool($value)) {
            return $value !== true;
        }

        if (is_string($value) && $this->container !== null && $this->container->has($value)) {
            /** @var IndexRouteManagerInterface $service */
            $service = $this->container->get($value);
            $interfaces = class_implements($service);
            if (in_array(IndexRouteManagerInterface::class, $interfaces, true)) {
                return $service->shouldIndex($request);
            }
        }

        return true;
    }
}
