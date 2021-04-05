Symfony RequestValidatorBundle
==============================

# Usage

```php
<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Validator\Constraints as Assert;
use Local\Bundles\RequestValidatorBundle\Validator\RequestValidator;
use Local\Bundles\RequestValidatorBundle\Annotation\Validator;

class AcmeController extends Controller
{
    /**
     * @Validator(name="page", default="1", constraints={@Assert\Type(type="numeric"), @Assert\Range(min=1)})
     * @Validator(name="limit", default="25", constraints={@Assert\Type(type="numeric"), @Assert\Range(min=10, max=100)})
     * @Validator(name="order", default="desc", constraints={@Assert\Choice(choices={"asc", "desc"}, message="error.wrong_order_choice")})
     * @Validator(name="name", constraints={@Assert\NotBlank()})
     * @Validator(name="email", required=true, constraints={@Assert\Email()})
     *
     * @param RequestValidator $requestValidator
     */
    public function someAction(RequestValidator $requestValidator)
    {
        // You can get errors if there is any
        /** @var \Symfony\Component\Validator\ConstraintViolationList $errors */
        $errors = $requestValidator->getErrors();
        
        // You can get the request value with `get($path)` method
        $email = $requestValidator->get('email');
         
        // ...
    }
}


