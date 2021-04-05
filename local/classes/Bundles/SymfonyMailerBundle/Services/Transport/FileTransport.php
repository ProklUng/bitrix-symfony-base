<?php

namespace Local\Bundles\SymfonyMailerBundle\Services\Transport;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;

/**
 * Class FileTransport
 * @package Local\Bundles\SymfonyMailerBundle\Services\Transport
 *
 * @since 02.03.2021
 *
 * @see https://github.com/maximaster/file-mailer-transport - основа, основательно
 * переделанная.
 */
class FileTransport extends AbstractTransport
{
    /**
     * @var Filesystem $filesystem Файловая система.
     */
    private $filesystem;

    /**
     * @var string $pathTemplate Базовая директория.
     */
    private $pathTemplate;

    /**
     * @var LoggerInterface $logger Логгер.
     */
    private $logger;

    /**
     * @var array $options Опции.
     */
    private $options = [
        'new_directory_mode' => 0777,
        'hash_algo' => 'crc32',
        'path_renderer' => 'strftime',
    ];

    /**
     * FileTransport constructor.
     *
     * @param string                        $baseDirectory DOCUMENT_ROOT.
     * @param Filesystem                    $filesystem    Файловая система.
     * @param array                         $options       Опции.
     * @param EventDispatcherInterface|null $dispatcher    EventDispatcher.
     * @param LoggerInterface|null          $logger        Логгер.
     */
    public function __construct(
        string $baseDirectory,
        Filesystem $filesystem,
        array $options = [],
        EventDispatcherInterface $dispatcher = null,
        LoggerInterface $logger = null
    ) {
        parent::__construct($dispatcher, $logger);

        $this->filesystem = $filesystem;

        $this->logger = $logger ?: new NullLogger;
        $this->pathTemplate = $baseDirectory;
        $this->options = array_merge($this->options, $options);
    }

    /**
     * "Отправка".
     *
     * @param SentMessage $message Сообщение.
     *
     * @return void
     * @throws IOException Ошибки файловой системы.
     */
    protected function doSend(SentMessage $message): void
    {
        $filePath = $this->buildPath($this->pathTemplate, $message);

        $fileDir = dirname($filePath);

        if (!$this->filesystem->exists($fileDir)) {
            $this->filesystem->mkdir($fileDir, $this->options['new_directory_mode']);
        }

        try {
            $this->filesystem->dumpFile($filePath, $message->toString());
        } catch (IOException $e) {
            $this->logger->error(sprintf('Unable to save message as "%s"', $filePath));
        }
    }

    /**
     * Построить путь.
     *
     * @param string      $template Шаблон.
     * @param SentMessage $message  Сообщение.
     *
     * @return string
     */
    private function buildPath(string $template, SentMessage $message): string
    {
        $path = str_replace(
            '@hash',
            hash($this->options['hash_algo'], $message->getMessage()->toString()),
            $template
        );

        $path = str_replace(['{', '}'], ['%', ''], $path);

        $path = !empty($this->options['path_renderer']) && is_callable($this->options['path_renderer'])
                ? call_user_func_array($this->options['path_renderer'], [$path]) : strftime($path);

        return $path;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return 'file';
    }
}
