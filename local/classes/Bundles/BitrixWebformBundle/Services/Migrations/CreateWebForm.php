<?php

namespace Local\Bundles\BitrixWebformBundle\Services\Migrations;

use CForm;
use CFormField;
use CFormStatus;
use LogicException;
use RuntimeException;

/**
 * Class CreateWebForm
 * @package Local\Bundles\BitrixWebformBundle\Services\Migrations
 *
 * @since 06.02.2021
 */
class CreateWebForm
{
    /**
     * @var CForm $cForm
     */
    private $cForm;

    /**
     * @var CFormStatus $cFormStatus
     */
    private $cFormStatus;

    /**
     * @var CFormField $cFormField
     */
    private $cFormField;

    /**
     * @var integer $idForm
     */
    private $idForm = 0;

    /**
     * @var string $sidForm
     */
    private $sidForm;

    /**
     * @var string $nameForm
     */
    private $nameForm;

    /**
     * @var integer $sort
     */
    private $sort = 100;

    /**
     * @var string $buttonText
     */
    private $buttonText = 'Отправить';

    /**
     * @var array $menuItem Названия в меню админки. ['ru' => 'Форма обратной связи ', 'en' => 'Form feedback']
     */
    private $menuItem = [
        'ru' => 'Форма',
        'en' => 'Form',
    ];

    /**
     * @var array $sites
     */
    private $sites = ['s1'];

    /**
     * @var array $questions
     */
    private $questions = [];

    /**
     * CreateWebForm constructor.
     *
     * @param CForm       $cForm       Битриксовый CForm.
     * @param CFormStatus $cFormStatus Битриксовый CFormStatus.
     * @param CFormField  $cFormField  Битриксовый CFormField.
     */
    public function __construct(CForm $cForm, CFormStatus $cFormStatus, CFormField $cFormField)
    {
        $this->cForm = $cForm;
        $this->cFormStatus = $cFormStatus;
        $this->cFormField = $cFormField;
    }

    /**
     * Удалить форму по ID.
     *
     * @param integer $idForm ID формы.
     *
     * @return void
     * @throws RuntimeException
     */
    public function deleteForm(int $idForm)
    {
        $result = $this->cForm::Delete($idForm);
        if ($result <= 0) {
            /** @psalm-suppress InvalidGlobal */
            global $strError;
            throw new RuntimeException(
                'Ошибка удаления формы : ' . $strError
            );
        }
    }

    /**
     * Удалить форму по символьному коду.
     *
     * @param string $sidForm Код формы.
     *
     * @return void
     * @throws RuntimeException
     */
    public function deleteFormByCode(string $sidForm): void
    {
        $result = $this->cForm::Delete(
            $this->getFormIdBySID($sidForm)
        );

        if ($result <= 0) {
            /** @psalm-suppress InvalidGlobal */
            global $strError;
            throw new RuntimeException(
                'Ошибка удаления формы : ' . $strError
            );
        }
    }

    /**
     * Создать форму.
     *
     * @return CreateWebForm
     * @throws RuntimeException
     */
    public function createForm(): CreateWebForm
    {
        $arFieldsForm = [
            'NAME' => $this->nameForm,
            'SID' => $this->sidForm,
            'C_SORT' => $this->sort,
            'BUTTON' => $this->buttonText,
            'arSITE' => $this->sites,
            'arMENU' => $this->menuItem,
        ];

        $this->idForm = $this->cForm::Set($arFieldsForm, false, 'N');
        if ($this->idForm <= 0) {
            /** @psalm-suppress InvalidGlobal */
            global $strError;
            throw new RuntimeException(
                'Ошибка создания формы : ' . $strError
            );
        }

        return $this;
    }

    /**
     * Добавить текстовое поле.
     *
     * @param string  $nameField Символьный код поля.
     * @param string  $title     Title поля (админке).
     * @param integer $sort      Индекс сортировки.
     *
     * @return $this
     */
    public function addTextField(string $nameField, string $title, int $sort = 500): CreateWebForm
    {
        $result = [
            $nameField => [
                'FIELD_TYPE' => 'text',
                'TITLE' => $title,
                'SORT' => $sort,
                'arANSWER' => [
                    [
                        'FIELD_TYPE' => 'text',
                        'ACTIVE' => 'Y',
                        'C_SORT' => 50,
                        'MESSAGE' => $nameField,
                        'VALUE' => '',

                    ],
                ],
            ],
        ];

        $this->questions = array_merge($this->questions, $result);

        return $this;
    }

