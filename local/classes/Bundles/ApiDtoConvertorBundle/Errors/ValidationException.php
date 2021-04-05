<?php

declare(strict_types=1);

namespace Local\Bundles\ApiDtoConvertorBundle\Errors;

use RuntimeException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * Class ValidationException
 * @package Local\Bundles\ApiDtoConvertorBundle\Error
 *
 * @since 04.11.2020
 */
class ValidationException extends RuntimeException
{
    /**
     * @var ConstraintViolationListInterface $violationList
     */
    private $violationList;

    private function __construct(ConstraintViolationListInterface $violationList)
    {
        $this->violationList = $violationList;
        parent::__construct();
    }

    public static function fromViolationList(ConstraintViolationListInterface $violationList): self
    {
        return new self($violationList);
    }

    public function getViolationList(): ConstraintViolationListInterface
    {
        return $this->violationList;
    }

    public function getStatusCode(): int
    {
        return Response::HTTP_BAD_REQUEST;
    }
}
