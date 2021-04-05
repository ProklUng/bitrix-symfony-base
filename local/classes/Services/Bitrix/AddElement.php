<?php

namespace Local\Services\Bitrix;

use Arrilot\BitrixModels\Exceptions\ExceptionFromBitrix;
use Arrilot\BitrixModels\Models\ElementModel;
use Local\Services\Bitrix\Exceptions\IblockModelDuplicateException;
use Local\Services\Bitrix\Exceptions\IblockModelException;
use Local\Services\Bitrix\Interfaces\TranslitInterface;

/**
 * Class AddElement
 * @package Local\Services\Bitrix
 *
 * @since 08.09.2020
 */
class AddElement
{
    /**
     * @var ElementModel $model Модель инфоблока.
     */
    private $model;

    /**
     * @var TranslitInterface $translit Транслиттер.
     */
    private $translit;

    /** @var array $arParams Параметры. */
    private $arParams = [];

    /** @var string $name Название элемента инфоблока. */
    private $name;

    /**
     * AddElement constructor.
     *
     * @param ElementModel|null $model    Модель инфоблока.
     * @param TranslitInterface $translit Транслиттер.
     */
    public function __construct(
        TranslitInterface $translit,
        ElementModel $model = null
    ) {
        $this->model = $model;
        $this->translit = $translit;
    }

    /**
     * Создать элемент инфоблока.
     *
     * @return boolean
     * @throws IblockModelException          Внутренняя ошибка.
     * @throws IblockModelDuplicateException Уже есть такие данные в базе.
     */
    public function create() : bool
    {
        if ($this->model === null) {
            throw new IblockModelException('Model of infoblock not initialized.');
        }

        if ($this->searchDupe()) {
            throw new IblockModelDuplicateException('Already have this user - phone & email saved in base.');
        }

        $defaultData = [
            'MODIFIED_BY'    => $GLOBALS['USER']->GetID(),
            'DATE_ACTIVE_FROM' => date('d.m.Y'),
            'IBLOCK_SECTION_ID' => false,
            'NAME'           => $this->name,
            'ACTIVE'         => 'Y',
            'CODE' => $this->translit->transform($this->name) . ' - ' . date('d.m.Y'),
            'SORT' => 500
        ];

        $data = array_merge($this->arParams, $defaultData);

        try {
            $item = $this->model->create($data);
            $item->refresh();

            return true;
        } catch (ExceptionFromBitrix $e) {
            return false;
        }
    }

    /**
     * Задать параметры нового элемента.
     *
     * @param array $arParams Параметры.
     *
     * @return $this
     */
    public function setParams(array $arParams): self
    {
        $this->arParams = $arParams;

        return $this;
    }

    /**
     * Задать название нового элемента.
     *
     * @param string $name Название.
     *
     * @return $this
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Задать модель инфоблока.
     *
     * @param ElementModel $model Модель инфоблока.
     *
     * @return $this
     */
    public function setModel(ElementModel $model): self
    {
        $this->model = $model;

        return $this;
    }

    /**
     * Искать дубликаты по телефону и email.
     *
     * @return boolean
     */
    private function searchDupe() : bool
    {
        $arFilter = [
          'ACTIVE' => 'Y',
          'PROPERTY_PHONE' => $this->arParams['PROPERTY_VALUES']['PHONE'],
          'PROPERTY_EMAIL' => $this->arParams['PROPERTY_VALUES']['EMAIL'],
        ];

        $items = $this->model->query()
            ->filter($arFilter)
            ->sort(['SORT' => 'ASC'])
            ->getList();

        return $items->isNotEmpty();
    }
}
