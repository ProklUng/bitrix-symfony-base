<?php

namespace Local\Validators;

use Symfony\Component\Validator\Constraint;

/**
 * Class Email
 * @package Local\Validators
 *
 * @Annotation
 *
 * @since 03.04.2021
 */
class Email extends Constraint
{
    /**
     * @var string $message
     */
    public $message = 'The string "{{ string }}" not valid email.';

}