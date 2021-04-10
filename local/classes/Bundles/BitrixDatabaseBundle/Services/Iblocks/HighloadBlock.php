<?php

namespace Local\Bundles\BitrixDatabaseBundle\Services\Iblocks;

use Bitrix\Highloadblock\HighloadBlockLangTable;
use Bitrix\Highloadblock\HighloadBlockRightsTable;
use Bitrix\Highloadblock\HighloadBlockTable;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Entity\DataManager;
use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Bitrix\Main\UserFieldTable;
use CDBResult;
use CTask;
use CUserFieldEnum;
use CUserTypeEntity;
use Exception;
use RuntimeException;

/**
 * Class HighloadBlock
 * @package Local\Bundles\BitrixDatabaseBundle\Services\Iblocks
 *
 * @since 10.04.2021
 */
class HighloadBlock
{
    /**
     * @return boolean
     */
    public function isEnabled() : bool
    {
        return $this->checkModules(['highloadblock']);
    }

    /**
     * Получает список highload-блоков.
     *
     * @param array $filter Фильтр.
     *
     * @return array
     * @throws ArgumentException | ObjectPropertyException | SystemException
     */
    public function getHlblocks(array $filter = []) : array
    {
        $result = [];
        $dbres = HighloadBlockTable::getList(
            [
                'select' => ['*'],
                'filter' => $filter,
            ]
        );
        while ($hlblock = $dbres->fetch()) {
            $result[] = $this->prepareHlblock($hlblock);
        }

        return $result;
    }

    /**
     * Добавить элемент.
     *
     * @param string $hlblockName Название HL блока.
     * @param array  $fields      Поля.
     *
     * @return integer
     * @throws ArgumentException | ObjectPropertyException | SystemException | Exception
     */
    public function addElement(string $hlblockName, array $fields) : int
    {
        $dataManager = $this->getDataManager($hlblockName);

        $result = $dataManager::add($fields);

        if ($result->isSuccess()) {
            return $result->getId();
        }

        throw new RuntimeException($result->getErrors());
    }

    /**
     * Удалить все элементы HL-блока.
     *
     * @param string $hlblockName HL-iblock.
     *
     * @return void
     * @throws ArgumentException | ObjectPropertyException | SystemException
     */
    public function deleteElements(string $hlblockName) : void
    {
        $dataManager = $this->getDataManager($hlblockName);
        $elements = $dataManager::getList();

        $arElements = $elements->fetchAll();
        foreach ($arElements as $element) {
            $dataManager::delete($element['ID']);
        }
    }

    /**
     * @param string $hlblockName Название HL блока.
     *
     * @return DataManager
     *
     * @throws ArgumentException | ObjectPropertyException | SystemException
     */
    public function getDataManager(string $hlblockName)
    {
        $hlblock = $this->getHlblock($hlblockName);
        $entity = HighloadBlockTable::compileEntity($hlblock);
        return $entity->getDataClass();
    }

    /**
     * Свойства поля HL блока.
     *
     * @param string $hlblockName Название HL блока.
     * @param string $property    Свойство.
     *
     * @return array
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public function getPropertyData(string $hlblockName, string $property) : array
    {
        $dbUserFields = UserFieldTable::getList(
            [
            'filter' => ['ENTITY_ID' => $this->getEntityId($hlblockName)],
            ]
        );

        $arResult = [];

        while ($arUserField = $dbUserFields->fetch()) {
            if ($arUserField["USER_TYPE_ID"] === 'enumeration') {
                $fieldEnum = new \CUserFieldEnum();
                $dbEnums = $fieldEnum->GetList(
                    array(),
                    array('USER_FIELD_ID' => $arUserField['ID'])
                );
                while ($arEnum = $dbEnums->GetNext()) {
                    $arUserField['ENUMS'][$arEnum['XML_ID']] = $arEnum;
                }
            }

            $arResult['USER_FIELDS'][$arUserField["FIELD_NAME"]] = $arUserField;
        }

        return $arResult['USER_FIELDS'][$property] ?? [];
    }

    /**
     * Все свойства HL-блока.
     *
     * @param string $hlblockName Название HL блока.
     *
     * @return array
     *
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public function getAllProperties(string $hlblockName) : array
    {
        $dbUserFields = UserFieldTable::getList(
            [
                'filter' => ['ENTITY_ID' => $this->getEntityId($hlblockName)],
            ]
        );

        $arResult = [];

        while ($arUserField = $dbUserFields->fetch()) {
            if ($arUserField["USER_TYPE_ID"] === 'enumeration') {
                $fieldEnum = new \CUserFieldEnum();
                $dbEnums = $fieldEnum->GetList(
                    array(),
                    array('USER_FIELD_ID' => $arUserField['ID'])
                );
                while ($arEnum = $dbEnums->GetNext()) {
                    $arUserField['ENUMS'][$arEnum['XML_ID']] = $arEnum;
                }
            }

            $arResult[$arUserField["FIELD_NAME"]] = $arUserField;
        }

        return $arResult;
    }

    /**
     * Получает пользовательские поля у объекта
     *
     * @param mixed $entityId
     *
     * @return array
     */
    public function getUserTypeEntities($entityId = false)
    {
        if (!empty($entityId)) {
            $filter = is_array($entityId)
                ? $entityId
                : [
                    'ENTITY_ID' => $entityId,
                ];
        } else {
            $filter = [];
        }

        $dbres = CUserTypeEntity::GetList([], $filter);
        $result = [];
        while ($item = $dbres->Fetch()) {
            $result[] = $this->getUserTypeEntityById($item['ID']);
        }
        return $result;
    }

