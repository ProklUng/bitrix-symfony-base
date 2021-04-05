<?php

namespace Local\Bundles\BitrixWebformBundle\Services;

use CDBResult;
use CForm;
use CFormResult;
use InvalidArgumentException;

/**
 * Class FormSearcher
 * @package Local\Bundles\BitrixWebformBundle\Services
 *
 * @since 30.10.2020
 */
class FormSearcher
{
    /**
     * @var CFormResult $formResult Битриксовый CFormResult.
     */
    private $formResult;

    /**
     * @var CForm $form Битриксовый CForm.
     */
    private $form;

    /** @var integer $idForm ID формы. */
    private $idForm;

    /** @var array $arFilter Фильтр. */
    protected $arFilter = [];

    /** @var integer $limit Размер выборки. */
    private $limit = 5000;

    /**
     * FormSearcher constructor.
     *
     * @param CFormResult $formResult Битриксовый CFormResult.
     * @param CForm       $form       Битриксовый CForm.
     */
    public function __construct(
        CFormResult $formResult,
        CForm $form
    ) {
        $this->formResult = $formResult;
        $this->form = $form;
    }

    /**
     * Фильтр.
     *
     * @param array $value Массив вида
     * ['EMAIL' => 'f@f.ru']. Или [['EMAIL' => 'f@f.ru'], ['PHONE' => '+791567899']].
     *
     * @return $this
     */
    public function addFilter(array $value) : self
    {
        /**
         * Смотри ниже.
         */
        if (count($value) === 1) {
            $sid = array_key_first($value);
            $this->arFilter[] = [
                'SID' => $sid,
                'VALUE' => $value[$sid],
                'PARAMETER_NAME' => stripos($sid, '_id') === false ? 'USER' : 'ANSWER_TEXT'
            ];

            return $this;
        }

        /**
         * @internal
         *
         * Особенность: для полей типа dropdown искать надо по ANSWER_TEXT, а не USER.
         * Потому соглашение: если в названии поля присутствует _ID (или _id) - поле
         * считается dropdown.
         */
        foreach ($value as $key => $item) {
            $this->arFilter[] = [
                'SID' => $key,
                'VALUE' => $item ?? '~_',
                'PARAMETER_NAME' => stripos($key, '_id') === false ? 'USER' : 'ANSWER_TEXT'
            ];
        }

        return $this;
    }

    /**
     * Поиск.
     *
     * @return array
     *
     * @throws InvalidArgumentException Не задали ID формы.
     */
    public function filter() : array
    {
        $result = [];

        $rsResults = $this->query();

        if (!$rsResults) {
            return [];
        }

        while ($arResult = $rsResults->Fetch()) {
            $result[] = $arResult;
        }

        return $result;
    }

    /**
     * Проверка на существование форм с заданными параметрами.
     *
     * @return boolean
     *
     * @throws InvalidArgumentException Не задали ID формы.
     */
    public function exist() : bool
    {
        $rsResults = $this->query();
        if (!$rsResults) {
            return false;
        }

        if ($rsResults->Fetch()) {
            return true;
        }

        return false;
    }

    /**
     * ID формы.
     *
     * @param integer $idForm ID формы.
     *
     * @return $this
     */
    public function setIdForm(int $idForm): self
    {
        $this->idForm = $idForm;

        return $this;
    }

    /**
     * Ограничить выборку.
     *
     * @param integer $limit Ограничить выборку.
     *
     * @return $this
     */
    public function setLimit(int $limit): self
    {
        $this->limit = $limit;

        return $this;
    }

    /**
     * Код формы. Сразу получается ID формы.
     *
     * @param string $formCode Символьный код формы.
     *
     * @return FormSearcher
     *
     * @throws InvalidArgumentException Если форма не существует.
     */
    public function setFormCode(string $formCode): self
    {
        $rsForm = $this->form::GetBySID($formCode);
        $arResult = $rsForm->Fetch();

        if ($arResult['ID'] > 0) {
            $this->idForm = $arResult['ID'];
            return $this;
        }

        throw new InvalidArgumentException(
            sprintf(
                'Форма с кодом %s не существует.',
                $formCode
            )
        );
    }

    /**
     * Запрос.
     *
     * @return CDBResult|false
     *
     * @throws InvalidArgumentException Не задали ID формы.
     */
    protected function query()
    {
        if ($this->idForm === 0) {
            throw new InvalidArgumentException('ID формы не инициализировано.');
        }

        /** @psalm-suppress PossiblyUndefinedVariable */
        $arFilter['FIELDS'] = $this->arFilter;

        $by='s_timestamp';
        $order='desc';

        return $this->formResult->GetList(
            $this->idForm,
            $by,
            $order,
            $arFilter,
            $is_filtered,
            'N',
            $this->limit
        );
    }
}
