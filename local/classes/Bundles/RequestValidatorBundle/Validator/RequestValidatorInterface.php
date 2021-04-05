<?php

namespace Local\Bundles\RequestValidatorBundle\Validator;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\ConstraintViolationList;

/**
 * Interface RequestValidatorInterface.
 */
interface RequestValidatorInterface
{
    /**
     * @return ConstraintViolationList
     */
    public function getErrors();

    /**
     * Gets value. If request does not have value, returns default.
     *
     * @param string $path
     *
     * @return mixed
     */
    public function get(string $path);

    /**
     * @param boolean $validate    Overwrites erroneous values with default one.
     * @param boolean $skipMissing
     *
     * @return array
     */
    public function getAll(bool $validate = true, bool $skipMissing = true);

    /**
     * @return Request
     */
    public function getRequest();
}
