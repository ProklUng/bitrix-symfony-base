<?php

namespace Local\Tests\Invokers;

use CBitrixComponent;
use CBitrixComponentTemplate;
use Exception;

class ResultModifierInvoker extends BaseInvoker
{

    /**
     * @var CBitrixComponent
     */
    private $component;

    /**
     * @var CBitrixComponent
     */
    private $__component;

    /**
     * @var array
     */
    private $initialArResult;

    /**
     * @var array
     */
    private $totalArResult;

    /**
     * @var array
     */
    private $totalArResultCached;

    /**
     * @var array
     */
    private $arParams = [];

    /**
     * ResultModifierInvoker constructor.
     *
     * @param string $component
     * @param string $template
     */
    public function __construct($component, $template = "")
    {
        $this->component = new CBitrixComponent();
        $this->component->initComponent($component, $template);
        $this->component->initComponentTemplate();

        // Фиксирование ошибки: отсутствие $this->component
        $this->__component = $this->component;
    }

    /**
     * @param array $value
     */
    public function setArResult(array $value) : void
    {
        $this->initialArResult = $value;
    }

    /**
     * Сеттер arParams.
     *
     * @param array $value
     */
    public function setArParams(array $value)
    {
        $this->arParams = $value;
    }

    /**
     * Выполнить result_modifier.php.
     *
     * @throws Exception
     */
    public function execute()
    {
        /** @var CBitrixComponentTemplate $template */
        $template = $this->component->getTemplate();
        if ($template === null) {
            throw new Exception("Component template has not found.");
        }
        if (!$template->GetFolder() || is_dir($template->GetFolder())) {
            throw new Exception("Template folder has not found.");
        }

        $file = $_SERVER['DOCUMENT_ROOT'] . $template->GetFolder() . "/result_modifier.php";

        if (!file_exists($file)) {
            throw new Exception("result_modifier.php file has not found in folder {$template->GetFolder()}");
        }

        $arResult = $this->initialArResult;
        $arParams = $this->arParams;

        $func = function () use (& $arResult, &$arParams) {
            include func_get_arg(0);
        };

        $func($file);
        $this->totalArResult = $arResult;

        // То, что было закэшировано через $this->__component
        $this->totalArResultCached = $this->__component->arResult;
    }

    /**
     * @return array
     * @throws Exception
     */
    public function getArResult()
    {
        if ($this->totalArResult === null) {
            throw new Exception("Execute has not been");
        }
        return $this->totalArResult;
    }

    /**
     *  $this->__component->arResult;
     *
     * @return array
     * @throws Exception
     */
    public function getArResultCached()
    {
        if ($this->totalArResultCached === null) {
            throw new Exception("Execute has not been");
        }
        return $this->totalArResultCached;
    }

    /**
     * @param $paramName
     * @return mixed
     * @throws Exception
     */
    public function getArResultValue($paramName)
    {
        if ($this->totalArResult === null) {
            throw new Exception("Execute has not been");
        }
        return $this->totalArResult[$paramName];
    }
}
