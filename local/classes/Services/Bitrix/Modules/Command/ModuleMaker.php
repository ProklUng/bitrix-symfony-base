<?php
namespace Local\Services\Bitrix\Modules\Command;

use Exception;
use InvalidArgumentException;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Loader\FilesystemLoader;

/**
 * Class ModuleMaker
 *
 * @package Local\Services\Bitrix\Modules\Command
 *
 * @since 14.04.2021
 */
class ModuleMaker extends Command
{
    /**
     * @var Environment $twig Твиг.
     */
    private $twig;

    /**
     * @var Filesystem $filesystem Файловая система.
     */
    private $filesystem;

    /**
     * @var string $basePath Базовый путь к модулям.
     */
    private $basePath = '/local/modules/';

    /**
     * @var array $twigContext Твиговский контекст.
     */
    private $twigContext = [];

    /**
     * ModuleMaker constructor.
     *
     * @param Filesystem $filesystem Файловая система.
     */
    public function __construct(Filesystem $filesystem)
    {
        $loader = new FilesystemLoader(
            [$_SERVER['DOCUMENT_ROOT'] . '/local/classes/Services/Bitrix/Modules/Command/templates']
        );

        $this->twig = new Environment($loader);

        $this->filesystem = $filesystem;
        $this->basePath = $_SERVER['DOCUMENT_ROOT'] . $this->basePath;
        parent::__construct();
    }

    /**
     * @inheritDoc
     */
    public function configure() : void
    {
        $this->setName('maker:module')
             ->setDescription('Генерация заготовки модуля')
             ->addArgument('name', InputArgument::REQUIRED, 'Название модуля без вендора (пример example.module)')
             ->addArgument('vendor', InputArgument::REQUIRED, 'Вендор модуля (то, что идет до :, пример - fedy)')
             ->addOption('entity', 'e',InputOption::VALUE_OPTIONAL, 'Генерировать сущность таблицы', 'true')
             ->addOption('admin', 'a',InputOption::VALUE_OPTIONAL, 'Генерировать админку', 'true')
             ->addOption('serviceprovider', 'sp',InputOption::VALUE_OPTIONAL, 'Генерировать админку', 'false')
        ;
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->validateParameters($input);

        $moduleName = $input->getArgument('name');
        $moduleVendor = $input->getArgument('vendor');

        $needGenerateEntity = trim($input->getOption('entity')) === 'true';
        $needGenerateAdmin = trim($input->getOption('admin')) === 'true';
        $needGenerateServiceProvider = trim($input->getOption('serviceprovider')) === 'true';

        $this->basePath .= ($moduleVendor . '.' . $moduleName);

        $this->twigContext = [
            'module_name' => $moduleName,
            'module_name_withoutspaces' => str_replace(['.', '_', '-'], '', $moduleName),
            'module_class_service_provider' => ucfirst(str_replace(['.', '_', '-'], '', $moduleName)),
            'module_name_converted' => str_replace(['.', '-'], '_', $moduleName),
            'module_id' => $moduleVendor . '.' .  $moduleName,
            'module_vendor' => $moduleVendor,
            'module_lang_suffix' => strtoupper(
                str_replace(['.', '-'], '_', $moduleName)
            ),
            'module_class' => $moduleVendor .'_'. str_replace(['.', '-'], '_', $moduleName),
            'current_date' => date('Y-m-d'),
            'namespace' => ucfirst($moduleVendor) . $this->getClassEntity($moduleName),
            'generate_entity' => $needGenerateEntity,
            'generate_admin' => $needGenerateAdmin,
            'generate_micro_serviceprovider' => $needGenerateServiceProvider,
        ];

        $output->writeln('Начинаю создание модуля');
        $this->createDestinationStructure();
        $output->writeln('Структура директорий модуля создана');

        if ($needGenerateAdmin) {
            $this->renderMenuPage();
            $this->twigContext['admin_filename'] = $this->renderAdminPage($moduleName);
        }

        if ($needGenerateServiceProvider) {
            $output->writeln('Генерация микро-сервис-провайдера.');
            $this->renderServiceProvider();
        }

        $this->renderInstallPhp();
        $this->renderVersionPhp();
        $this->renderLang();

        if ($needGenerateEntity) {
            $this->renderEntity();
        }

        $this->renderIncludePhp();
        $this->renderOthers();

        $output->writeln('Модуль создан.');

        return 0;
    }

    /**
     * Создать директории под модуль.
     *
     * @return void
     * @throws RuntimeException Когда базовая директория уже существует.
     */
    private function createDestinationStructure() : void
    {
        if ($this->filesystem->exists($this->basePath)) {
            throw new RuntimeException(
                'Директория ' . $this->basePath . ' уже существует'
            );
        }

        $this->filesystem->mkdir(
            [
                $this->basePath,
                $this->basePath . '/admin',
                $this->basePath . '/install',
                $this->basePath . '/install/admin',
                $this->basePath . '/lang',
                $this->basePath . '/lang/ru',
                $this->basePath . '/lang/ru/admin',
                $this->basePath . '/lang/ru/install',
                $this->basePath . '/lib',
            ]
        );

        // Директории для микро-сервис провайдера.
        if ($this->twigContext['generate_micro_serviceprovider']) {
            $this->filesystem->mkdir(
                [
                    $this->basePath,
                    $this->basePath . '/config',
                    $this->basePath . '/config/packages',
                ]
            );
        }
    }

