<?php

namespace Local\Services;

/**
 * Class TimestampNews
 * @package Local\Services
 */
class TimestampNews
{
    /**
     * @var array $arTimeStamps Таймстампы элементов новостей.
     */
    private $arTimestamps;

    /**
     * @var array $arData
     */
    private $arData = [];

    /**
     * Получить свежайший timestamp.
     *
     * @return string
     */
    public function getNewestTimestamp() : string
    {
        $this->arTimestamps = $this->process($this->arData);

        return $this->arTimestamps[0] ?? '';
    }

    /**
     * @param array $arResultItems Битриксовый $arResult['ITEMS'].
     *
     * @return $this
     */
    public function setTimestamps(array $arResultItems): self
    {
        $this->arData = $arResultItems;

        return $this;
    }

    /**
     * @param array $arResultItems Битриксовый $arResult['ITEMS'].
     *
     * @return array
     */
    protected function process(array $arResultItems = []) : array
    {
        if (count($arResultItems) === 0) {
            return [];
        }

        $this->arTimestamps = collect($arResultItems)->pluck('TIMESTAMP_X')->toArray();
        usort($this->arTimestamps, function ($a, $b) {
            if (strtotime($a) < strtotime($b)) {
                return 1;
            } else {
                if (strtotime($a) > strtotime($b)) {
                    return -1;
                }

                return 0;
            }
        });

        return $this->arTimestamps;
    }
}