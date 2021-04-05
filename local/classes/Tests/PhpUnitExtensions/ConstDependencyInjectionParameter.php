<?php

namespace Local\Tests\PhpUnitExtensions;

class ConstDependencyInjectionParameter
{
    private $value;

    public function get()
    {
        return $this->value;
    }

    public function set($value): self
    {
        $this->value = $value;

        return $this;
    }
}
