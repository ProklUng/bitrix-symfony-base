<?php

namespace Local\Validators;

use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

/**
 * Class PhoneValidator
 * @package Local\Validators
 *
 * @since 03.04.2021
 */
class PhoneValidator extends ConstraintValidator
{
    /** @const string DEFAULT_COUNTRY Код страны по умолчанию. */
    private const DEFAULT_COUNTRY = 'RU';

    /**
     * @inheritDoc
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof Phone) {
            throw new UnexpectedTypeException($constraint, Phone::class);
        }

        if ($value === null || $value === '') {
            return;
        }

        if (strpos($value, '+') === 0) {
            $defaultRegion = null;
        }

        if (!is_string($value)) {
            // throw this exception if your validator cannot handle the passed type so that it can be marked as invalid
            throw new UnexpectedValueException($value, 'string');
        }

        $defaultRegion = self::DEFAULT_COUNTRY;

        $phoneUtil = PhoneNumberUtil::getInstance();
        $value = (string) $value;

        $phoneNumber = null;

        try {
            $phoneNumber = $phoneUtil->parse($value, $defaultRegion);
        } catch (NumberParseException $e) {
            $this->context->buildViolation($constraint->message)
                          ->setParameter('{{ string }}', $value)
                          ->addViolation();
            return;
        }

        $phoneUtil->format($phoneNumber, PhoneNumberFormat::INTERNATIONAL);

        if ($phoneUtil->isValidNumber($phoneNumber) === false) {
            $this->context->buildViolation($constraint->message)
                          ->setParameter('{{ string }}', $value)
                          ->addViolation();
        }
    }
}