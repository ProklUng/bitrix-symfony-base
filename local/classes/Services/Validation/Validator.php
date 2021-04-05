<?php

namespace Local\Services\Validation;

use Local\Services\Validation\Traits\Validatable;
use Symfony\Component\Yaml\Yaml;

/**
 * Class Validator
 * @package Local\Services
 */
class Validator
{
    use Validatable;

    /** @var array $rules Правила валидации. */
    private $rules;

    /**
     * LaravelValidator constructor.
     *
     * @param string      $documentRoot
     * @param string|null $rulesYaml    Файл с правилами валидации Yaml.
     * @param array       $rules        Правила валидации.
     */
    public function __construct(
        string $documentRoot,
        string $rulesYaml = null,
        array $rules = []
    ) {
        $this->rules = $rules;

        if (empty($this->rules) && $rulesYaml) {
            $this->rules = Yaml::parseFile($documentRoot . $rulesYaml);
        }
    }

    /**
     * Сеттер правил валидации.
     *
     * @param array $rules Правила.
     *
     * @return mixed
     */
    public function setRules(array $rules)
    {
        $this->rules = $rules;

        return $this;
    }
}
