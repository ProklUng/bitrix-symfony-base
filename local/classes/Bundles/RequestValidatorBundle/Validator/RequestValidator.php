<?php

namespace Local\Bundles\RequestValidatorBundle\Validator;

use Local\Bundles\RequestValidatorBundle\Annotation\Validator;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class RequestValidator.
 */
class RequestValidator implements RequestValidatorInterface
{
    /**
     * @var Validator[] $annotations Аннотации.
     */
    private $annotations;

    /**
     * @var ValidatorInterface $validator Валидатор.
     */
    private $validator;

    /**
     * @var Request $request Request.
     */
    private $request;

    /**
     * RequestValidator constructor.
     *
     * @param Request            $request     Request.
     * @param ValidatorInterface $validator   Валидатор.
     * @param array              $annotations Аннотации.
     */
    public function __construct(Request $request, ValidatorInterface $validator, array $annotations = [])
    {
        $this->request = $request;
        $this->validator = $validator;
        $this->annotations = $annotations;
    }

    /**
     * @inheritDoc
     */
    public function getErrors() : ConstraintViolationList
    {
        $errors = new ConstraintViolationList();

        $allFields = $this->getAll(false, false);

        foreach ($this->annotations as $annotation) {
            $requestValue = $this->getParameterBag()->get($annotation->getName());

            // Add NotNull for required empty params
            if (!$this->getParameterBag()->get($annotation->getName()) && $annotation->isRequired()) {
                $annotation->addConstraint(
                    new Assert\NotNull(null, 'Value of field ' . $annotation->getName() . ' should not be null.')
                );
            }

            // Adjust Symfony constraints to request validator
            foreach ($annotation->getConstraints() as $key => $constraint) {
                // Conditional constraints
                if (isset($constraint->payload['when'])) {
                    $language = new ExpressionLanguage();
                    $condition = $language->evaluate($constraint->payload['when'], $allFields);

                    if (!$condition) {
                        $annotation->removeConstraint($key);
                        continue;
                    }
                }
                // Skip not required and empty params
                elseif (!$this->getParameterBag()->has($annotation->getName()) && $annotation->isOptional()) {
                    $annotation->removeConstraint($key);
                    continue;
                }

                // Fix for All constraint
                if ($constraint instanceof Assert\All) {
                    if ($requestValue === null) {
                        $error = $this->validator->validate(
                            null,
                            new Assert\NotNull()
                        )->get(0);
                    } elseif (!is_array($requestValue)) {
                        $error = $this->validator->validate($requestValue, new Assert\Type(['type' => 'array']))->get(0);
                    } else {
                        continue;
                    }

                    $errors->set($annotation->getName(), $error);

                    continue 2;
                }

                // Fix for Type=boolean
                if ($constraint instanceof Assert\Type && 'boolean' === $constraint->type && $this->isBoolean($requestValue)) {
                    $requestValue = filter_var($requestValue, FILTER_VALIDATE_BOOLEAN);
                }
            }

            // Validate the value with all the constraints defined
            $violationList = $this->validator->validate($requestValue, $annotation->getConstraints());
            foreach ($violationList as $key => $violation) {
                $errors->set($key, $violation);
            }
        }

        return $errors;
    }

    /**
     * @inheritDoc
     */
    public function get($path)
    {
        $annotation = $this->getAnnotation($path);

        if (!$annotation) {
            return null;
        }

        if (!$this->getParameterBag()->has($path)) {
            return $annotation->getDefault();
        }

        $requestValue = $this->getParameterBag()->get($path);

        foreach ($annotation->getConstraints() as $constraint) {
            if ($constraint instanceof Assert\Type && 'boolean' === $constraint->type && $this->isBoolean($requestValue)) {
                return filter_var($requestValue, FILTER_VALIDATE_BOOLEAN);
            }
        }

        return $requestValue;
    }

    /**
     * @inheritDoc
     */
    public function getAll(bool $validate = true, bool $skipMissing = true) : array
    {
        $all = [];

        foreach ($this->annotations as $annotation) {
            if (!$this->getParameterBag()->has($annotation->getName())) {
                if ($annotation->isRequired() || $annotation->getDefault() || is_array($annotation->getDefault())) {
                    $all[$annotation->getName()] = $annotation->getDefault();
                }

                if ($skipMissing) {
                    continue;
                }
            }

            $requestValue = $this->get($annotation->getName());

            if ($validate) {
                $violationList = $this->validator->validate($requestValue, $annotation->getConstraints());
                $all[$annotation->getName()] = count($violationList)
                    ? $annotation->getDefault()
                    : $requestValue;
            } else {
                $all[$annotation->getName()] = $requestValue;
            }
        }

        return $all;
    }

    /**
     * @inheritDoc
     */
    public function getRequest() : Request
    {
        return $this->request;
    }

    /**
     * @return ParameterBag
     */
    private function getParameterBag() : ParameterBag
    {
        if ($this->request->getMethod() === 'GET') {
            return $this->request->query;
        }

        return $this->request->request;
    }

    /**
     * @param string $path
     *
     * @return Validator|null
     */
    private function getAnnotation(string $path): ?Validator
    {
        return $this->annotations[$path] ?? null;
    }

    /**
     * On boolean type request values with 0 and 1 should be considered as false and true respectively.
     *
     * @param mixed $s
     *
     * @return boolean
     */
    private function isBoolean($s): bool
    {
        if (filter_var($s, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) === null) {
            return false;
        }

        return true;
    }
}