    /**
     * Добавить поле textarea.
     *
     * @param string  $nameField Символьный код поля.
     * @param string  $title     Title поля (админке).
     * @param integer $sort      Индекс сортировки.
     *
     * @return $this
     */
    public function addTextareaField(string $nameField, string $title, int $sort = 500): CreateWebForm
    {
        $textarea = [
            [
                'MESSAGE' => $title, // параметр ANSWER_TEXT
                'C_SORT' => 100, // порядок фортировки
                'ACTIVE' => 'Y', // флаг активности
                'FIELD_TYPE' => 'textarea',
                'FIELD_PARAM' => 'Y',
            ],

        ];

        $result = [
            $nameField => [
                'FIELD_TYPE' => 'textarea',
                'TITLE' => $title,
                'SORT' => $sort,
                'arANSWER' => $textarea,
                'arFILTER_ANSWER_TEXT' => ['textarea'],
            ],
        ];

        $this->questions = array_merge($this->questions, $result);

        return $this;
    }

    /**
     * Добавить поле dropdown.
     *
     * @param string  $nameField Символьный код поля.
     * @param string  $title     Title поля (админке).
     * @param array   $values    Значения dropdown.
     * @param integer $sort      Индекс сортировки.
     *
     * @return $this
     */
    public function addDropdown(string $nameField, string $title, array $values, int $sort = 500): CreateWebForm
    {
        $answer = [];

        foreach ($values as $value) {
            $answer[] = [
                'MESSAGE' => $value,
                'C_SORT' => 100,
                'ACTIVE' => 'Y',
                'FIELD_TYPE' => 'dropdown',
                'FIELD_PARAM' => ''
            ];
        }

        $result = [
            $nameField => [
                'FIELD_TYPE' => 'dropdown',
                'TITLE' => $title,
                'SORT' => $sort,
                'arFILTER_ANSWER_TEXT' => ['dropdown'],
                'arANSWER' => $answer,
            ],
        ];

        $this->questions = array_merge($this->questions, $result);

        return $this;
    }

    /**
     * Добавить кнопку - Yes-No.
     *
     * @param string  $nameField Символьный код поля.
     * @param string  $title     Title поля (админке).
     * @param integer $sort      Индекс сортировки.
     *
     * @return $this
     */
    public function addRadioButtonYesNow(string $nameField, string $title, int $sort = 500): CreateWebForm
    {
        /** @psalm-suppress PossiblyUndefinedVariable */
        $answer[] = [
            'MESSAGE' => 'да',
            'C_SORT' => 100,
            'ACTIVE' => 'Y',
            'FIELD_TYPE' => 'radio',
            'FIELD_PARAM' => 'checked class="inputradio"'
        ];

        $answer[] = [
            'MESSAGE' => 'нет',
            'C_SORT' => 200,
            'ACTIVE' => 'Y',
            'FIELD_TYPE' => 'radio',
        ];

        $result = [
            $nameField => [
                'FIELD_TYPE' => 'checkbox',
                'TITLE' => $title,
                'SORT' => $sort,
                'arFILTER_ANSWER_TEXT' => ['radio'],
                'arANSWER' => $answer,
            ],
        ];

        $this->questions = array_merge($this->questions, $result);

        return $this;
    }

    /**
     * Создать поля формы.
     *
     * @return $this
     * @throws RuntimeException
     */
    public function addQuestions() : CreateWebForm
    {
        if ($this->idForm === 0) {
            throw new LogicException(
                'Не задано ID формы. Забыли задать или создать?'
            );
        }

        foreach ($this->questions as $key => $questionData) {
            $arFiledNew = [
                'FORM_ID' => $this->idForm,
                'ACTIVE' => 'Y',
                'TITLE' => $questionData['TITLE'],
                'SID' => $key,
                'C_SORT' => $questionData['SORT'],
                'ADDITIONAL' => 'N',
                'IN_RESULTS_TABLE' => 'Y',
                'IN_EXCEL_TABLE' => 'Y',
                'FIELD_TYPE' => $questionData['FIELD_TYPE'],
                'FILTER_TITLE' => $questionData['TITLE'],
                'RESULTS_TABLE_TITLE' => $questionData['TITLE'],
            ];

            if (array_key_exists('arANSWER', $questionData)) {
                $arFiledNew['arANSWER'] = $questionData['arANSWER'];
                $arFiledNew['arFILTER_ANSWER_TEXT'] = $questionData['arFILTER_ANSWER_TEXT'];
            } else {
                $arFiledNew['FIELD_TYPE'] = 'text';
            }

            if ($questionData['FIELD_TYPE'] === 'textarea') {
                $arFiledNew['FIELD_TYPE'] = $questionData['FIELD_TYPE'];
            }

            $newFieldId = $this->cFormField->Set($arFiledNew, false, 'N');
            if ($newFieldId <= 0) {
                /** @psalm-suppress InvalidGlobal */
                global $strError;

                throw new RuntimeException(
                  'Ошибка добавления поля в веб форму '. $strError
                );
            }
        }

        return $this;
    }

