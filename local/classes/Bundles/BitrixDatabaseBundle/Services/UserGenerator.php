<?php

namespace Local\Bundles\BitrixDatabaseBundle\Services;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\GroupTable;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use CGroup;
use CUser;
use Exception;
use Faker\Factory;
use Faker\Generator;
use RuntimeException;

/**
 * Class UserGenerator
 * @package Local\Bundles\BitrixDatabaseBundle\Services
 *
 * @since 12.04.2021
 */
class UserGenerator
{
    /**
     * @var Generator $faker Фэйкер.
     */
    private $faker;

    /**
     * @var CUser $cuser Битриксовый CUser.
     */
    private $cuser;

    /**
     * @var CGroup $cGroup Битриксовый CGroup.
     */
    private $cGroup;

    /**
     * UserGenerator constructor.
     *
     * @param CUser  $cuser  Битриксовый CUser.
     * @param CGroup $cGroup Битриксовый CGroup.
     */
    public function __construct(
        CUser $cuser,
        CGroup $cGroup
    ) {
        $this->faker = Factory::create('ru_Ru');
        $this->cuser = $cuser;
        $this->cGroup = $cGroup;
    }

    /**
     * Создать случайного пользователя.
     *
     * @param boolean $phoneable Номер телефона в качестве логина.
     *
     * @return integer
     * @throws ArgumentException | ObjectPropertyException | SystemException
     * @throws RuntimeException | Exception
     */
    public function createUser(bool $phoneable = false) : int
    {
        $password = $this->faker->password();
        $allUserGroups = $this->getAllUserGroupData();

        $login = $this->faker->slug(1);
        // Телефонный номер как логин.
        if ($phoneable) {
            $login = $this->faker->phoneNumber;
            $login = str_replace(['(', ')', '-', ' '], '', $login);
            if (strpos($login, '+7') === false) {
                $login = '+7' . $login;
            }
        }

        // Исключить группу 1 - админов.
        $allUserGroups = array_filter(
            $allUserGroups,
            /** @param integer $item
             *  @return integer
             */
            static function (int $item): int {
                return $item !== 1;
        });

        $randomKey = random_int(0, count($allUserGroups) - 1);

        $arFields = [
            'NAME' => $this->faker->firstName,
            'LAST_NAME' => $this->faker->lastName,
            'EMAIL' => $this->faker->email,
            'LOGIN' => $login,
            'LID' => 'ru',
            'ACTIVE' => 'Y',
            'GROUP_ID' => [$allUserGroups[$randomKey]],
            'PASSWORD' => $password,
            'CONFIRM_PASSWORD' => $password,
        ];

        $id = $this->cuser->Add($arFields);
        if ((int)($id) > 0) {
            return (int)($id);
        }

        throw new RuntimeException($this->cuser->LAST_ERROR);
    }

    /**
     * Создать группу пользователей.
     *
     * @return void
     * @throws RuntimeException
     */
    public function createUserGroup() : void
    {
        $idGroup = $this->faker->slug;

        if ($this->userGetGroupIdByCode($idGroup)) {
            throw new RuntimeException('Group with STRING_ID ' . $idGroup . ' already exists');
        }

        $id = $this->cGroup->Add(array_merge(['ACTIVE' => 'Y'], ['STRING_ID' => $idGroup]));
        if ($id) {
            return;
        }

        throw new RuntimeException("Can't create {$idGroup} users group");
    }

    /**
     * Удалить всех пользователей, кроме admin.
     *
     * @return void
     */
    public function deleteAllUsers() : void
    {
        $by = 'id';
        $order = 'asc';
        $dbResultList = $this->cuser::GetList($by, $order, []);

        while ($arResult = $dbResultList->Fetch()) {
            if ($arResult['LOGIN'] === 'admin') {
                continue;
            }

            $this->cuser::Delete($arResult['ID']);
        }
    }

    /**
     * Данные о группах пользователя по ID.
     *
     * @return array
     *
     * @throws ArgumentException | ObjectPropertyException | SystemException
     */
    private function getAllUserGroupData(): array
    {
        $groups = [];

        $result = GroupTable::getList(
            [
                'select' => ['ID', 'STRING_ID', 'NAME'],
                'filter' => ['=ACTIVE' => 'Y'],
            ]
        );
        while ($row = $result->fetch()) {
            $groups[] = (int)$row['ID'];
        }

        return $groups;
    }

    /**
     * @var string $code
     *
     * @return integer
     *
     * @throws RuntimeException
     */
    private function userGetGroupIdByCode(string $code): int
    {
        $by = 'c_sort';
        $order = 'desc';

        $rsGroups = $this->cGroup::GetList($by, $order, [
            'STRING_ID' => $code,
        ]);

        if ($ob = $rsGroups->Fetch()) {
            return $ob['ID'];
        }

        throw new RuntimeException('Группа пользователей с ID ' . $code . ' не найдена.');
    }
}
