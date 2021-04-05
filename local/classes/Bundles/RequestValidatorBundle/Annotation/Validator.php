<?php

namespace Local\Bundles\RequestValidatorBundle\Annotation;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 *
 */
class Validator
{
    /**
     * @var string $name
     */
    private $name;

    /**
     * @var array|Constraint[]
     */
    private $constraints = [];

    /**
     * @var boolean
     */
    private $required = false;

    /**
     * @var mixed
     */
    private $default = null;

    /**
     * Validator constructor.
     *
     * @param array $values
     */
    public function __construct(array $values)
    {
        foreach ($values as $k => $v) {
            if (!method_exists($this, $name = 'set'.$k)) {
                throw new \RuntimeException(sprintf('Unknown key "%s" for annotation "@%s".', $k, static::class));
            }

            $this->$name($v);
        }
    }

    public function getAliasName()
    {
        return 'request_validator';
    }

    public function allowArray()
    {
        return true;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return array|Constraint[]
     */
    public function getConstraints()
    {
        return $this->constraints;
    }

    /**
     * @param array $constraints
     */
    public function setConstraints($constraints)
    {
        $this->constraints = $constraints;
    }

    /**
     * @param $key
     */
    public function removeConstraint($key)
    {
        unset($this->constraints[$key]);
    }

    /**
     * @param Constraint $constraint
     */
    public function addConstraint(Constraint $constraint)
    {
        $this->constraints[] = $constraint;
    }

    /**
     * @return mixed
     */
    public function getDefault()
    {
        return $this->default;
    }

    /**
     * @param mixed $default
     */
    public function setDefault($default)
    {
        $this->default = $default;
    }

    /**
     * @return boolean
     */
    public function isRequired()
    {
        return $this->required;
    }

    /**
     * @return boolean
     */
    public function isOptional()
    {
        return !$this->required;
    }

    /**
     * @param boolean $required
     */
    public function setRequired($required)
    {
        $this->required = $required;
    }
}
