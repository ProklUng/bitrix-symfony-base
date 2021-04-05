<?php

namespace Local\Validators;

use Egulias\EmailValidator\Validation\RFCValidation;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

/**
 * Class EmailValidator
 * @package Local\Validators
 *
 * @since 03.04.2021
 */
class EmailValidator extends ConstraintValidator
{
    /**
     * @inheritDoc
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof Email) {
            throw new UnexpectedTypeException($constraint, Email::class);
        }

        if ($value === null || $value === '') {
            return;
        }

        if (!is_string($value)) {
            // throw this exception if your validator cannot handle the passed type so that it can be marked as invalid
            throw new UnexpectedValueException($value, 'string');
        }

        $validator = new \Egulias\EmailValidator\EmailValidator();
        if (!$validator->isValid($value, new RFCValidation())) {
            $this->context->buildViolation($constraint->message)
                 ->setParameter('{{ string }}', $value)
                 ->addViolation();
        }
    }
}