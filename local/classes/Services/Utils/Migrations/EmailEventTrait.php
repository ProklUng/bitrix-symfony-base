<?php

namespace Local\Services\Utils\Migrations;

use CEventType;
use Exception;

/**
 * Trait EmailEventTrait
 * @package Local\Services\Utils\Migrations
 *
 * @since 11.04.2021
 */
trait EmailEventTrait
{
    /**
     * Создает новый тип почтовых событий.
     *
     * @param array $eventData
     *
     * @return array
     *
     * @throws Exception
     */
    public function createEmailEventType(array $eventData): array
    {
        if ($this->findEmailEventType($eventData)) {
            throw new Exception("Email event type {$eventData['EVENT_NAME']} already exists");
        }

        $res = CEventType::add($eventData);
        if (!$res) {
            throw new Exception("Can't create email event type {$eventData['EVENT_NAME']}: {$et->LAST_ERROR}");
        }

        return ["Email event type {$eventData['EVENT_NAME']}({$res}) created"];
    }

    /**
     * Обновляет тип почтовых событий.
     *
     * @param array $eventData
     *
     * @return array
     *
     * @throws Exception
     */
    public function updateEmailEventType(array $eventData): array
    {
        if (!($event = $this->findEmailEventType($eventData))) {
            throw new Exception("Can't find {$eventData['EVENT_NAME']} email event type");
        }
        $et = new CEventType;
        unset($eventData['EVENT_NAME'], $eventData['LID']);
        $result = CEventType::update(['ID' => $event['ID']], $eventData);
        if (!$result) {
            throw new Exception("Can't update email event type {$eventData['EVENT_NAME']}: {$et->LAST_ERROR}");
        }

        return ["Email event type {$event['EVENT_NAME']}({$event['ID']}) updated"];
    }

    /**
     * Удаляет тип почтового события по его идентификатору (EVENT_NAME).
     *
     * @param array $eventData
     *
     * @return array
     * @throws Exception
     */
    public function deleteEmailEventType(array $eventData): array
    {
        if (!($event = $this->findEmailEventType($eventData))) {
            throw new Exception("Can't find {$eventData['EVENT_NAME']} email event type");
        }

        CEventType::delete(['ID' => $event['ID']]);

        return ["Email event type {$eventData['EVENT_NAME']}({$event['ID']}) deleted"];
    }

    /**
     * Ищет тип почтового события по массиву параметров.
     *
     * @param array $eventData
     *
     * @return array|null
     *
     * @throws Exception
     */
    public function findEmailEventType(array $eventData): ?array
    {
        if (empty($eventData['EVENT_NAME'])) {
            throw new Exception('Empty email event type name');
        }
        $filter = ['TYPE_ID' => $eventData['EVENT_NAME']];
        if (!empty($eventData['LID'])) {
            $filter['LID'] = $eventData['LID'];
        }

        return CEventType::getList($filter)->fetch();
    }
}
