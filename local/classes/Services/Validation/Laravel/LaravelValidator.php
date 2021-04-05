<?php

namespace Local\Services\Validation\Laravel;

use Local\Services\Validation\Interfaces\ValidatorInterface;
use Local\Services\Validation\Traits\Validatable;
use Symfony\Component\Yaml\Yaml;

/**
 * Class LaravelValidator
 * @package Local\Services\Validation
 *
 * @since 08.09.2020 Изменено название интерфейса.
 */
class LaravelValidator implements ValidatorInterface
{
    use Validatable;

    /** @var array $rules Правила валидации. */
    private $rules;

    /**
     * LaravelValidator constructor.
     *
     * @param string      $documentRoot DOCUMENT_ROOT.
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
