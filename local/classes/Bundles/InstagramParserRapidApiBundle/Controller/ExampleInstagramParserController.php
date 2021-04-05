<?php

namespace Local\Bundles\InstagramParserRapidApiBundle\Controller;

use Exception;
use Local\Bundles\InstagramParserRapidApiBundle\Services\ComplexParser;
use Local\Bundles\InstagramParserRapidApiBundle\Services\UserInfoRetriever;
use Psr\Cache\InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Class ExampleInstagramParserController
 * @package Local\Bundles\InstagramParserRapidApiBundle\Controller
 *
 * @since 22.02.2021
 */
class ExampleInstagramParserController extends AbstractController
{
    /**
     * @var ComplexParser $parser Парсер.
     */
    private $parser;

    /**
     * @var SerializerInterface $serializer Сериалайзер.
     */
    private $serializer;

    /**
     * ExampleInstagramParserController constructor.
     *
     * @param ComplexParser       $parser     Парсер.
     * @param SerializerInterface $serializer Сериалайзер.
     */
    public function __construct(ComplexParser $parser, SerializerInterface $serializer)
    {
        $this->parser = $parser;
        $this->serializer = $serializer;
    }

    /**
     * @param Request $request Request.
     * @param integer $count   Количество картинок.
     *
     * @return Response
     * @throws Exception
     */
    public function action(
        Request $request,
        int $count = 3
    ): Response {
        $this->parser->setQueryCount(12);
        $this->parser->setCount($count);

        $result = $this->parser->parse();
//        $nextPageMarker = $this->parser->getCurrentAfterParam();
//        if ($nextPageMarker) {
//            $this->parser->setAfterParam($nextPageMarker);
//            $result = $this->parser->parse();
//        }

        return new Response(
            $this->serializer->serialize($result, 'json'),
            Response::HTTP_OK,
            ['Content-Type', 'application/json; charset=utf-8']
        );
    }

    /**
     * @param Request           $request         Request.
     * @param string            $username        Пользователь.
     * @param UserInfoRetriever $userIdRetriever Извлекатель ID.
     *
     * @return Response
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function userId(
        Request $request,
        string $username,
        UserInfoRetriever $userIdRetriever
    ): Response {

        $userIdRetriever->setUserName($username);

        $result = [
            'id' => $userIdRetriever->getUserId()
        ];

        return new Response(
            $this->serializer->serialize($result, 'json'),
            Response::HTTP_OK,
            ['Content-Type', 'application/json; charset=utf-8']
        );
    }

    /**
     * @param Request           $request         Request.
     * @param string            $username        Пользователь.
     * @param UserInfoRetriever $userIdRetriever Извлекатель ID.
     *
     * @return Response
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function byUserName(
        Request $request,
        string $username,
        UserInfoRetriever $userIdRetriever
    ): Response {

        $userIdRetriever->setUserName($username);
        $idUser = $userIdRetriever->getUserId();
        $this->parser->setIdUser($idUser);

        $result = $this->parser->parse();

        return new Response(
            $this->serializer->serialize($result, 'json'),
            Response::HTTP_OK,
            ['Content-Type', 'application/json; charset=utf-8']
        );
    }
}
