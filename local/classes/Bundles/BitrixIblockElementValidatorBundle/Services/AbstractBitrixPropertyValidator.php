<?php

namespace Local\Bundles\BitrixIblockElementValidatorBundle\Services;

use Local\Bundles\BitrixIblockElementValidatorBundle\Services\Exceptions\ValidateErrorException;

/**
 * Class AbstractBitrixPropertyValidator
 * @package Local\Bundles\BitrixIblockElementValidatorBundle\Services
 *
 * @since 07.02.2021
 */
class AbstractBitrixPropertyValidator implements Contracts\BitrixPropertyValidatorInterface
{
    /**
     * @var string $propertyCode Символьный код свойства.
     */
    protected $propertyCode;

    /**
     * @var string $errorMessage Сообщение об ошибке.
     */
    protected $errorMessage;

    /**
     * @var integer $idIblock ID инфоблока.
     */
    protected $idIblock;

    /**
     * @inheritDoc
     */
    public function setPropertyCode(string $code) : void
    {
        $this->propertyCode = $code;
    }

    /**
     * @inheritDoc
    @throws ValidateErrorException
     */
    public function validate($value): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function setErrorMessage(string $errorMessage): void
    {
        $this->errorMessage = $errorMessage;
    }

    /**
     * @inheritDoc
     */
    public function setIdIblock(int $idIblock): void
    {
        $this->idIblock = $idIblock;
    }
}
