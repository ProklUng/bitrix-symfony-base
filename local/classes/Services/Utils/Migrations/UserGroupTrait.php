<?php

namespace Local\Services\Utils\Migrations;

use CGroup;
use Exception;

/**
 * Trait UserGroupTrait
 * @package Local\Services\Utils\Migrations
 *
 * @since 11.04.2021
 */
trait UserGroupTrait
{
    /**
     * @var array
     *
     * @return array
     *
     * @throws Exception
     */
    protected function userGroupCreate(array $data): array
    {
        $return = [];
        if (empty($data['STRING_ID'])) {
            throw new Exception('You must set group STRING_ID');
        }
        if ($this->userGetGroupIdByCode($data['STRING_ID'])) {
            throw new Exception('Group with STRING_ID ' . $data['STRING_ID'] . ' already exists');
        }
        $ib = new CGroup();
        $id = $ib->Add(array_merge(['ACTIVE' => 'Y'], $data));
        if ($id) {
            $return[] = "Add {$data['STRING_ID']} users group";
        } else {
            throw new Exception("Can't create {$data['STRING_ID']} users group");
        }

        return $return;
    }

    /**
     * @var string
     *
     * @return array
     *
     * @throws Exception
     */
    protected function userGroupDelete($groupName): array
    {
        $return = [];
        $id = $this->UserGetGroupIdByCode($groupName);
        if ($id) {
            $group = new CGroup();
            if ($group->Delete($id)) {
                $return[] = "Delete group {$groupName}";
            } else {
                throw new Exception("Can't delete group {$groupName}");
            }
        } else {
            throw new Exception("Group {$groupName} does not exist");
        }

        return $return;
    }

    /**
     * @var string
     *
     * @return int|null
     *
     * @throws Exception
     */
    protected function UserGetGroupIdByCode($code): ?int
    {
        $rsGroups = CGroup::GetList(($by = 'c_sort'), ($order = 'desc'), [
            'STRING_ID' => $code,
        ]);
        if ($ob = $rsGroups->Fetch()) {
            return $ob['ID'];
        }

       return null;
    }
}
