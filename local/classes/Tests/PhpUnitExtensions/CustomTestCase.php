<?php

namespace Local\Tests\PhpUnitExtensions;

use CModule;
use Faker\Factory;
use Faker\Generator;
use Local\ServiceProvider\ServiceProvider;
use Local\Tests\FixtureGenerator\FixtureServiceProvider;
use Local\Tests\PHPUnitTrait;
use Local\Tests\PHPUnitUtils;
use Mockery;
use ReflectionClass;
use ReflectionException;
use Symfony\Component\DependencyInjection\Container;

/**
 * Class CustomTestCase
 * @package Local\Tests\PhpUnitExtensions
 */
class CustomTestCase extends TestCase
{
    /**
     * @internal Используемые аннотации классов:
     *
     * @backupSymfonyService - делать бэкап сервис-контейнера Symfony.
     * @clearSymfonyService - очищать перед тестом сервис-контейнер Symfony.
     */

    use PHPUnitTrait;

    /** @var array $backupGlobalsBlacklist Не бэкапить $DB. */
    protected $backupGlobalsBlacklist = ['DB'];

    /** @var Container $backupSymfonyServiceContainer Symfony Service container backup. */
    protected $backupSymfonyServiceContainer;

    /** @var mixed $obTestObject */
    protected $obTestObject;
    /** @var Generator | null $faker */
    protected $faker;

    /**
     * @beforeClass
     */
    public static function setUpSomeSharedFixtures()
    {
        // Сервис-провайдер генератора фикстур.
        (new FixtureServiceProvider())->register();
    }

    protected function setUp(): void
    {
        $annotationClass = $this->getAnnotationClass();

        // Backup сервис-контейнера
        if ($this->needBackupSymfonyServices($annotationClass)) {
            /** @noinspection PhpUndefinedMethodInspection */
            $this->backupSymfonyServiceContainer = ServiceProvider::instance();

            if ($this->needClearSymfonyServices($annotationClass)) {
                $this->clearStaticProperty(
                    ServiceProvider::class,
                    'containerBuilder'
                );
            }
        }

        Mockery::resetContainer();
        parent::setUp();

        $this->faker = Factory::create();
    }

    protected function tearDown(): void
    {
        // Восстановить Symfony Service контейнер.
        if (!empty($this->backupSymfonyServiceContainer)) {
            PHPUnitUtils::setStaticProperty(
                ServiceProvider::class,
                'containerBuilder',
                $this->backupSymfonyServiceContainer
            );
        }

        parent::tearDown();

        Mockery::close();
    }

    /**
     * Test iblock module.
     */
    public function testIblockModule() : void
    {
        $this->assertTrue(
            CModule::includeModule('iblock'),
            'Модуль iblock не подключен.'
        );
    }

    /**
     * Делать копию сервис-контейнера?
     *
     * @param array $annotations Аннотации.
     *
     * @return boolean
     */
    private function needBackupSymfonyServices(array $annotations = []) : bool
    {
        return $this->searchAnnotation($annotations, '@backupSymfonyService');
    }

    /**
     * Очищать сервис-контейнер?
     *
     * @param array $annotations Аннотации.
     *
     * @return boolean
     */
    private function needClearSymfonyServices(array $annotations = []) : bool
    {
        return $this->searchAnnotation($annotations, '@clearSymfonyService');
    }

    /**
     * Поиск в аннотациях класса.
     *
     * @param array $annotations
     * @param string $key
     *
     * @return boolean
     */
    private function searchAnnotation(array $annotations, string $key) : bool
    {
        foreach ($annotations[0] as $line) {
            if (stripos($line, $key) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Аннотация текущего класса.
     *
     * @return array
     */
    private function getAnnotationClass() : array
    {
        try {
            $rc = new ReflectionClass(get_class($this));
        } catch (ReflectionException $e) {
            return [];
        }

        $doc = $rc->getDocComment();
        preg_match_all('#@(.*?)\n#s', $doc, $annotations);

        if (!is_array($annotations)) {
            return [];
        }

        return $annotations;
    }
}
