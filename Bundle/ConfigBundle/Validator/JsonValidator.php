<?php

namespace Victoire\Bundle\ConfigBundle\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class JsonValidator extends ConstraintValidator
{
    /**
     * @param mixed      $value
     * @param Constraint $constraint
     *
     * @throws \Exception
     */
    public function validate($value, Constraint $constraint)
    {
        try {
            if (null !== $value) {
                $array = json_decode($value, true);
                if (!\is_array($array)) {
                    throw new \Exception();
                }

                return $array;
            }
        } catch (\Exception $e) {
            $this->context->buildViolation('victoire.config.json.invalid')
                ->setParameter('{{ value }}', $value)
                ->addViolation();
        }
    }
}