    /**
     * Создать шаблоны для писем.
     *
     * @return $this
     * @throws LogicException
     */
    public function createEmailTemplate() : CreateWebForm
    {
        if ($this->idForm === 0) {
            throw new LogicException(
                'Не задано ID формы. Забыли задать или создать?'
            );
        }

        $templates = $this->cForm::SetMailTemplate($this->idForm);

        $this->cForm::Set(
            ['arMAIL_TEMPLATE' => $templates],
            $this->idForm
        );

        return $this;
    }

    /**
     * Создать статус формы.
     *
     * @return $this
     * @throws RuntimeException|LogicException
     */
    public function createStatus() : CreateWebForm
    {
        if (!$this->idForm) {
            throw new LogicException(
              'Не задано ID формы. Забыли задать или создать?'
            );
        }

        $arFields = [
            'FORM_ID' => $this->idForm,
            'C_SORT' => 500,
            'ACTIVE' => 'Y',
            'TITLE' => 'DEFAULT',
            'DESCRIPTION' => 'DEFAULT',
            'CSS' => 'statusgreen',
            'HANDLER_OUT' => '',
            'HANDLER_IN' => '',
            'DEFAULT_VALUE' => 'Y',
            'arPERMISSION_VIEW' => [30],
            'arPERMISSION_MOVE' => [30],
            'arPERMISSION_EDIT' => [30],
            'arPERMISSION_DELETE' =>[30],
        ];

        $newFieldId = $this->cFormStatus->Set($arFields, false, 'N');

        if ($newFieldId <= 0) {
            global $strError;

            throw new RuntimeException(
                'Ошибка добавления статуса в веб форму '. $strError
            );
        }

        return $this;
    }

    /**
     * @param string $sidForm
     *
     * @return CreateWebForm
     */
    public function setSidForm(string $sidForm): CreateWebForm
    {
        $this->sidForm = $sidForm;

        return $this;
    }

    /**
     * Задать название формы.
     *
     * @param string $nameForm Название формы.
     *
     * @return CreateWebForm
     */
    public function setNameForm(string $nameForm): CreateWebForm
    {
        $this->nameForm = $nameForm;

        return $this;
    }

    /**
     * Задать индекс сортировки.
     *
     * @param integer $sort Сортировка.
     *
     * @return CreateWebForm
     */
    public function setSort(int $sort): CreateWebForm
    {
        $this->sort = $sort;

        return $this;
    }

    /**
     * @param string $buttonText
     *
     * @return CreateWebForm
     */
    public function setButtonText(string $buttonText): CreateWebForm
    {
        $this->buttonText = $buttonText;

        return $this;
    }

    /**
     * @param array $menuItem
     *
     * @return CreateWebForm
     */
    public function setMenuItem(array $menuItem): CreateWebForm
    {
        $this->menuItem = $menuItem;

        return $this;
    }

    /**
     * Задать параметр сайты.
     *
     * @param array $sites Сайты. Массив вида ['s1', 's2'].
     *
     * @return CreateWebForm
     */
    public function setSites(array $sites): CreateWebForm
    {
        $this->sites = $sites;

        return $this;
    }

    /**
     * @param integer $idForm
     *
     * @return CreateWebForm
     */
    public function setIdForm(int $idForm): CreateWebForm
    {
        $this->idForm = $idForm;

        return $this;
    }

    /**
     * ID формы. Если 0 - не инициализирована или не создана.
     *
     * @return integer
     */
    public function getIdForm(): int
    {
        return $this->idForm;
    }

    /**
     * ID формы по символьному коду.
     *
     * @param string $formSid Символьный код формы.
     *
     * @return integer
     */
    private function getFormIdBySID(string $formSid): int
    {
        $rsForm = $this->cForm::GetBySID($formSid);
        $arResult = $rsForm->Fetch();

        if ($arResult['ID'] > 0) {
            return (int)$arResult['ID'];
        }

        return 0;
    }
}
