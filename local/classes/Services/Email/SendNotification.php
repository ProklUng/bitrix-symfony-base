<?php

namespace Local\Services\Email;

use Bitrix\Main\Mail\Event;
use CFile;

/**
 * Class SendNotification
 * @package Local\Services\Email
 *
 * @since 08.09.2020
 */
class SendNotification
{
    /**
     * @var CFile $fileHandler Битриксовый CFile.
     */
    private $fileHandler;

    /**
     * @var Event $bitrixEventHandler Битриксовый Event manager.
     */
    private $bitrixEventHandler;

    /** @var string Название события. */
    private $eventName;

    /** @var array $arFiles Аттачи.*/
    private $arFiles = [];
    /** @var array $arFields Поля события. */
    private $arFields = [];

    /**
     * SendNotification constructor.
     *
     * @param Event $bitrixEventHandler Битриксовый Event manager.
     * @param CFile $fileHandler        Битриксовый CFile.
     */
    public function __construct(
        Event $bitrixEventHandler,
        CFile $fileHandler
    ) {
        $this->fileHandler = $fileHandler;
        $this->bitrixEventHandler = $bitrixEventHandler;
    }

    /**
     * @return mixed
     */
    public function send()
    {
        return $this->bitrixEventHandler::send(
            [
                'EVENT_NAME' => $this->eventName,
                'LID' => 's1',
                'C_FIELDS' => $this->arFields,
                'FILE' => $this->arFiles,
            ]
        );
    }

    /**
     * Название события.
     *
     * @param string $eventName Событие.
     *
     * @return $this
     */
    public function setEventName(string $eventName) : self
    {
        $this->eventName = $eventName;
        return $this;
    }

    /**
     * Приаттаченные файлы.
     *
     * @param string $filename Имя файла.
     *
     * @return $this
     */
    public function setFiles(string $filename) : self
    {
        $this->arFiles[] = $this->registerFile($filename);

        return $this;
    }

    /**
     * Поля события.
     *
     * @param array $arFields Поля события.
     *
     * @return $this
     */
    public function setFields(array $arFields) : self
    {
        $this->arFields = $arFields;
        return $this;
    }

    /**
     * Зарегистрировать файл в Битриксе.
     *
     * @param string $filePath    Путь к файлу.
     * @param string $strSavePath Нагрузка.
     *
     * @return mixed
     */
    protected function registerFile(string $filePath, string $strSavePath = 'pdf'): int
    {
        $arDataFiles = $this->fileHandler::MakeFileArray($filePath);

        return $this->fileHandler::SaveFile($arDataFiles, $strSavePath);
    }
}
