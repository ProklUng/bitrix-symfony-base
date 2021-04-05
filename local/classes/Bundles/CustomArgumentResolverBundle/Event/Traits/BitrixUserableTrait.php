<?php

namespace Local\Bundles\CustomArgumentResolverBundle\Event\Traits;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\GroupTable;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Bitrix\Main\UserTable;
use CUser;

/**
 * Trait BitrixUserableTrait
 * @package Local\Bundles\CustomArgumentResolverBundle\Event\Traits
 *
 * @since 19.02.2021
 */
trait BitrixUserableTrait
{
    /**
     * @var CUser $currentUser
     */
    private $currentUser;

    /**
     * @var array $userData Данные на текущего пользователя.
     */
    private $userData = [];

    /**
     * Инициализация сведений о пользователе.
     *
     * @return void
     * @throws ArgumentException | ObjectPropertyException | SystemException
     */
    public function initializeBitrixUserableTrait(): void
    {
        $this->currentUser = new CUser();
        $this->userData = [
            'id' => 0,
            'groups' => [],
            'isAdmin' => false,
        ];

        $userId = (int)$this->currentUser->getId();
        $this->userData['isAuthorized'] = $this->currentUser->IsAuthorized();

        if ($userId) {
            $this->userData['isAdmin'] = $this->currentUser->IsAdmin();
            $this->userData['id'] = $userId;
            $this->userData['groups'] = $this->getUserGroupData($userId);
            $this->userData['info'] = $this->getUserInfo($userId);
        }
    }

    /**
     * Данные о группач пользователя по ID.
     *
     * @param integer $userId ID пользователя.
     *
     * @return array
     *
     * @throws ArgumentException | ObjectPropertyException | SystemException
     */
    private function getUserGroupData(int $userId): array
    {
        $groups = [];

        $result = GroupTable::getList(
            [
                'select' => ['ID', 'STRING_ID', 'NAME'],
                'filter' => ['=ACTIVE' => 'Y', '=UserGroup:GROUP.USER_ID' => $userId],
            ]
        );
        while ($row = $result->fetch()) {
            $groups[$row['STRING_ID']] = (int)$row['ID'];
        }

        return $groups;
    }

    /**
     * Сведения о юзере по ID.
     *
     * @param integer $userId ID пользователя.
     *
     * @return array
     *
     * @throws ArgumentException | ObjectPropertyException | SystemException
     */
    private function getUserInfo(int $userId): array
    {
        $userData = [];

        $result = UserTable::getList([
            'filter' => ['ID' => $userId],
            'select' => [
                'ID',
                'LOGIN',
                'NAME',
                'LAST_NAME',
                'SECOND_NAME',
                'EMAIL',
            ],
            'limit' => 1,
        ]);


        if ($arUser = $result->fetch()) {
            $userData = $arUser;
        }

        return $userData;
    }
}
