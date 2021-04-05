<?php

namespace Local\Bundles\RequestLogBundle\Tests;

use Local\Bundles\RequestLogBundle\Service\ResponseLogger;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ResponseLoggerTest
 * @package Local\Bundles\RequestLogBundle\Tests
 */
class ResponseLoggerTest extends TestCase
{
    /**
     * @var integer $umask
     */
    private $umask;

    /**
     * @var Filesystem $filesystem
     */
    private $filesystem;

    /**
     * @var string $workspace
     */
    private $workspace;

    /**
     * @var ResponseLogger $responseLogger
     */
    protected $responseLogger;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->umask = umask(0);
        $this->filesystem = new Filesystem();
        $this->workspace = $this->createTempDir();
        $this->responseLogger = new ResponseLogger($this->workspace);

        $this->filesystem->mkdir($this->workspace.DIRECTORY_SEPARATOR.'dir');
        $this->filesystem->touch($this->workspace.DIRECTORY_SEPARATOR.'file');
    }

    /**
     * @inheritDoc
     */
    protected function tearDown() : void
    {
        $this->filesystem->remove($this->workspace);
        umask($this->umask);
    }

    /**
     * @return void
     */
    public function testClearMocksDir() : void
    {
        $this->responseLogger->clearMocksDir();

        self::assertNotTrue(is_file($this->workspace.DIRECTORY_SEPARATOR.'file'));
        self::assertNotTrue(is_dir($this->workspace.DIRECTORY_SEPARATOR.'dir'));
        self::assertTrue(is_dir($this->workspace));
    }

    /**
     * @return void
     */
    public function testDumpMocksTo() : void
    {
        $targetPath = $this->createTempDir();
        $this->filesystem->touch($targetPath.DIRECTORY_SEPARATOR.'badfile');

        $this->responseLogger->dumpMocksTo($targetPath);

        self::assertTrue(is_file($targetPath.DIRECTORY_SEPARATOR.'file'));
        self::assertTrue(is_dir($targetPath.DIRECTORY_SEPARATOR.'dir'));
        self::assertNotTrue(is_file($targetPath.DIRECTORY_SEPARATOR.'badfile'));

        $this->filesystem->remove($targetPath);
    }

    /**
     * @return void
     */
    public function testLogGetReponse() : void
    {
        $request = Request::create('/categories?order[parent.name]=asc+desc&limit=5', 'GET');
        $response = Response::create(json_encode(['foo' => 'bar']), Response::HTTP_OK, ['Content-Type' => 'application/json']);
        $serializedResponse = json_encode(serialize($response));

        $file = $this->responseLogger->logResponse($request, $response);

        self::assertTrue(is_file($file));

        self::assertJsonStringEqualsJsonFile($file,'{
            "request": {
                "uri": "/categories?order[parent.name]=asc+desc&limit=5",
                "method": "GET",
                "parameters": [],
                "content": ""
            },
            "response": {
                "statusCode": 200,
                "contentType": "application/json",
                "content": {
                    "foo": "bar"
                },
                "serialized_response": ' . $serializedResponse . '
            }
        }'
        );
    }

    /**
     * @return void
     */
    public function testLogPostReponse() : void
    {
        $request = Request::create('/categories', 'POST', ['key' => 'value']);
        $response = Response::create('', Response::HTTP_CREATED);
        $serializedResponse = json_encode(serialize($response));

        $file = $this->responseLogger->logResponse($request, $response);

        self::assertTrue(is_file($file));

        self::assertJsonStringEqualsJsonFile($file, '{
            "request": {
                "uri": "/categories",
                "method": "POST",
                "parameters": {
                    "key": "value"
                },
                "content": ""
            },
            "response": {
                "statusCode": 201,
                "contentType": null,
                "content": "",
                "serialized_response": ' . $serializedResponse . '
            }
        }');
    }

    /**
     * @return void
     */
    public function testGetFilePathByRequest() : void
    {
        $request = Request::create('/categories', 'POST', ['key' => 'value']);

        $filename = $this->responseLogger->getFilePathByRequest($request);

        self::assertSame('categories/POST____22845.json', $filename);
    }

    /**
     * @return void
     */
    public function testGetEncodedFilePathByRequest() : void
    {
        $responseLogger = new ResponseLogger($this->workspace, true);
        $request = Request::create('/categories?order[foo]=asc&order[bar]=desc', 'GET');

        $filename = $responseLogger->getFilePathByRequest($request);

        self::assertSame('categories/GET__--90150.json', $filename);
    }

    /**
     * @return void
     */
    public function testGetNonIndexedArrayParams() : void
    {
        $responseLogger = new ResponseLogger($this->workspace, false, true);
        $request = Request::create('/categories?search[category][]=foo', 'GET');

        $filename = $responseLogger->getFilePathByRequest($request);

        self::assertSame('categories/GET__--search%5Bcategory%5D%5B0%5D=foo.json', $filename);
    }

    /**
     * @dataProvider requestsMocksNamesProvider
     *
     * @param Request $request
     * @param mixed   $expectedFilename
     *
     * @return void
     */
    public function testMockFilenames(Request $request, $expectedFilename)
    {
        $filename = $this->responseLogger->getFilePathByRequest($request);

        self::assertSame(
            $expectedFilename,
            $filename, sprintf('Invalid filename for request %s %s', $request->getMethod(), $request->getRequestUri()));
    }

    /**
     * @return array[]
     */
    public function requestsMocksNamesProvider() : array
    {
        return [
            [Request::create('/', 'GET'), 'GET__.json'],
            [Request::create('/categories', 'GET'), 'categories/GET__.json'],
            [Request::create('/categories?search[category][]=foo', 'GET'), 'categories/GET__--search%5Bcategory%5D%5B%5D=foo.json'],
            [Request::create('/categories?order[foo]=asc&order[bar]=desc', 'GET'), 'categories/GET__--order%5Bbar%5D=desc&order%5Bfoo%5D=asc.json'],
            [Request::create('/categories?parent=/my/iri&master.name=foo+bar.test', 'GET'), 'categories/GET__--master_name=foo+bar.test&parent=%2Fmy%2Firi.json'],
            [Request::create('/categories/1', 'GET'), 'categories/GET__1.json'],
            [Request::create('/categories/1/articles', 'GET'), 'categories/1/GET__articles.json'],
            [Request::create('/categories', 'POST', ['foo1' => 'bar1', 'foo2' => 'bar2']), 'categories/POST____3e038.json'],
            [Request::create('/categories', 'POST', ['foo1' => 'b/ar', 'foo2' => 'b&nbsp;ar']), 'categories/POST____293e3.json'],
            [Request::create('/categories', 'POST', ['foo2' => 'bar2', 'foo1' => 'bar1']), 'categories/POST____3e038.json'],
            [Request::create('/categories', 'POST', [], [], [], [], 'foobar'), 'categories/POST____8843d.json'],
            [Request::create('/categories', 'POST', [], [], [], [], json_encode(['foo' => 'bar'])), 'categories/POST____a5e74.json'],
            [Request::create('/categories', 'POST', ['foo2' => 'bar2', 'foo1' => 'bar1'], [], [], [], json_encode(['foo' => 'bar'])), 'categories/POST____a5e74__3e038.json'],
            [Request::create('/categories/1', 'PUT', [], [], [], [], json_encode(['foo' => 'bar'])), 'categories/PUT__1__a5e74.json'],
        ];
    }

    /**
     * @return string
     */
    private function createTempDir() : string
    {
        $dir = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.time().mt_rand(0, 1000);
        $this->filesystem->mkdir($dir, 0777);

        return realpath($dir);
    }
}
