<?php

namespace Victoire\Bundle\CriteriaBundle\Criteria;


use Symfony\Component\HttpFoundation\RequestStack;

class RequestCriteria
{
    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * RequestCriteria constructor.
     *
     * @param RequestStack $requestStack
     */
    public function __construct(RequestStack $requestStack)
    {

        $this->requestStack = $requestStack;
    }

    public function getLocale()
    {
        return $this->requestStack->getCurrentRequest()->getLocale();
    }

    public function isHttps()
    {
        return $this->requestStack->getCurrentRequest()->getScheme();
    }

}
