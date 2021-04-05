<?php

namespace Local\Bundles\BitrixWebformBundle\Services;

use CDBResult;
use CForm;
use CFormAnswer;
use CFormField;

/**
 * Class FormManager
 * @package Local\Bundles\BitrixWebformBundle\Services
 */
class FormManager
{
    /**
     * @var CForm $cForm Битриксовый CForm.
     */
    private $cForm;

    /**
     * @var CFormField $cFormField Битриксовый CFormField.
     */
    private $cFormField;

    /**
     * @var CFormAnswer $cFormAnswer Битриксовый CFormAnswer.
     */
    private $cFormAnswer;

    /**
     * FormManager constructor.
     *
     * @param CForm       $cForm       Битриксовый CForm.
     * @param CFormField  $cFormField  Битриксовый CFormField.
     * @param CFormAnswer $cFormAnswer Битриксовый CFormAnswer.
     */
    public function __construct(CForm $cForm, CFormField $cFormField, CFormAnswer $cFormAnswer)
    {
        $this->cForm = $cForm;
        $this->cFormField = $cFormField;
        $this->cFormAnswer = $cFormAnswer;
    }

    /** ID формы
     *
     * @param string $formSid Символьный код формы.
     *
     * @return integer
     */
    public function getFormIdBySID(string $formSid): int
    {
        $rsForm = $this->cForm::GetBySID($formSid);
        $arResult = $rsForm->Fetch();

        if ($arResult['ID'] > 0) {
            return (int)$arResult['ID'];
        }

        return 0;
    }

    /** Ответы формы (не учитывает множественные вопросы!).
     *
     * @param string $sFormSid Код формы.
     *
     * @return array
     */
    public function getFormAnswers(string $sFormSid): array
    {
        $arAnswerForm = [];
        $formID = $this->getFormIdBySID($sFormSid);
        $is_filtered = false;

        $by = 's_id';
        $order = 'desc';

        $rsQuestions = $this->cFormField->GetList($formID, 'N', $by, $order, [], $is_filtered);

        $order = 'asc';

        while ($arQuestion = $rsQuestions->Fetch()) {
            $QUESTION_ID = $arQuestion['ID']; // ID вопроса

            $rsAnswers = $this->cFormAnswer->GetList(
                $QUESTION_ID,
                $by,
                $order,
                [],
                $is_filtered
            );

            if ($arAnswer = $rsAnswers->Fetch()) {
                $arAnswerForm[$arQuestion['SID']] = [
                    'QUESTION' => $arQuestion,
                    'ANSWER' => $arAnswer,
                ];
            }
        }

        return $arAnswerForm;
    }

    /**
     * Получить все ответы по коду вопроса.
     *
     * @param string $formCode     Символьный код формы.
     * @param string $codeQuestion Символьный код вопроса.
     *
     * @return array|mixed
     */
    public function getAllAnswersByIdQuestion(string $formCode, string $codeQuestion)
    {
        $form = $questions = $answers = $multiselect = [];

        $idForm = $this->getFormIdBySID($formCode);

        $arForm = $this->cForm::GetDataByID(
            $idForm,
            $form,
            $questions,
            $answers,
            $dropdown,
            $multiselect
        );

        if ($arForm) {
            return $answers[$codeQuestion];
        }

        return [];
    }

    /**
     *
     * @param mixed $questionID ID вопроса.
     *
     * @return CDBResult|false
     *
     * @since 29.10.2020
     */
    public function getAnswersForm($questionID)
    {
        $by = 's_id';
        $order = 'asc';
        $is_filtered = false;

        return $this->cFormAnswer->GetList(
            $questionID,
            $by,
            $order,
            [],
            $is_filtered
        );
    }
}
