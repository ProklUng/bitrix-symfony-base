<?php

namespace Local\Bundles\BitrixWebformBundle\Services;

use CForm;
use CFormCrm;
use CFormResult;
use Local\Bundles\BitrixWebformBundle\Services\Exceptions\ErrorAddingWebFormException;

/**
 * Class FormResult
 * @package Local\Bundles\BitrixWebformBundle\Services
 *
 * @since 29.10.2020
 * @since 06.02.2021 Доработка.
 *
 * @see https://github.com/ASDAFF/hipot.framework. Light refactoring.
 */
class FormResult
{
    /**
     * @var CForm $cForm Битриксовый CForm.
     */
    private $cForm;

    /**
     * @var CFormResult $formResult Битриксовый CFormResult.
     */
    private $formResult;

    /**
     * @var CFormCrm $cFormCRM Битриксовый CFormCRM.
     */
    private $cFormCRM;

    /**
     * @var boolean $notifyByEmail Уведомлять по email.
     */
    private $notifyByEmail = true;

    /**
     * FormResult constructor.
     *
     * @param CForm       $cForm      Битриксовый CForm.
     * @param CFormResult $formResult Битриксовый CFormResult.
     * @param CFormCrm    $cFormCRM   Битриксовый CFormCRM.
     */
    public function __construct(
        CForm $cForm,
        CFormResult $formResult,
        CFormCrm $cFormCRM
    ) {
        $this->formResult = $formResult;
        $this->cFormCRM = $cFormCRM;
        $this->cForm = $cForm;
    }

    /**
     * Добавить в модуль веб-формы в форму данные
     *
     * @param integer $idWebform    Id формы, для которой пришел ответ.
     * @param array   $arValuesForm = <pre>array (
     *   [WEB_FORM_ID] => 3
     *   [web_form_submit] => Отправить
     *
     *   [form_text_18] => aafafsfasdf
     *   [form_text_19] => q1241431342
     *   [form_text_21] => afsafasdfdsaf
     *   [form_textarea_20] =>
     *   [form_text_22] => fasfdfasdf
     *   [form_text_23] => 31243123412впывапвыапывпыв аывпывпыв
     *
     *   18, 19, 21 - ID ответов у вопросов https://yadi.sk/i/_9fwfZMvO2kblA
     *   )</pre>
     *
     * @return integer
     * @throws ErrorAddingWebFormException
     *
     * @see https://github.com/ASDAFF/hipot.framework
     */
    public function add(int $idWebform, array $arValuesForm = []) : int
    {
        global $strError;

        $this->checkParameters($idWebform, $arValuesForm);

        // add result like bitrix:form.result.new
        $arValuesForm['WEB_FORM_ID'] = $idWebform;
        if ($arValuesForm['WEB_FORM_ID'] <= 0) {
            throw new ErrorAddingWebFormException(
                'Форма с ID ' . $idWebform . ' не найдена.'
            );
        }

        $arValuesForm['web_form_submit'] = 'Отправить';

        if ($idResult = $this->formResult->Add($idWebform, $arValuesForm)) {
            $this->notifyByEmail($idWebform, $idResult);

            return $idResult;
        }

        throw new ErrorAddingWebFormException($strError);
    }

    /**
     * Сеттер - отправлять почту или нет.
     *
     * @param boolean $notifyByEmail Отправлять почту.
     *
     * @return $this
     */
    public function setNotifyByEmail(bool $notifyByEmail): self
    {
        $this->notifyByEmail = $notifyByEmail;

        return $this;
    }

    /**
     * Отправить почту.
     *
     * @param mixed $idWebform ID формы.
     * @param mixed $idResult  ID результата.
     *
     * @return void
     */
    private function notifyByEmail($idWebform, $idResult) : void
    {
        if (!$this->notifyByEmail) {
            return;
        }

        // send email notifications
        $this->cFormCRM::onResultAdded($idWebform, $idResult);
        $this->formResult->SetEvent($idResult);
        $this->formResult->Mail($idResult);
    }

    /**
     * Проверка параметров формы (пропускает через битриксовые валидаторы).
     *
     * @param integer $idWebform    ID формы.
     * @param array   $arValuesForm Параметры, как для form.result.new.
     *
     * @return boolean
     * @throws ErrorAddingWebFormException Ошибки валидации.
     *
     * @since 06.02.2021
     */
    private function checkParameters(int $idWebform, array $arValuesForm = []) : bool
    {
        $result = $this->cForm::Check($idWebform, $arValuesForm, false, 'N');
        if ($result !== '') {
            throw new ErrorAddingWebFormException(
                $result
            );
        }

        return true;
    }
}
