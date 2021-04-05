<?php

namespace Local\Bundles\RequestValidatorBundle\EventListener;

use Doctrine\Common\Annotations\Reader;
use Local\Bundles\RequestValidatorBundle\Annotation\Validator;
use Local\Bundles\RequestValidatorBundle\Validator\RequestValidator;
use Local\Bundles\RequestValidatorBundle\Validator\RequestValidatorInterface;
use ReflectionException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class ValidatorAnnotationListener.
 *
 */
final class ValidatorAnnotationListener
{
    /**
     * @var Reader $reader Читатель аннотаций.
     */
    private $reader;

    /**
     * @var ValidatorInterface $validator Валидатор.
     */
    private $validator;

    /**
     * ValidatorAnnotationListener constructor.
     *
     * @param Reader             $reader    Читатель аннотаций.
     * @param ValidatorInterface $validator Валидатор.
     */
    public function __construct(Reader $reader, ValidatorInterface $validator)
    {
        $this->reader = $reader;
        $this->validator = $validator;
    }

    /**
     * @param ControllerEvent $event Событие.
     *
     * @return void
     * @throws ReflectionException Ошибки рефлексии.
     */
    public function onKernelController(ControllerEvent $event): void
    {
        if (!is_array($controller = $event->getController())) {
            return;
        }

        $request = $event->getRequest();

        $object = new \ReflectionObject($controller[0]);
        $method = $object->getMethod($controller[1]);

        $annotations = $this->reader->getMethodAnnotations($method);

        // Filter out Validator annotations
        $annotations = array_filter($annotations, static function ($annotation) {
            return (bool) ($annotation instanceof Validator);
        });

        $validators = [];
        array_walk($annotations, static function ($value) use (&$validators) {
            /* @var Validator $value */
            $validators[$value->getName()] = $value;
        });

        $request->attributes->set('requestValidator', $this->createRequestValidator($request, $validators));
    }

    /**
     * @param Request $request    Request.
     * @param array   $validators Валидаторы.
     *
     * @return RequestValidatorInterface
     */
    protected function createRequestValidator(Request $request, array $validators) : RequestValidatorInterface
    {
        return new RequestValidator($request, $this->validator, $validators);
    }
}
