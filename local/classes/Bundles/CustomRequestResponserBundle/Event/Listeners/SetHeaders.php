<?php

namespace Local\Bundles\CustomRequestResponserBundle\Event\Listeners;

use Local\Bundles\CustomRequestResponserBundle\Event\Interfaces\OnKernelResponseHandlerInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

/**
 * Class SetHeaders
 *
 * @package Local\Bundles\CustomRequestResponserBundle\Event\Listeners
 *
 * @since 20.10.2020
 */
final class SetHeaders implements OnKernelResponseHandlerInterface
{
    /** @var ExpressionLanguage $expressionLanguage */
    private $expressionLanguage;

    /** @var array $headers */
    private $headers;

    /**
     * SetHeaders constructor.
     *
     * @param ExpressionLanguage $expressionLanguage
     * @param array              $headers
     */
    public function __construct(ExpressionLanguage $expressionLanguage, array $headers)
    {
        $this->expressionLanguage = $expressionLanguage;
        $this->headers = $headers;
    }

    /**
     * @inheritDoc
     */
    public function handle(ResponseEvent $event): void
    {
        // Фильтрация внешних нативных маршрутов.
        if (!$event->isMasterRequest()
            ||
            $event->getResponse()->getStatusCode() === 404
        ) {
            return;
        }

        $response = $event->getResponse();

        $evaluationValues = [
            'request' => $event->getRequest(),
            'response' => $event->getResponse(),
        ];

        foreach ($this->headers['headers'] as $header) {
            if (isset($header['condition'])
                &&
                (bool)$this->expressionLanguage->evaluate($header['condition'], $evaluationValues) !== true) {
                continue;
            }

            $response->headers->set($header['name'], $header['value']);
        }
    }
}
