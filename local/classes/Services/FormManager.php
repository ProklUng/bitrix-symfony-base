<?php

namespace Local\Services;

use CForm;
use CFormAnswer;
use CFormField;
use Local\Constants;
use Local\Facades\CacherFacade;

/**
 * Class FormManager
 * @package Local\Services
 */
class FormManager
{
    /** @const string URL проверки Google Captcha re2 на валидность. */
    public const GOOGLE_CAPTCHA_VERIFY_URL = 'https://www.google.com/recaptcha/api/siteverify';
    /** @const string Google Secret Site Key. */
    public const GOOGLE_CAPTCHA_SECRET_KEY = '6LcS3K0UAAAAAKTn4Odrs17pLEAMiaVuLJOToPbl';
    /** @const string Google Site Key. */
    public const GOOGLE_CAPTCHA_SITE_KEY = '6LcS3K0UAAAAAODPNv_DHGHABdhBg2C_Ubqbwdzg';

    /** ID формы
     *
     * @param string $formSid
     *
     * @return integer
     */
    public function getFormIdBySID(string $formSid): int
    {
        $rsForm = CForm::GetBySID($formSid);
        $arResult = $rsForm->Fetch();

        if ($arResult['ID'] > 0) {
            return (int)$arResult['ID'];
        }

        return 0;
    }

    /**
     * Метод возвращает ID формы по его коду из кэша.
     *
     * @param string $sFormSid Символьный код формы.
     *
     * @return integer
     * @noinspection PhpUndefinedMethodInspection
     */
    public function getFormIdBySIDCached(string $sFormSid = '')
    {
        return CacherFacade::setCacheId('form'.$sFormSid)
            ->setCallback([$this, 'getFormIdBySID'])
            ->setCallbackParams($sFormSid)
            ->setTtl(Constants::SECONDS_IN_WEEK)
            ->returnResultCache();
    }

    /** Ответы формы (не учитывает множественные вопросы!).
     *
     * @param string $sFormSid
     *
     * @return array
     */
    public function getFormAnswers(string $sFormSid): array
    {
        $arAnswerForm = [];
        $formID = $this->getFormIdBySIDCached($sFormSid);
        $is_filtered = false;

        $rsQuestions = CFormField::GetList($formID, 'N', $by = 's_id', $order = 'asc', array(), $is_filtered);

        while ($arQuestion = $rsQuestions->Fetch()) {
            $QUESTION_ID = $arQuestion['ID']; // ID вопроса

            // получим ответ
            $rsAnswers = CFormAnswer::GetList(
                $QUESTION_ID,
                $by = 's_id',
                $order = 'desc',
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
     * @param string $sFormCode     Символьный код формы.
     * @param string $sCodeQuestion Символьный код вопроса.
     *
     * @return array|mixed
     */
    public function getAllAnswersByIdQuestion(string $sFormCode, string $sCodeQuestion)
    {
        $idForm = $this->getFormIdBySID($sFormCode);

        $arForm = CForm::GetDataByID(
            $idForm,
            $form,
            $questions,
            $answers,
            $dropdown,
            $multiselect
        );

        if ($arForm) {
            return $answers[$sCodeQuestion];
        }

        return [];
    }

    /**
     * Метод возвращает ответы формы.
     *
     * @param string $sFormSid Символьный код формы.
     *
     * @return array
     * @noinspection PhpUndefinedMethodInspection
     */
    public function getFormAnswersCached(string $sFormSid = '')
    {
        return CacherFacade::setCacheId($sFormSid . '_getFormAnswers')
            ->setCallback([$this, 'getIBlockElements'])
            ->setCallbackParams($sFormSid)
            ->setTtl(Constants::SECONDS_IN_WEEK)
            ->returnResultCache();
    }
}
