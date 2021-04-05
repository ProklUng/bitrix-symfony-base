<?php

namespace Local\Services\Bitrix\WebForm;

use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use CFormCRM;
use CFormResult;

/**
 * Class FormResult
 * @package Local\Services\Bitrix\WebForm
 *
 * @since 13.10.2020
 *
 * @see https://github.com/ASDAFF/hipot.framework. Light refactoring.
 */
class FormResult
{
    /**
     * @var CFormResult $formResult
     */
    private $formResult;

    /**
     * @var CFormCRM $cFormCRM
     */
    private $cFormCRM;

    /**
     * FormResult constructor.
     *
     * @param CFormResult $formResult
     * @param CFormCRM $cFormCRM
     */
    public function __construct(
        CFormResult $formResult,
        CFormCRM $cFormCRM
    ) {
        $this->formResult = $formResult;
        $this->cFormCRM = $cFormCRM;
    }

    /**
     * Добавить в модуль веб-формы в форму данные
     *
     * @param int $WEB_FORM_ID id формы, для которой пришел ответ
     * @param array $arValuesForm = <pre>array (
     * [WEB_FORM_ID] => 3
     * [web_form_submit] => Отправить
     *
     * [form_text_18] => aafafsfasdf
     * [form_text_19] => q1241431342
     * [form_text_21] => afsafasdfdsaf
     * [form_textarea_20] =>
     * [form_text_22] => fasfdfasdf
     * [form_text_23] => 31243123412впывапвыапывпыв аывпывпыв
     *
     * 18, 19, 21 - ID ответов у вопросов https://yadi.sk/i/_9fwfZMvO2kblA
     * )</pre>
     *
     * @return bool | UpdateResult
     * @throws LoaderException
     *
     * @see https://github.com/ASDAFF/hipot.framework
     */
    public function formResultAddSimple($WEB_FORM_ID, $arValuesForm = [])
    {
        global $strError;

        if (!Loader::includeModule('form')) {
            return false;
        }

        // add result like bitrix:form.result.new
        $arValuesForm['WEB_FORM_ID'] = (int)$WEB_FORM_ID;
        if ($arValuesForm['WEB_FORM_ID'] <= 0) {
            return false;
        }

        $arValuesForm["web_form_submit"] = "Отправить";

        if ($RESULT_ID = $this->formResult::Add($WEB_FORM_ID, $arValuesForm)) {
            if ($RESULT_ID) {
                // send email notifications
                $this->cFormCRM::onResultAdded($WEB_FORM_ID, $RESULT_ID);
                $this->formResult::SetEvent($RESULT_ID);
                $this->formResult::Mail($RESULT_ID);

                return new UpdateResult(['RESULT' => $RESULT_ID, 'STATUS' => UpdateResult::STATUS_OK]);
            }

           return new UpdateResult(['RESULT' => $strError, 'STATUS' => UpdateResult::STATUS_ERROR]);
        }

        return false;
    }
}