<?php

namespace Local\Tests\Invokers;

use CBitrixComponent;
use Exception;
use ReflectionException;

/**
 * @author Maxim Sokolovsky <sokolovsky@worksolutions.ru>
 */
class ComponentInvoker extends BaseInvoker
{

    /**
     * @var
     */
    private $name;

    /**
     * @var array
     */
    private $params = array();

    /**
     * @var string
     */
    private $path;

    /**
     * @var CBitrixComponent
     */
    private $bitrixComponent;

    /**
     * @var mixed
     */
    private $executeResult;

    /**
     * @var CBitrixComponent
     */
    private $runComponent;

    private $template;

    /**
     * ComponentInvoker constructor.
     *
     * @param string $name
     * @param mixed $template
     */
    public function __construct(string $name, $template = "")
    {
        $this->name = $name;
        $this->template = $template;
        $this->path = Module::getBitrixPath();

        $this->bitrixComponent = new CBitrixComponent($name);
        $this->bitrixComponent->initComponent($name, $template);
    }

    /**
     * @param array $arParams
     */
    public function setParams(array $arParams)
    {
        $this->params = $arParams;
    }

    /**
     * @throws ReflectionException
     */
    public function execute()
    {
        $classOfComponent = static::getObjectPropertyValue($this->bitrixComponent, "classOfComponent");

        if ($classOfComponent) {
            /** @var CBitrixComponent $component  */
            $component = new $classOfComponent($this);
            $component->arParams = $component->onPrepareComponentParams($this->params);
            static::invokeMethod($component, "__prepareComponentParams", $component->arParams);
            $component->onIncludeComponentLang();

            // execute
            $this->executeResult = $component->executeComponent();

            $this->runComponent = $component;
        } else {
            static::invokeMethod($this->bitrixComponent, "__prepareComponentParams", $this->params);
            $this->bitrixComponent->arParams = $this->params;
            $this->bitrixComponent->includeComponentLang();
            // execute.
            if ($this->template === false) {
                ob_start();
            }

            $this->executeResult = $this->bitrixComponent->executeComponent();
            if ($this->template === false) {
                ob_get_clean();
            }

            $this->runComponent = $this->bitrixComponent;
        }
    }

    /**
     * @param string $name
     * @return mixed
     * @throws Exception
     */
    public function getResultValue($name)
    {
        $this->throwIfWasntExecute();
        return $this->runComponent->arResult[$name];
    }

    /**
     * Неполноценный $arResult (как минимум, в случае со старыми компонентами). Полный $arResult существует только
     * в контексте функции __includeComponent.
     *
     * @return array
     * @throws Exception
     */
    public function getArResult()
    {
        $this->throwIfWasntExecute();

        return $this->runComponent->arResult;
    }

    /**
     * @return mixed
     */
    public function getExecuteResult()
    {
        return $this->executeResult;
    }

    /**
     * @throws Exception
     */
    private function throwIfWasntExecute()
    {
        if ($this->runComponent !== null) {
            return;
        }

        throw new Exception("Execute of invoker has not been yet.");
    }
}
