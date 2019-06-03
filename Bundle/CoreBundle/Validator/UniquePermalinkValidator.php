<?php

namespace Victoire\Bundle\CoreBundle\Validator;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Victoire\Bundle\CoreBundle\Entity\WebViewInterface;
use Victoire\Bundle\CoreBundle\Helper\UrlBuilder;
use Victoire\Bundle\ViewReferenceBundle\Connector\ViewReferenceRepository;

class UniquePermalinkValidator extends ConstraintValidator
{
    private $viewReferenceRepository;
    private $requestStack;
    private $urlBuilder;

    /**
     * UniquePermalinkValidator constructor.
     *
     * @param ViewReferenceRepository $viewReferenceRepository
     * @param RequestStack            $requestStack
     * @param UrlBuilder              $urlBuilder
     */
    public function __construct(ViewReferenceRepository $viewReferenceRepository, RequestStack $requestStack, UrlBuilder $urlBuilder)
    {
        $this->viewReferenceRepository = $viewReferenceRepository;
        $this->requestStack = $requestStack;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * @param View       $view
     * @param Constraint $constraint
     */
    public function validate($view, Constraint $constraint)
    {
        if ($view instanceof WebViewInterface && ($url = $this->urlBuilder->buildUrl($view)) != '') {
            $viewReference = $this->viewReferenceRepository->getReferenceByUrl(
                $url,
                $this->requestStack->getCurrentRequest()->getLocale()
            );

            if ($viewReference && $viewReference->getViewId() != $view->getId()) {
                $this->context->buildViolation('victoire.url.alreadyInUse')
                    ->atPath('permalink')
                    ->addViolation();
            }
        }
    }
}
