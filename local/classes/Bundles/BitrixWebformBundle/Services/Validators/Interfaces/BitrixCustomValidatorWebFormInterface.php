<?php

namespace Local\Bundles\BitrixWebformBundle\Services\Validators\Interfaces;

/**
 * Interface BitrixCustomValidatorWebFormInterface
 * @package Local\Bundles\BitrixWebformBundle\Services\Validators\Interfaces
 *
 * @since 06.02.2021
 */
interface BitrixCustomValidatorWebFormInterface
{
    /**
     * @return array
     */
    public function GetDescription() : array;

    /**
     * @return array
     */
    public function GetSettings() : array;

    /**
     * @param array $arParams
     *
     * @return mixed
     */
    public function ToDB($arParams);

    /**
     * @param string $strParams
     * @return mixed
     */
    public function FromDB($strParams);

    /**
     * @param mixed $arParams
     * @param array $arQuestion
     * @param array $arAnswers
     * @param mixed $arValues
     *
     * @return boolean
     */
    public function DoValidate($arParams, $arQuestion, $arAnswers, $arValues) : bool;
}