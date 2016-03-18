<?php

namespace Victoire\Bundle\CriteriaBundle\DataSource;


use Symfony\Component\HttpFoundation\RequestStack;

class RequestDataSource
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

    public function getScheme()
    {
        return $this->requestStack->getCurrentRequest()->getScheme();
    }
}
