<?php

namespace Local\Bundles\BitrixComponentParamsBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

/**
 * Class CreateNewsDTOCommand
 * @package Local\Commands
 *
 * @since 26.02.2021
 */
class CreateNewsDTOCommand extends Command
{
    /**
     * @var Environment $twig Твиг.
     */
    private $twig;

    /**
     * @var string $pathTemplates Путь к твиговским шаблонам.
     */
    private $pathTemplates;

    /**
     * @var string $arraysPath Путь, где лежат массивы-заготовки.
     */
    private $arraysPath;

    /**
     * @var string $dtoReadyPath Путь, куда лягут готовые DTO.
     */
    private $dtoReadyPath;

    /**
     * CreateNewsDTOCommand constructor.
     *
     * @param Environment    $twig             Твиг.
     * @param ServiceLocator $customExtensions Локальные расширения Твига.
     * @param string         $dtoReadyPath     Путь, куда лягут готовые DTO.
     * @param string         $arraysPath       Путь, где лежат массивы-заготовки.
     */
    public function __construct(
        Environment $twig,
        ServiceLocator $customExtensions,
        string $dtoReadyPath,
        string $arraysPath
    ) {
        $this->twig = $twig;
        $this->arraysPath = $arraysPath;
        $this->dtoReadyPath = $dtoReadyPath;

        foreach ($customExtensions->getProvidedServices() as $twigExtension => $value) {
            $this->twig->addExtension($customExtensions->get($twigExtension));
        }

        parent::__construct();
    }

    /**
     * @inheritdoc
     */
    protected function configure(): void
    {
        $this
            ->setName('dto:create')
            ->setDescription('Создать DTO из массива')
            ->setDefinition([
                new InputArgument('dto', InputArgument::REQUIRED, 'Класс DTO'),
                new InputArgument('array', InputArgument::REQUIRED, 'Файл с массивом'),
            ]);
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dto = $input->getArgument('dto');
        $array = $input->getArgument('array');

        /** @noinspection PhpIncludeInspection */
        $arrayDto = require $_SERVER['DOCUMENT_ROOT'] . $this->arraysPath .'/' . $array;

        if (!$arrayDto) {
            $output->writeln('Не смог прочитать массив из файла.');

            return;
        }

        try {
            $rendered = $this->twig->render(
                $this->pathTemplates . '/dtoNews.twig',
                [
                    'data' => $arrayDto,
                    'className' => $dto,
                ]
            );
        } catch (LoaderError | RuntimeError | SyntaxError $e) {
            $output->writeln('Ошибка рендеринга '.$e->getMessage());

            return;
        }

        file_put_contents(
            $_SERVER['DOCUMENT_ROOT'].$this->dtoReadyPath.'/'.$dto.'.php',
            $rendered
        );

        $output->writeln('Процесс завершен');
    }
}