    /**
     * @return void
     */
    private function renderServiceProvider() : void
    {
        $this->baseRender('./microServiceProvider/ModuleServiceProvider.twig', '/lib/ModuleServiceProvider.php');
        $this->baseRender('./microServiceProvider/services.twig', '/config/services.yaml');
        $this->baseRender('./microServiceProvider/standalone_bundles.twig', '/lib/standalone_bundles.php');
    }

    /**
     * @return void
     * @throws RuntimeException Когда не удалось сгенерировать шаблон или записать файл.
     */
    private function renderMenuPage() : void
    {
        $this->baseRender('./admin/menu.twig', '/admin/menu.php');
    }

    /**
     * @param string $moduleName Название модуля.
     *
     * @return string
     * @throws RuntimeException Когда не удалось сгенерировать шаблон или записать файл.
     */
    private function renderAdminPage(string $moduleName) : string
    {
        $filename = str_replace(['.', '_', '-'], '_', $moduleName);
        $filename .= '_index';

        $this->baseRender('./install/admin/admin_form.twig', '/install/admin/' . $filename . '.php');

        return $filename;
    }

    /**
     * @return void
     * @throws RuntimeException Когда не удалось сгенерировать шаблон или записать файл.
     */
    private function renderInstallPhp() : void
    {
        $this->baseRender('./install/install.twig', '/install/index.php');
    }

    /**
     * @return void
     * @throws RuntimeException Когда не удалось сгенерировать шаблон или записать файл.
     */
    private function renderVersionPhp() : void
    {
        $this->baseRender('./install/version.twig', '/install/version.php');
    }

    /**
     * @return void
     * @throws RuntimeException Когда не удалось сгенерировать шаблон или записать файл.
     */
    private function renderLang() : void
    {
        $arFiles = [
            './lang/options.twig' => '/lang/ru/options.php',
            './lang/admin/menu.twig' => '/lang/ru/admin/menu.php',
            './lang/install/index.twig' => '/lang/ru/install/index.php',
        ];

        foreach ($arFiles as $source => $target) {
            $this->baseRender($source, $target);
        }
    }

    /**
     * @return void
     * @throws RuntimeException Когда не удалось сгенерировать шаблон или записать файл.
     */
    private function renderEntity() : void
    {
        $this->baseRender('./lib/entity.twig', '/lib/moduletable.php');
    }

    /**
     * @return void
     * @throws RuntimeException Когда не удалось сгенерировать шаблон или записать файл.
     */
    private function renderIncludePhp() : void
    {
        $this->baseRender('./include.twig', '/include.php');
    }

    /**
     * @return void
     * @throws RuntimeException Когда не удалось сгенерировать шаблон или записать файл.
     */
    private function renderOthers() : void
    {
        $this->baseRender('./default_option.twig', './default_option.php');
        $this->baseRender('./options.twig', './options.php');
    }

    /**
     * @param string $from Откуда генерировать. Твиговский шаблон.
     * @param string $to   Куда записывать. PHP файл.
     *
     * @return void
     * @throws RuntimeException Когда не удалось сгенерировать шаблон или записать файл.
     */
    private function baseRender(string $from, string $to) : void
    {
        try {
            $html = $this->twig->render(
                $from,
                $this->twigContext
            );
        } catch (LoaderError | RuntimeError | SyntaxError $e) {
            $this->revert($from);
            return;
        }

        $result = file_put_contents(
            $this->basePath . $to,
            $html
        );

        if ($result === false) {
            $this->revert($this->basePath . $from);
        }
    }

    /**
     * Откат назад в случае любой ошибки - удаление созданных директорий и папок.
     *
     * @param string $path Базовый путь к модулю.
     *
     * @return void
     * @throws RuntimeException Когда что-то пошло не так с созданием файлов.
     */
    private function revert(string $path) : void
    {
        $this->rrmdir($this->basePath);

        throw new RuntimeException(
            'Ошибка создания файла по адресу ' . $path
        );
    }

    /**
     * @param string $moduleName Должен быть в формате (xxxx.xxxx).
     *
     * @return string
     */
    private function getClassEntity(string $moduleName) : string
    {
        $exploded = explode('.', $moduleName);
        $result = '';
        foreach ($exploded as $item) {
            $namespace = '\\' . ucfirst($item);
            $result .= $namespace;
        }

        return $result;
    }

    /**
     * Рекурсивно удалить папки и файлы в них.
     *
     * @param string $dir Директория.
     *
     * @return void
     */
    private function rrmdir(string $dir) : void
    {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object !== '.' && $object !== '..') {
                    if (is_dir($dir.DIRECTORY_SEPARATOR.$object) && !is_link($dir.'/'.$object)) {
                        $this->rrmdir($dir.DIRECTORY_SEPARATOR.$object);
                    } else {
                        unlink($dir.DIRECTORY_SEPARATOR.$object);
                    }
                }
            }
            rmdir($dir);
        }
    }

    /**
     * Валидация входящих параметров.
     *
     * @param InputInterface $input
     *
     * @return void
     *
     * @throws InvalidArgumentException
     */
    private function validateParameters(InputInterface $input) : void
    {
        if (!is_string($input->getArgument('vendor'))) {
            throw new InvalidArgumentException(
                'Параметр vendor должен быть только строкой.'
            );
        }

        if (!is_string($input->getArgument('name'))) {
            throw new InvalidArgumentException(
                'Параметр name должен быть только строкой.'
            );
        }
    }
}
