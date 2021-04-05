<?php

namespace Local\Bundles\BitrixWebformBundle\Services;

use CForm;

/**
 * Class FormParamsProcessor
 * @package Local\Bundles\BitrixWebformBundle\Services
 *
 * @since 29.10.2020
 */
class FormParamsProcessor
{
    /**
     * @var CForm $form Битриксовый Form.
     */
    private $form;

    /**
     * @var FormManager $formManager Хэлперы.
     */
    private $formManager;

    /** @var array $inboundData Входящие данные. */
    private $inboundData = [];

    /**
     * FormParamsProcessor constructor.
     *
     * @param CForm       $form        Битриксовый CForm.
     * @param FormManager $formManager Хэлперы.
     */
    public function __construct(
        CForm $form,
        FormManager $formManager
    ) {
        $this->form = $form;
        $this->formManager = $formManager;
    }

    /**
     * Получить параметры формы, годные для занесения в базу.
     *
     * @param integer $idForm ID формы.
     *
     * @return array
     */
    public function getParameters(int $idForm) : array
    {
        $arDataForm = [];
        $arQuestions = $arAnswers = $arAnswersVarname = [];

        $this->form::GetResultAnswerArray(
            $idForm,
            $arQuestions,
            $arAnswers,
            $arAnswersVarname,
            []
        );

        foreach ($arQuestions as $questionID => $questionItem) {
            $rsAnswers = $this->formManager->getAnswersForm($questionID);
            if ($rsAnswers === false) {
                continue;
            }

            while ($arAnswer = $rsAnswers->Fetch()) {
                if ($arAnswer['FIELD_TYPE'] === 'text') {
                    $arDataForm['form_text_'. $arAnswer['ID']] = $this->inboundData[$questionItem['SID']];
                } elseif ($arAnswer['FIELD_TYPE'] === 'dropdown') {
                    if (mb_strtolower($this->inboundData[$questionItem['SID']]) == mb_strtolower($arAnswer['MESSAGE'])) {
                        $arDataForm['form_dropdown_'.$questionItem['SID']] = $arAnswer['ID'];
                    }
                } elseif ($arAnswer['FIELD_TYPE'] === 'textarea') {
                    $arDataForm['form_textarea_'.$arAnswer['ID']] = $this->inboundData[$questionItem['SID']];
                } elseif ($arAnswer['FIELD_TYPE'] === 'radio') {
                    if (mb_strtolower($this->inboundData[$questionItem['SID']]) == mb_strtolower($arAnswer['MESSAGE'])) {
                        $arDataForm['form_radio_'.$questionItem['SID']] = $arAnswer['ID'];
                    }
                } else {
                    $arDataForm['form_'.$arAnswer['FIELD_TYPE'].'_'.$arAnswer['ID']] = $this->inboundData[$questionItem['SID']];
                }
            }

        }

        return $arDataForm;
    }

    /**
     * Сеттер параметров.
     *
     * @param array $data Параметры.
     *
     * @return $this
     */
    public function setData(array $data) : self
    {
        $this->inboundData = $data;

        return $this;
    }
}
