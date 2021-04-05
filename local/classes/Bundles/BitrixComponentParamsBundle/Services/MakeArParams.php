<?php

namespace Local\Bundles\BitrixComponentParamsBundle\Services;

use Local\Bundles\BitrixComponentParamsBundle\Services\Contracts\BitrixParameterInterface;
use Spatie\DataTransferObject\DataTransferObject;

/**
 * Class MakeArParams
 * @package Local\Bundles\BitrixComponentParamsBundle\Services
 *
 * @since 26.02.2021
 */
class MakeArParams
{
    /**
     * @var BitrixParameterInterface $dto DTO.
     */
    private $dto;

    /**
     * MakeArParams constructor.
     *
     * @param BitrixParameterInterface $dto DTO.
     */
    public function __construct(
        BitrixParameterInterface $dto
    ) {
        $this->dto = $dto;
    }

    /**
     * @param array $arParams Недостающие битриксовые параметры.
     *
     * @return array
     */
    public function make(array $arParams): array
    {
        $dtoClass = get_class($this->dto);
        /** @var DataTransferObject $dto */
        $dto = new $dtoClass($arParams);

        return $dto->toArray();
    }
}
