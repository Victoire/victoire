<?php

namespace Victoire\Bundle\CoreBundle\Validator;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Victoire\Bundle\ViewReferenceBundle\Connector\ViewReferenceRepository;

class UniquePermalinkValidator extends ConstraintValidator
{
    private $viewReferenceRepository;
    private $requestStack;

    /**
     * PermalinkValidator constructor.
     * @param ViewReferenceRepository $viewReferenceRepository
     * @param RequestStack $requestStack
     */
    public function __construct(ViewReferenceRepository $viewReferenceRepository, RequestStack $requestStack)
    {
        $this->viewReferenceRepository = $viewReferenceRepository;
        $this->requestStack = $requestStack;
    }

    /**
     * @param View $view
     * @param Constraint $constraint
     *
     */
    public function validate($view, Constraint $constraint)
    {
        if ($view->getPermalink() != '' && $view->getPermalink() != null) {
            $viewReference = $this->viewReferenceRepository->getReferenceByUrl(
                $view->getPermalink(),
                $this->requestStack->getCurrentRequest()->getLocale()
            );

            if ($viewReference && $viewReference->getViewId() != $view->getId()) {
                $this->context->buildViolation('victoire.permalink.invalid')
                    ->atPath('permalink')
                    ->addViolation();
            }
        }
    }
}
