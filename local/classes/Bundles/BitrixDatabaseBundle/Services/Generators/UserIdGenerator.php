<?php

namespace Local\Bundles\BitrixDatabaseBundle\Services\Generators;

use CUser;
use Exception;
use Local\Bundles\BitrixDatabaseBundle\Services\Contracts\FixtureGeneratorInterface;

/**
 * Class UserIdGenerator
 * @package Local\Bundles\BitrixDatabaseBundle\Services\Generators
 *
 * @since 08.04.2021
 */
class UserIdGenerator implements FixtureGeneratorInterface
{
    /**
     * @inheritDoc
     * @throws Exception
     */
    public function generate(?array $payload = null)
    {
        $result = [];

        $by = 'id';
        $order = 'asc';
        $dbResultList = CUser::GetList($by, $order, []);
        while ($arResult = $dbResultList->Fetch()) {
            $result[] = $arResult['ID'];
        }

        return $result[random_int(0, count($result) - 1)];
    }
}
