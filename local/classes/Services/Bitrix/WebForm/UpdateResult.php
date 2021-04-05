<?php

namespace Local\Services\Bitrix\WebForm;

/**
 * Class UpdateResult
 * @package Local\Services\Bitrix\WebForm
 */
class UpdateResult extends ObjectArItem
{
    public const STATUS_OK         = 'OK';
    public const STATUS_ERROR      = 'ERROR';

    /**
     * Идентификатор записи или текст ошибки.
     * @var int|string
     */
    public $RESULT;

    /**
     * OK | ERROR успешно, либо ошибка.
     * @var string
     */
    public $STATUS;
}