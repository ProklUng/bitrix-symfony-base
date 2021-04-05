<?php

namespace Local\Bundles\ApiDtoConvertorBundle\Examples;

use Local\Services\Sanitizing\SanitizableTrait;
use Local\Services\Validation\Controllers\ValidateableTrait;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class ExampleApiController
 * @package Local\Bundles\ApiDtoConvertorBundle\Examples
 *
 * @since 04.11.2020
 */
class ExampleApiController
{
    use ValidateableTrait;
    use SanitizableTrait;

    public function action(ExampleDTOPost $dto)
    {

        return [$dto, $dto];
    }

    public function action2(ExampleDTOPost $DTOPost)
    {
        $DTOPost->amount += 1150;

        return $DTOPost;
    }

    public function action3(Request $request, ExampleDTOPostSpatie $DTOPost)
    {
        // var_dump($DTOPost);
        $DTOPost->amount += 150;

        return $DTOPost;
    }

    public function action4(ExampleDTOPostSpatie $DTOPost)
    {
        // var_dump($DTOPost);
        $DTOPost->amount += 150;

        return [$DTOPost, $DTOPost, $DTOPost];
    }
}
