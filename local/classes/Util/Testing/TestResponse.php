<?php

namespace Local\Util\Testing;

use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Traits\Macroable;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Response;
use PHPUnit\Framework\Assert as PHPUnit;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * Class TestResponse
 * @package Local\Util\Testing
 *
 * @see https://raw.githubusercontent.com/laravel/framework/5.4/src/Illuminate/Foundation/Testing/TestResponse.php
 * Response Laravel заменен на Response Symfony. Переработка под собственные нужды.
 *
 * @since 18.09.2020
 */
class TestResponse
{
    use Macroable {
        __call as macroCall;
    }

    /**
     * The response to delegate to.
     *
     * @var Response
     */
    public $baseResponse;

    /**
     * @var ResponseHeaderBag $headers Заголовки.
     */
    public $headers;

    /**
     * Create a new test response instance.
     *
     * @param Response $response
     */
    public function __construct($response)
    {
        $this->baseResponse = $response;
        $this->headers = $this->baseResponse->headers;
    }

    /**
     * Create a new TestResponse from another response.
     *
     * @param $response
     *
     * @return static
     */
    public static function fromBaseResponse($response)
    {
        return new static($response);
    }

    /**
     * Код ответа.
     *
     * @return integer
     */
    public function getStatusCode(): int
    {
        return $this->baseResponse->getStatusCode();
    }

    /**
     * Контент ответа.
     *
     * @return string
     */
    public function getContent(): string
    {
        return $this->baseResponse->getContent();
    }

    /**
     * Успех.
     *
     * @return boolean
     */
    public function isSuccessful() : bool
    {
        return $this->baseResponse->getStatusCode() === 200;
    }

    /**
     * Assert that the response has a successful status code.
     *
     * @return $this
     */
    public function assertSuccessful(): self
    {
        PHPUnit::assertTrue(
            $this->isSuccessful(),
            'Response status code ['.$this->getStatusCode().'] is not a successful status code.'
        );

        return $this;
    }


    /**
     * Assert that the response has a not successful status code.
     *
     * @return $this
     */
    public function assertNotSuccessful(): self
    {
        PHPUnit::assertFalse(
            $this->isSuccessful(),
            'Response status code ['.$this->getStatusCode().'] is not a successful status code.'
        );

        return $this;
    }

    /**
     * Assert that the response has the given status code.
     *
     * @param integer $status HTTP код ответа.
     *
     * @return $this
     */
    public function assertStatus($status): self
    {
        $actual = $this->getStatusCode();

        PHPUnit::assertSame(
            $actual,
            $status,
            "Expected status code {$status} but received {$actual}."
        );

        return $this;
    }

    /**
     * Asserts that the response contains the given header and equals the optional value.
     *
     * @param string $headerName Заголовок.
     * @param mixed  $value      Значение.
     *
     * @return $this
     */
    public function assertHeader($headerName, $value = null): self
    {
        PHPUnit::assertTrue(
            $this->headers->has($headerName),
            "Header [{$headerName}] not present on response."
        );

        $actual = $this->headers->get($headerName);

        if (!is_null($value)) {
            PHPUnit::assertEquals(
                $value,
                $this->headers->get($headerName),
                "Header [{$headerName}] was found, but value [{$actual}] does not match [{$value}]."
            );
        }

        return $this;
    }

    /**
     * Asserts that the response contains the given cookie and equals the optional value.
     *
     * @param string $cookieName Кука.
     * @param mixed  $value      Значение.
     *
     * @return $this
     */
    public function assertPlainCookie($cookieName, $value = null): self
    {
        $this->assertCookie(
            $cookieName,
            $value
        );

        return $this;
    }

    /**
     * Asserts that the response contains the given cookie and equals the optional value.
     *
     * @param string $cookieName Кука.
     * @param mixed  $value      Значение.
     *
     * @return $this
     */
    public function assertCookie($cookieName, $value = null): self
    {
        PHPUnit::assertNotNull(
            $cookie = $this->getCookie($cookieName),
            "Cookie [{$cookieName}] not present on response."
        );

        if (!$cookie || is_null($value)) {
            return $this;
        }

        $cookieValue = $cookie->getValue();

        $actual = $cookieValue;

        PHPUnit::assertEquals(
            $value,
            $actual,
            "Cookie [{$cookieName}] was found, but value [{$actual}] does not match [{$value}]."
        );

        return $this;
    }

