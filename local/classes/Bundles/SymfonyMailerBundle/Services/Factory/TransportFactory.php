<?php

namespace Local\Bundles\SymfonyMailerBundle\Services\Factory;

use Local\Bundles\SymfonyMailerBundle\Services\Transport\FileTransport;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Class TransportFactory
 * @package Local\Bundles\SymfonyMailerBundle\Services\Factory
 *
 * @since 02.03.2021
 *
 * @see https://github.com/maximaster/file-mailer-transport - основа.
 */
class TransportFactory
{
    /**
     * @var string $projectDir DOCUMENT_ROOT.
     */
    private $projectDir;

    /**
     * TransportFactory constructor.
     *
     * @param string $projectDir DOCUMENT_ROOT.
     */
    public function __construct(string $projectDir)
    {
        $this->projectDir = $projectDir;
    }

    /**
     * @param string                        $dsn        DSN.
     * @param Filesystem                    $filesystem Файловая система.
     * @param EventDispatcherInterface|null $dispatcher EventDispatcher.
     * @param HttpClientInterface|null      $client     Client.
     * @param LoggerInterface|null          $logger     Логгер.
     *
     * @return TransportInterface
     */
    public function __invoke(
        string $dsn,
        Filesystem $filesystem,
        EventDispatcherInterface $dispatcher = null,
        HttpClientInterface $client = null,
        LoggerInterface $logger = null
    ): TransportInterface {
        $url = parse_url($dsn);

        $parsedOptions = [];
        if (!empty($url['query'])) {
            parse_str($url['query'], $parsedOptions);
        }

        if ($url['scheme'] === 'file') {
            return new FileTransport(
                implode(DIRECTORY_SEPARATOR, [
                    $this->projectDir,
                    $url['path'],
                ]),
                $filesystem,
                $parsedOptions,
                $dispatcher,
                $logger
            );
        }

        return Transport::fromDsn($dsn, $dispatcher, $client, $logger);
    }
}
