<?php

namespace Local\Bundles\UrlSignedBundle\Controller;

use Local\Bundles\UrlSignedBundle\UrlSigner\UrlSignerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ExampleSignedController
 * @package Local\Bundles\UrlSignedBundle\Controller
 */
class ExampleSignedController extends AbstractController
{
    /**
     * @var UrlSignerInterface
     */
    private $urlSigner;

    /**
     * ExampleSignedController constructor.
     *
     * @param UrlSignerInterface $urlSigner
     */
    public function __construct(UrlSignerInterface $urlSigner) {
        $this->urlSigner = $urlSigner;
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function action(Request $request) : Response
    {
        return new Response(
            $this->generateSignedUrl()
        );
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function signed(Request $request) : Response
    {
          return new Response(
            'ÓK'
        );
    }

    /**
     * @return string
     */
    private function generateSignedUrl(): string
    {
        $url = $this->generateUrl('signer_client', ['id' => 42]);
        // Will expire after one hour.
        $expiration = (new \DateTime('now'))->add(new \DateInterval('PT1H'));
        // An integer can also be used for the expiration: it will correspond to a number of days. For 3 days:
        // $expiration = 3;

        // Not passing the second argument will use the default expiration time
        // (1 day by default).
        // return $this->urlSigner->sign($url);

        // Will return a URL (more precisely a path) like this: /documents/42?expires=1611316656&signature=82f6958bd5c96fda58b7a55ade7f651fadb51e12171d58ed271e744bcc7c85c3
        return $this->urlSigner->sign($url, $expiration);
    }
}
