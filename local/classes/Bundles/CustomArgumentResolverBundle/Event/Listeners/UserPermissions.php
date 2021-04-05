<?php

namespace Local\Bundles\CustomArgumentResolverBundle\Event\Listeners;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\DB\SqlExpression;
use Bitrix\Main\GroupTable;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Bitrix\Main\UserTable;
use CUser;
use Local\Bundles\CustomArgumentResolverBundle\Event\Exceptions\AnonymousDenyAccessException;
use Local\Bundles\CustomArgumentResolverBundle\Event\Exceptions\UserDenyAccessException;
use Local\Bundles\CustomArgumentResolverBundle\Event\Interfaces\OnControllerRequestHandlerInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;

/**
 * Class UserPermissions
 * @package Local\Bundles\CustomArgumentResolverBundle\Event\Listeners
 *
 * @since 18.02.2021
 */
class UserPermissions implements OnControllerRequestHandlerInterface
{
    /**
     * @const string ROUTE_PARAM_NAME Параметр роута, содержащий список групп пользователей,
     * которым разрешен доступ.
     */
    private const ROUTE_PARAM_NAME = 'is_granted';

    /**
     * @var UserTable $userManager Работа с пользователями D7.
     */
    private $userManager;

    /**
     * @var CUser $user Битриксовый CUser.
     */
    private $user;

    /**
     * UserPermissions constructor.
     *
     * @param CUser     $user        Битриксовый CUser.
     * @param UserTable $userManager Работа с пользователями D7.
     */
    public function __construct(
        CUser $user,
        UserTable $userManager
    ) {
        $this->userManager = $userManager;
        $this->user = $user;
    }

    /**
     * Обработчик события kernel.controller.
     *
     * Проверка прав на роут.
     *
     * @param ControllerEvent $event Объект события.
     *
     * @return void
     *
     * @throws AnonymousDenyAccessException Анонимным пользователям вход воспрещен.
     * @throws UserDenyAccessException      В доступе отказано по правам.
     * @throws ArgumentException | ObjectPropertyException | SystemException
     */
    public function handle(ControllerEvent $event): void
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        $request = $event->getRequest();
        $isGranted = $request->get(self::ROUTE_PARAM_NAME);
        $userId = $this->user->getId();

        // Админам можно всё.
        if (!$isGranted || $this->user->IsAdmin()) {
            return;
        }

        if (!$userId) {
            throw new AnonymousDenyAccessException(
                'Access non-authorized users denied.'
            );
        }

        $grantedGroupUsers = (array)$isGranted;
        $userGroupsCode = $this->getUserGroupCodes($userId);

        if (array_intersect($grantedGroupUsers, $userGroupsCode)) {
            return;
        }

        throw new UserDenyAccessException(
            'Access denied.'
        );
    }

    /**
     * Коды групп пользователя по ID.
     *
     * @param integer $userId ID пользователя.
     *
     * @return array
     *
     * @throws ArgumentException | ObjectPropertyException | SystemException
     */
    private function getUserGroupCodes(int $userId) : array
    {
        $groups = [];

        if($userId > 0) {
            $nowTimeExpression = new SqlExpression(
                $this->userManager::getEntity()->getConnection()->getSqlHelper()->getCurrentDateTimeFunction()
            );

            $result = GroupTable::getList([
                'select' => ['STRING_ID'],
                'filter' => [
                    '=UserGroup:GROUP.USER_ID' => $userId,
                    '=ACTIVE' => 'Y',
                    [
                        'LOGIC' => 'OR',
                        '=UserGroup:GROUP.DATE_ACTIVE_FROM' => null,
                        '<=UserGroup:GROUP.DATE_ACTIVE_FROM' => $nowTimeExpression,
                    ],
                    [
                        'LOGIC' => 'OR',
                        '=UserGroup:GROUP.DATE_ACTIVE_TO' => null,
                        '>=UserGroup:GROUP.DATE_ACTIVE_TO' => $nowTimeExpression,
                    ],
                    [
                        'LOGIC' => 'OR',
                        '!=ANONYMOUS' => 'Y',
                        '=ANONYMOUS' => null
                    ]
                ]
            ]);

            while ($row = $result->fetch())
            {
                $groups[] = $row['STRING_ID'];
            }
        }

        $groups = array_filter($groups);
        $groups = array_unique($groups);
        sort($groups);

        return $groups;
    }
}
