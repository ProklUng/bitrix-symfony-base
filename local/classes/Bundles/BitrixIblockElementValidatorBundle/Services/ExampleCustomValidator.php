<?php

namespace Local\Bundles\BitrixIblockElementValidatorBundle\Services;

use Local\Bundles\BitrixIblockElementValidatorBundle\Services\Exceptions\ValidateErrorException;

/**
 * Class ExampleCustomValidator
 * @package Local\Bundles\BitrixIblockElementValidatorBundle\Services
 *
 * @since 07.02.2021
 */
class ExampleCustomValidator extends AbstractBitrixPropertyValidator
{
    /**
     * ExampleCustomValidator constructor.
     *
     * @param string $errorMessage Сообщение об ошибке.
     */
    public function __construct(string $errorMessage)
    {
        $this->errorMessage = $errorMessage;
    }

    /**
     * @inheritDoc
     * @throws ValidateErrorException
     */
    public function validate($value): bool
    {
        if ($value === 'funt@mail.ru') {
            throw new ValidateErrorException(
                $this->errorMessage
            );
        }

        return true;
    }
}
