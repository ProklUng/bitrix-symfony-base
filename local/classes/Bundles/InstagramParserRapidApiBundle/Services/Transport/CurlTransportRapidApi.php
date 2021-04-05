<?php

namespace Local\Bundles\InstagramParserRapidApiBundle\Services\Transport;

use Local\Bundles\InstagramParserRapidApiBundle\Services\Exceptions\InstagramTransportException;

/**
 * Class CurlTransportRapidApi
 * @package Local\Bundles\InstagramParserRapidApiBundle\Services\Transport
 *
 * @since 23.02.2021
 */
class CurlTransportRapidApi implements InstagramTransportInterface
{
    /**
     * @var string $rapidApiKey Ключ к RapidAPI.
     */
    private $rapidApiKey;

    /**
     * @const string RAPID_API_URL URL RapidAPI.
     */
    private const RAPID_API_URL = 'instagram40.p.rapidapi.com';

    /**
     * CurlTransportRapidApi constructor.
     *
     * @param string $rapidApiKey Ключ к RapidAPI.
     */
    public function __construct(string $rapidApiKey)
    {
        $this->rapidApiKey = $rapidApiKey;
    }

    /**
     * @inheritDoc
     */
    public function get(string $query) : string
    {
        $curl = curl_init();

        curl_setopt_array(
            $curl,
            [
                CURLOPT_URL => 'https://' . self::RAPID_API_URL . $query,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_HTTPHEADER => [
                    'x-rapidapi-host: '.self::RAPID_API_URL,
                    'x-rapidapi-key: '.$this->rapidApiKey,
                ],
            ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            throw new InstagramTransportException('Get Request Error: ' . $err, 400);
        }

        return (string)$response;
    }
}