    /**
     * Get the given cookie from the response.
     *
     * @param string $cookieName Кука.
     *
     * @return Cookie|null
     */
    protected function getCookie($cookieName): ?Cookie
    {
        foreach ($this->headers->getCookies() as $cookie) {
            if ($cookie->getName() === $cookieName) {
                return $cookie;
            }
        }

        return null;
    }

    /**
     * Assert that the given string is contained within the response.
     *
     * @param string $value Значение.
     *
     * @return $this
     */
    public function assertSee($value): self
    {
        PHPUnit::assertStringContainsString(
            $value,
            $this->getContent()
        );

        return $this;
    }

    /**
     * Assert that the given string is contained within the response text.
     *
     * @param string $value Значение.
     *
     * @return $this
     */
    public function assertSeeText($value): self
    {
        PHPUnit::assertStringNotContainsString(
            $value,
            strip_tags($this->getContent())
        );

        return $this;
    }

    /**
     * Assert that the given string is not contained within the response.
     *
     * @param string $value Значение.
     *
     * @return $this
     */
    public function assertDontSee($value): self
    {
        PHPUnit::assertStringNotContainsString(
            $value,
            $this->getContent()
        );

        return $this;
    }

    /**
     * Assert that the given string is not contained within the response text.
     *
     * @param string $value Значение.
     *
     * @return $this
     */
    public function assertDontSeeText($value): self
    {
        PHPUnit::assertNotContains($value, [strip_tags($this->getContent())]);

        return $this;
    }

    /**
     * Assert that the response is a superset of the given JSON.
     *
     * @param array $data Массив данных.
     *
     * @return $this
     * @throws Exception
     */
    public function assertJson(array $data): self
    {
        PHPUnit::assertArraySubset(
            $data,
            $this->decodeResponseJson(),
            false,
            $this->assertJsonMessage($data)
        );

        return $this;
    }

    /**
     * Assert that the response has the exact given JSON.
     *
     * @param array $data
     *
     * @return $this
     */
    public function assertExactJson(array $data): self
    {
        $actual = json_encode(Arr::sortRecursive(
            (array)$this->decodeResponseJson()
        ));

        PHPUnit::assertEquals(json_encode(Arr::sortRecursive($data)), $actual);

        return $this;
    }

    /**
     * Assert that the response contains the given JSON fragment.
     *
     * @param array $data
     *
     * @return $this
     */
    public function assertJsonFragment(array $data): self
    {
        $actual = json_encode(Arr::sortRecursive(
            (array)$this->decodeResponseJson()
        ));

        foreach (Arr::sortRecursive($data) as $key => $value) {
            $expected = substr(json_encode([$key => $value]), 1, -1);

            PHPUnit::assertTrue(
                Str::contains($actual, $expected),
                'Unable to find JSON fragment: '.PHP_EOL.PHP_EOL.
                "[{$expected}]".PHP_EOL.PHP_EOL.
                'within'.PHP_EOL.PHP_EOL.
                "[{$actual}]."
            );
        }

        return $this;
    }

    /**
     * Assert that the response does not contain the given JSON fragment.
     *
     * @param array $data
     *
     * @return $this
     */
    public function assertJsonMissing(array $data): self
    {
        $actual = json_encode(Arr::sortRecursive(
            (array)$this->decodeResponseJson()
        ));

        foreach (Arr::sortRecursive($data) as $key => $value) {
            $expected = substr(json_encode([$key => $value]), 1, -1);

            PHPUnit::assertFalse(
                Str::contains($actual, $expected),
                'Found unexpected JSON fragment: '.PHP_EOL.PHP_EOL.
                "[{$expected}]".PHP_EOL.PHP_EOL.
                'within'.PHP_EOL.PHP_EOL.
                "[{$actual}]."
            );
        }

        return $this;
    }

