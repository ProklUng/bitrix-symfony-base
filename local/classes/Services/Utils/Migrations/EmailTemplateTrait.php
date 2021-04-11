<?php

namespace Local\Services\Utils\Migrations;

use CEventMessage;
use Exception;

/**
 * Trait EmailTemplateTrait
 * @package Local\Services\Utils\Migrations
 *
 * @since 11.04.2021
 */
trait EmailTemplateTrait
{
    /**
     * Создает новый шаблон для почтового сообщения.
     *
     * @param array $templateData
     *
     * @return array
     *
     * @throws Exception
     */
    public function createEmailTemplate(array $templateData): array
    {
        if ($this->findEmailTemplate($templateData)) {
            throw new Exception("Email template for event {$templateData['EVENT_NAME']} already exists");
        }

        $et = new CEventMessage;
        $res = $et->add($templateData);
        if (!$res) {
            throw new Exception("Can't create email event type {$templateData['EVENT_NAME']}: {$et->LAST_ERROR}");
        }

        return ["Email template({$res}) for event {$templateData['EVENT_NAME']} created"];
    }

    /**
     * Обновляет шаблон почтового сообщения.
     *
     * @param array $templateData
     * @param array $search
     *
     * @return array
     *
     * @throws Exception
     */
    public function updateEmailTemplate(array $search, array $templateData): array
    {
        if (!($template = $this->findEmailTemplate($search))) {
            throw new Exception("Email template for event {$templateData['EVENT_NAME']} not found");
        }

        $et = new CEventMessage;
        unset($templateData['EVENT_NAME'], $templateData['LID']);
        $res = $et->update($template['ID'], $templateData);
        if (!$res) {
            throw new Exception("Can't update template for event {$templateData['EVENT_NAME']}: {$et->LAST_ERROR}");
        }

        return ["Email template({$template['ID']}) for event {$template['EVENT_NAME']} updated"];
    }

    /**
     * Удаляет шаблон почтового сообщения.
     *
     * @param array $templateData
     *
     * @return array
     * @throws Exception
     */
    public function deleteEmailTemplate(array $templateData): array
    {
        if (!($template = $this->findEmailTemplate($templateData))) {
            throw new Exception("Email template for event {$templateData['EVENT_NAME']} not found");
        }

        $et = new CEventMessage;
        $res = $et->delete($template['ID']);

        if (!$res) {
            throw new Exception("Can't delete template({$template['ID']}) for type {$templateData['EVENT_NAME']}");
        }

        return ["Email template({$template['ID']}) for type {$templateData['EVENT_NAME']} deleted"];
    }

    /**
     * Ищет шаблон почтового сообщения по массиву параметров.
     *
     * @param array $templateData
     *
     * @return array|null
     *
     * @throws Exception
     */
    public function findEmailTemplate(array $templateData): ?array
    {
        if (empty($templateData['EVENT_NAME'])) {
            throw new Exception('Empty email event type name');
        }

        $filter = ['TYPE_ID' => $templateData['EVENT_NAME']];
        if (!empty($templateData['LID'])) {
            $filter['SITE_ID'] = $templateData['LID'];
        }
        if (!empty($templateData['SUBJECT'])) {
            $filter['SUBJECT'] = $templateData['SUBJECT'];
        }

        $return = null;
        $rsMess = CEventMessage::GetList(
            ($by = 'site_id'),
            ($order = 'desc'),
            $filter
        );
        while ($template = $rsMess->fetch()) {
            if ($return) {
                throw new Exception("More than one template are found: {$templateData['EVENT_NAME']}");
            }
            $return = $template;
        }

        return $return;
    }
}
