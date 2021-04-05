<?php

namespace Local\Bundles\BitrixIblockElementValidatorBundle\Services\Contracts;

use Local\Bundles\BitrixIblockElementValidatorBundle\Services\Exceptions\ValidateErrorException;
use RuntimeException;

/**
 * Interface BitrixPropertyValidatorInterface
 * @package Local\Bundles\BitrixIblockElementValidatorBundle\Services\Contracts
 *
 * @since 07.02.2021
 */
interface BitrixPropertyValidatorInterface
{
    /**
     * Задать символьный код свойства.
     *
     * @param string $code Символьный код свойства.
     *
     * @return void
     */
    public function setPropertyCode(string $code) : void;

    /**
     * Валидация.
     *
     * @param mixed $value Значение под валидацию.
     *
     * @return boolean
     * @throws ValidateErrorException Ошибка валидации.
     */
    public function validate($value) : bool;

    /**
     * Задать сообщение об ошибке.
     *
     * @param string $errorMessage Сообщение об ошибке.
     *
     * @return void
     */
    public function setErrorMessage(string $errorMessage): void;

    /**
     * Задать ID инфоблока.
     *
     * @param integer $idIblock ID инфоблока.
     *
     * @return void
     */
    public function setIdIblock(int $idIblock): void;

}
