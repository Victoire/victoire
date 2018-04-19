<?php

namespace Victoire\Bundle\ConfigBundle\Validator;

use Symfony\Component\Validator\Constraint;

class SemanticalOrganizationJsonLDValidator extends JsonValidator
{
    public function validate($value, Constraint $constraint)
    {
        if (null !== $value) {
            $jsonLD = parent::validate($value, $constraint);
            if (!preg_match('/https?:\/\/schema\.org/', $jsonLD['@context'])) {
                $this->context->buildViolation('victoire.config.global.organizationJsonLD.invalidContext')
                    ->setParameter('{{ value }}', $value)
                    ->addViolation();
            }
            if (!isset($jsonLD['@type'])) {
                $this->context->buildViolation('victoire.config.global.organizationJsonLD.missingType')
                    ->setParameter('{{ value }}', $value)
                    ->addViolation();
            }
        }
    }
}
