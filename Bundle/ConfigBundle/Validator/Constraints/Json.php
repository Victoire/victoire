<?php

namespace Victoire\Bundle\ConfigBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Victoire\Bundle\ConfigBundle\Validator\JsonValidator;

/**
 * @Annotation
 */
class Json extends Constraint
{
    public function validatedBy()
    {
        return JsonValidator::class;
    }
}
