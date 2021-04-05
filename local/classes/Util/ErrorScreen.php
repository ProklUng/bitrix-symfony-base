<?php

namespace Local\Util;

use CMain;
use RuntimeException;

/**
 * Class ErrorScreen
 * @package Local\Util
 *
 * @since 16.03.2021 Легкий рефакторинг.
 * @since 23.03.2021 Упрощение.
 */
class ErrorScreen
{
    /** @const string ERROR_PAGE Тэг под замену текстом сообщения об ошибке. */
    private const ERROR_MESSAGE_TAG = '%error_message%';

    /**
     * @var CMain $application Экземпляр $APPLICATION.
     */
    private $application;

    /**
     * @var string $template Шаблон страницы вывода ошибок.
     */
    private $template = '
    <div class="container">
    <h1>Фатальная ошибка!</h1>
    <div>
        <h2>%error_message%</h2>
    </div>

    <div>
        Свяжитесь с поддержкой, как можно скорее.
    </div>
</div>
    ';

    /**
     * ErrorScreen constructor.
     *
     * @param CMain $application Экземпляр $APPLICATION.
     *
     * @throws RuntimeException Файл-шаблон не найден.
     */
    public function __construct(
        CMain $application
    ) {
        $this->application = $application;
    }

    /**
     * Показать экран смерти.
     *
     * @param string $message Сообщение об ошибке.
     *
     * @return boolean
     */
    public function die(string $message = '') : ?bool
    {
        if (defined('PHPUNIT_COMPOSER_INSTALL') && defined('__PHPUNIT_PHAR__')) {
            echo $message;
            return false;
        }

        $content = $this->prepareErrorScreen($message);

        $this->application->RestartBuffer();
        echo $content;

        die();
    }

    /**
     * Задать шаблон.
     *
     * @param string $template Шаблон.
     *
     * @return void
     */
    public function setTemplate(string $template): void
    {
        $this->template = $template;
    }

    /**
     * Подготовить контент страницы.
     *
     * @param string $message Сообщение об ошибке.
     *
     * @return string
     */
    private function prepareErrorScreen(string $message) : string
    {
        return str_replace(self::ERROR_MESSAGE_TAG, $message, $this->template);
    }
}
