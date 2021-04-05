<?php

namespace Local\Util\Autowiring;

/**
 * Class BaseServiceClass
 * Базовый класс для авто-вайринга свойств классов.
 * @package Local\Util\Autowiring
 */
class BaseAutowiringProperty
{
    /**
     * BaseServiceClass constructor.
     */
    public function __construct()
    {
        $wirePropertiesProcessor = new WireProperties();
        $wirePropertiesProcessor->process($this);
    }
}
