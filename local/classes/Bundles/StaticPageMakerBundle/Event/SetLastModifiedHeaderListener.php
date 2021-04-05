<?php

namespace Local\Bundles\StaticPageMakerBundle\Event;

use Local\Bundles\StaticPageMakerBundle\Services\Utils\TwigUtils;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Carbon\Carbon;

/**
 * Class SetLastModifiedHeaderListener
 * @package Local\Bundles\StaticPageMakerBundle\Event
 *
 * @since 30.01.2021
 */
final class SetLastModifiedHeaderListener
{
    /**
     * @var TwigUtils $twigUtils Утилиты для работы с Твигом.
     */
    private $twigUtils;

    /**
     * @var boolean $setHeader
     */
    private $setHeader;

    /**
     * SetLastModifiedHeaderListener constructor.
     *
     * @param TwigUtils $twigUtils Утилиты для работы с Твигом.
     * @param boolean   $setHeader Устанавливать заголовки? (опция бандла)
     */
    public function __construct(
        TwigUtils $twigUtils,
        bool $setHeader
    ) {
        $this->twigUtils = $twigUtils;
        $this->setHeader = $setHeader;
    }

    /**
     * @param ResponseEvent $event
     *
     * @return void
     */
    public function handle(ResponseEvent $event): void
    {
        // Фильтрация внешних нативных маршрутов и неподходящих роутов.
        if (!$event->isMasterRequest()
            ||
            !$this->setHeader
            ||
            $event->getResponse()->getStatusCode() === 404
            ||
            !$event->getRequest()->attributes->get('template')
        ) {
            return;
        }

        $response = $event->getResponse();
        $template = $event->getRequest()->attributes->get('template');

        $timestamp = $this->twigUtils->getModifiedTimeTemplate($template);
        if ($timestamp === 0) {
            return;
        }

        if (get_class($response) === Response::class) {
            $lastModified = Carbon::createFromTimestamp($timestamp);
            $response->setLastModified($lastModified);

            $response->setPublic();

            if ($response->isNotModified($event->getRequest())) {
                $response->setStatusCode(304);
            }
        }
    }
}