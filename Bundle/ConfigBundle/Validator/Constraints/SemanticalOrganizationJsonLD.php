<?php

namespace Victoire\Bundle\ConfigBundle\Validator\Constraints;

use Victoire\Bundle\ConfigBundle\Validator\SemanticalOrganizationJsonLDValidator;

/**
 * @Annotation
 */
class SemanticalOrganizationJsonLD extends Json
{
    public $message = 'victoire.config.global.organizationJsonLD.invalid';

    public function validatedBy()
    {
        return SemanticalOrganizationJsonLDValidator::class;
    }
}