    /**
     * Assert that the response has a given JSON structure.
     *
     * @param array|null $structure
     * @param array|null $responseData
     *
     * @return $this
     * @throws Exception
     */
    public function assertJsonStructure(array $structure = null, $responseData = null): self
    {
        if (is_null($structure)) {
            return $this->assertJson($this->json());
        }

        if (is_null($responseData)) {
            $responseData = $this->decodeResponseJson();
        }

        foreach ($structure as $key => $value) {
            if (is_array($value) && $key === '*') {
                PHPUnit::assertIsArray($responseData);

                foreach ($responseData as $responseDataItem) {
                    $this->assertJsonStructure($structure['*'], $responseDataItem);
                }
            } elseif (is_array($value)) {
                PHPUnit::assertArrayHasKey($key, $responseData);

                $this->assertJsonStructure($structure[$key], $responseData[$key]);
            } else {
                PHPUnit::assertArrayHasKey($value, $responseData);
            }
        }

        return $this;
    }

    /**
     * Validate and return the decoded response JSON.
     *
     * @return array
     */
    public function decodeResponseJson() : array
    {
        $decodedResponse = json_decode($this->getContent(), true);

        if (is_null($decodedResponse) || $decodedResponse === false) {
            if ($this->exception) {
                throw $this->exception;
            }

            PHPUnit::fail('Invalid JSON was returned from the route.');
        }

        return $decodedResponse;
    }

    /**
     * Validate and return the decoded response JSON.
     *
     * @return array
     */
    public function json() : array
    {
        return $this->decodeResponseJson();
    }

    /**
     * Assert that the session has a given value.
     *
     * @param string|array $key   Ключ.
     * @param mixed        $value Значение.
     *
     * @return $this
     */
    public function assertSessionHas($key, $value = null): self
    {
        if (is_array($key)) {
            return $this->assertSessionHasAll($key);
        }

        if (is_null($value)) {
            PHPUnit::assertTrue(
                $this->session()->has($key),
                "Session is missing expected key [{$key}]."
            );
        }

        return $this;
    }

    /**
     * Assert that the session has a given list of values.
     *
     * @param array $bindings
     *
     * @return $this
     */
    public function assertSessionHasAll(array $bindings) : self
    {
        foreach ($bindings as $key => $value) {
            if (is_int($key)) {
                $this->assertSessionHas($value);
            } else {
                $this->assertSessionHas($key, $value);
            }
        }

        return $this;
    }

    /**
     * Assert that the session does not have a given key.
     *
     * @param string|array $key
     *
     * @return $this
     */
    public function assertSessionMissing($key): self
    {
        if (is_array($key)) {
            foreach ($key as $value) {
                $this->assertSessionMissing($value);
            }
        } else {
            PHPUnit::assertFalse(
                $this->session()->has($key),
                "Session has unexpected key [{$key}]."
            );
        }

        return $this;
    }

    /**
     * Dynamically access base response parameters.
     *
     * @param string $key
     *
     * @return mixed
     */
    public function __get($key)
    {
        return $this->baseResponse->{$key};
    }

    /**
     * Proxy isset() checks to the underlying base response.
     *
     * @param string $key
     * @return mixed
     */
    public function __isset($key)
    {
        return isset($this->baseResponse->{$key});
    }

    /**
     * Handle dynamic calls into macros or pass missing methods to the base response.
     *
     * @param string $method
     * @param array $args
     * @return mixed
     */
    public function __call($method, $args)
    {
        if (static::hasMacro($method)) {
            return $this->macroCall($method, $args);
        }

        return $this->baseResponse->{$method}(...$args);
    }

    /**
     * Get the assertion message for assertJson.
     *
     * @param array $data Массив данных.
     *
     * @return string
     */
    protected function assertJsonMessage(array $data): string
    {
        $expected = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        $actual = json_encode($this->decodeResponseJson(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        return 'Unable to find JSON: '.PHP_EOL.PHP_EOL.
            "[{$expected}]".PHP_EOL.PHP_EOL.
            'within response JSON:'.PHP_EOL.PHP_EOL.
            "[{$actual}].".PHP_EOL.PHP_EOL;
    }

    /**
     * Get the current session store.
     *
     * @return Session
     */
    protected function session(): Session
    {
        return container()->get('symfony.session.instance');
    }
}
