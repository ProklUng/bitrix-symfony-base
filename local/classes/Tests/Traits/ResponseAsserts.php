<?php

namespace Local\Tests\Traits;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

/**
 * Trait ResponseAsserts
 * Из Laravel.
 * @package Local\Tests\Traits
 * @see https://github.com/laravel/framework/blob/6.x/src/Illuminate/Foundation/Testing/TestResponse.php#L549
 *
 * @since 17.09.2020
 */
trait ResponseAsserts
{
    /**
     * Assert that the response contains the given JSON fragment.
     *
     * @param string $content
     * @param array $data
     *
     * @return $this
     */
    public function assertJsonFragment(string $content, array $data) : self
    {
        $actual = json_encode(Arr::sortRecursive(
            (array)$this->decodeResponseJson($content)));

        foreach (Arr::sortRecursive($data) as $key => $value) {
            $expected = $this->jsonSearchStrings($key, $value);

            $this->assertTrue(
                Str::contains($actual, $expected),
                'Unable to find JSON fragment: '.PHP_EOL.PHP_EOL.
                '['.json_encode([$key => $value]).']'.PHP_EOL.PHP_EOL.
                'within'.PHP_EOL.PHP_EOL.
                "[{$actual}]."
            );
        }

        return $this;
    }

    /**
     * Assert that the response is a superset of the given JSON.
     *
     * @param mixed $content
     * @param array $data
     * @param bool $strict
     *
     * @return $this
     */
    public function assertJson($content, array $data, $strict = false): self
    {
        $this->assertArraySubset(
            $data, $this->decodeResponseJson($content), $strict, $this->assertJsonMessage($content, $data)
        );

        return $this;
    }

    /**
     * Assert that the response has the exact given JSON.
     *
     * @param mixed $content
     * @param array $data
     *
     * @return $this
     * @throws JsonException
     */
    public function assertExactJson($content, array $data) : self
    {
        $actual = json_encode(Arr::sortRecursive(
            (array) $this->decodeResponseJson($content)
        ));

        $this->assertEquals(
            json_encode(Arr::sortRecursive($data)),
            $actual
        );

        return $this;
    }

    /**
     * Assert that the response does not contain the given JSON fragment.
     *
     * @param mixed $content
     * @param array $data
     * @param bool $exact
     *
     * @return $this
     */
    public function assertJsonMissing($content, array $data, $exact = false): self
    {
        if ($exact) {
            return $this->assertJsonMissingExact($content, $data);
        }

        $actual = json_encode(Arr::sortRecursive(
            (array)$this->decodeResponseJson($content)
        ));

        foreach (Arr::sortRecursive($data) as $key => $value) {
            $unexpected = $this->jsonSearchStrings($key, $value);

            $this->assertFalse(
                Str::contains($actual, $unexpected),
                'Found unexpected JSON fragment: '.PHP_EOL.PHP_EOL.
                '['.json_encode([$key => $value]).']'.PHP_EOL.PHP_EOL.
                'within'.PHP_EOL.PHP_EOL.
                "[{$actual}]."
            );
        }

        return $this;
    }


    /**
     * Assert that the response does not contain the exact JSON fragment.
     *
     * @param mixed $content
     * @param array $data
     *
     * @return $this
     */
    public function assertJsonMissingExact($content, array $data) : self
    {
        $actual = json_encode(Arr::sortRecursive(
            (array)$this->decodeResponseJson($content)
        ));

        foreach (Arr::sortRecursive($data) as $key => $value) {
            $unexpected = $this->jsonSearchStrings($key, $value);

            if (! Str::contains($actual, $unexpected)) {
                return $this;
            }
        }

        $this->assertTrue(
            false,
            'Found unexpected JSON fragment: '.PHP_EOL.PHP_EOL.
            '['.json_encode($data).']'.PHP_EOL.PHP_EOL.
            'within'.PHP_EOL.PHP_EOL.
            "[{$actual}]."
        );

        return $this;
    }

    /**
     * Assert that the given string is contained within the response text.
     *
     * @param $content
     * @param string $value
     *
     * @return $this
     */
    public function assertSeeText($content, $value): self
    {
        $this->assertStringContainsString(
            (string) $value,
            strip_tags($content)
        );

        return $this;
    }

    /**
     * Assert that the given string is contained within the response text.
     *
     * @param $content
     * @param string $value
     *
     * @return $this
     */
    public function assertDontSee($content, $value): self
    {
        $this->assertStringNotContainsString(
            (string) $value, strip_tags($content)
        );

        return $this;
    }

    /**
     * Validate and return the decoded response JSON.
     *
     * @param mixed $content
     * @param string|null $key
     *
     * @return mixed
     */
    public function decodeResponseJson($content, $key = null)
    {
        $decodedResponse = json_decode($content, true, 512);

        if ($decodedResponse === false || is_null($decodedResponse)) {
            return null;
        }

        return data_get($decodedResponse, $key);
    }

    /**
     * Get the strings we need to search for when examining the JSON.
     *
     * @param  string  $key
     * @param  string  $value
     * @return array
     */
    protected function jsonSearchStrings($key, $value): array
    {
        $needle = substr(json_encode([$key => $value]), 1, -1);

        return [
            $needle.']',
            $needle.'}',
            $needle.',',
        ];
    }

    /**
     * Get the assertion message for assertJson.
     *
     * @param mixed $content
     * @param array $data
     *
     * @return string
     * @throws JsonException
     */
    protected function assertJsonMessage($content, array $data): string
    {
        $expected = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        $actual = json_encode($this->decodeResponseJson($content), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        return 'Unable to find JSON: '.PHP_EOL.PHP_EOL.
            "[{$expected}]".PHP_EOL.PHP_EOL.
            'within response JSON:'.PHP_EOL.PHP_EOL.
            "[{$actual}].".PHP_EOL.PHP_EOL;
    }
}
