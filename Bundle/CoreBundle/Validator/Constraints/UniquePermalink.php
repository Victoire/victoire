<?php

namespace Victoire\Bundle\CoreBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Victoire\Bundle\CoreBundle\Validator\UniquePermalinkValidator;

/**
 * @Annotation
 */
class UniquePermalink extends Constraint
{
    public function validatedBy()
    {
        return UniquePermalinkValidator::class;
    }

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
