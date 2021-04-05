<?php

namespace Local\Bundles\BitrixWebformBundle\Services;

use CForm;
use InvalidArgumentException;
use Local\Bundles\BitrixWebformBundle\Services\Exceptions\ErrorAddingWebFormException;

/**
 * Class FormProcessor
 * @package Local\Bundles\BitrixWebformBundle\Services
 *
 * @since 29.10.2020
 */
class FormProcessor
{
    /**
     * @var FormParamsProcessor $formParamsProcessor Обработчик параметров формы.
     */
    private $formParamsProcessor;

    /**
     * @var FormResult $formResult Занесение результатов в базу.
     */
    private $formResult;

    /**
     * @var FormSearcher $formSearcher  Поисковик по формам.
     */
    private $formSearcher;

    /**
     * @var CForm $form Битриксовый CForm.
     */
    private $form;

    /** @var string $formCode Код формы. */
    private $formCode = '';

    /** @var integer $idForm ID формы. */
    private $idForm;

    /** @var array $inboundData */
    private $inboundData = [];

    /**
     * @var boolean $notifyByEmail
     */
    private $notifyByEmail = false;

    /**
     * FormProcessor constructor.
     *
     * @param FormParamsProcessor $formParamsProcessor Обработчик параметров формы.
     * @param FormResult          $formResult          Занесение результатов в базу.
     * @param FormSearcher        $formSearcher        Поисковик по формам.
     * @param CForm               $form                Битриксовый CForm.
     */
    public function __construct(
        FormParamsProcessor $formParamsProcessor,
        FormResult $formResult,
        FormSearcher $formSearcher,
        CForm $form
    ) {
        $this->formParamsProcessor = $formParamsProcessor;
        $this->formResult = $formResult;
        $this->formSearcher = $formSearcher;
        $this->form = $form;
    }

    /**
     * Обработать форму.
     *
     * @return integer ID созданной формы.
     *
     * @throws ErrorAddingWebFormException Не получилось добавить данные в форму.
     */
    public function processForm() : int
    {
        $parameters = $this->formParamsProcessor->setData(
            $this->inboundData
        )->getParameters($this->idForm);

        $this->formSearcher->setIdForm($this->idForm);

        return $this->formResult->setNotifyByEmail($this->notifyByEmail)
                                ->add($this->idForm, $parameters);
    }

    /**
     * Сеттер кода формы. Сразу вычисляется ее ID.
     *
     * @param string $formCode Символьный код формы.
     *
     * @return $this
     * @throws InvalidArgumentException Форма не найдена.
     */
    public function setFormCode(string $formCode) : self
    {
        $this->formCode = $formCode;

        $rsForm = $this->form::GetBySID($this->formCode);
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

    /**
     * Отправлять уведомление?
     *
     * @param boolean $notifyByEmail Да-нет.
     *
     * @return FormProcessor
     */
    public function setNotifyByEmail(bool $notifyByEmail): FormProcessor
    {
        $this->notifyByEmail = $notifyByEmail;

        return $this;
    }
}