    /**
     * Получает пользовательское поле у объекта.
     *
     * @param mixed $fieldId
     *
     * @return array|bool
     */
    public function getUserTypeEntityById($fieldId)
    {
        $item = CUserTypeEntity::GetByID($fieldId);
        if (empty($item)) {
            return false;
        }

        if ($item['USER_TYPE_ID'] == 'enumeration') {
            $item['ENUM_VALUES'] = $this->getEnumValues($fieldId);
        }

        return $item;
    }

    /**
     * @param mixed $hlblockName
     *
     * @return string
     * @throws ArgumentException | ObjectPropertyException | SystemException
     */
    public function getEntityId($hlblockName)
    {
        $hlblockId = is_numeric($hlblockName) ? $hlblockName : $this->getHlblockId($hlblockName);
        return 'HLBLOCK_' . $hlblockId;
    }

    /**
     * Получает id highload-блока.
     *
     * @param mixed $hlblockName Id, имя или фильтр.
     *
     * @return integer|mixed
     * @throws ArgumentException | ObjectPropertyException | SystemException
     */
    public function getHlblockId($hlblockName)
    {
        $item = $this->getHlblock($hlblockName);
        return ($item && isset($item['ID'])) ? $item['ID'] : 0;
    }

    /**
     * Получает highload-блок.
     *
     * @param mixed $hlblockName Id, имя или фильтр.
     *
     * @return array
     * @throws ArgumentException | ObjectPropertyException | SystemException
     */
    public function getHlblock($hlblockName)
    {
        if (is_array($hlblockName)) {
            $filter = $hlblockName;
        } elseif (is_numeric($hlblockName)) {
            $filter = ['ID' => $hlblockName];
        } else {
            $filter = ['NAME' => $hlblockName];
        }

        $hlblock = HighloadBlockTable::getList(
            [
                'select' => ['*'],
                'filter' => $filter,
            ]
        )->fetch();

        return $this->prepareHlblock($hlblock);
    }

    /**
     * @param array $item
     *
     * @return array
     */
    private function prepareHlblock(array $item) : array
    {
        if (empty($item['ID'])) {
            return $item;
        }

        $langs = $this->getHblockLangs($item['ID']);
        if (!empty($langs)) {
            $item['LANG'] = $langs;
        }

        return $item;
    }


    /**
     * @param string $hlblockId
     *
     * @return array
     */
    protected function getGroupRights(string $hlblockId) : array
    {
        $result = [];
        if (!class_exists('\Bitrix\Highloadblock\HighloadBlockRightsTable')) {
            return $result;
        }

        try {
            $items = HighloadBlockRightsTable::getList(
                [
                    'filter' => [
                        'HL_ID' => $hlblockId,
                    ],
                ]
            )->fetchAll();

        } catch (Exception $e) {
            $items = [];
        }

        foreach ($items as $item) {
            if (strpos($item['ACCESS_CODE'], 'G') !== 0) {
                continue;
            }

            $groupId = (int)substr($item['ACCESS_CODE'], 1);
            if (empty($groupId)) {
                continue;
            }

            $letter = CTask::GetLetter($item['TASK_ID']);
            if (empty($letter)) {
                continue;
            }

            $item['LETTER'] = $letter;
            $item['GROUP_ID'] = $groupId;

            $result[] = $item;
        }

        return $result;
    }

    /**
     * @param string $hlblockId
     *
     * @return array
     */
    private function getHblockLangs(string $hlblockId) : array
    {
        $result = [];

        if (!class_exists('\Bitrix\Highloadblock\HighloadBlockLangTable')) {
            return $result;
        }

        try {
            $dbres = HighloadBlockLangTable::getList([
                'filter' => ['ID' => $hlblockId],
            ]);

            while ($item = $dbres->fetch()) {
                $result[$item['LID']] = [
                    'NAME' => $item['NAME'],
                ];
            }
        } catch (Exception $e) {

        }

        return $result;
    }

    /**
     * @param array $names Модули.
     *
     * @return boolean
     */
    private function checkModules(array $names = []) : bool
    {
        $names = is_array($names) ? $names : [$names];
        foreach ($names as $name) {
            try {
                if (!Loader::includeModule($name)) {
                    return false;
                }
            } catch (LoaderException $e) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param mixed $fieldId
     *
     * @return array
     */
    private function getEnumValues($fieldId) : array
    {
        $obEnum = new CUserFieldEnum;
        $dbres = $obEnum->GetList([], ["USER_FIELD_ID" => $fieldId]);

        return $this->fetchAll($dbres);
    }

    /**
     * @param CDBResult $dbres
     * @param mixed     $indexKey
     * @param mixed     $valueKey
     *
     * @return array
     */
    private function fetchAll(CDBResult $dbres, $indexKey = false, $valueKey = false) : array
    {
        $result = [];

        while ($item = $dbres->Fetch()) {
            if ($valueKey) {
                $value = $item[$valueKey];
            } else {
                $value = $item;
            }

            if ($indexKey) {
                $indexVal = $item[$indexKey];
                $result[$indexVal] = $value;
            } else {
                $result[] = $value;
            }
        }

        return $result;
    }
}