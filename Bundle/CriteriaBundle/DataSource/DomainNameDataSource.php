<?php

namespace Victoire\Bundle\CriteriaBundle\DataSource;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class DomainNameDataSource
 * @package Victoire\Bundle\CriteriaBundle\DataSource
 */
class DomainNameDataSource
{
    /**
     * @var array
     */
    private $domainName;
    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * DomainNameDataSource constructor.
     * @param array $domainName
     * @param RequestStack $requestStack
     */
    public function __construct(
        array $domainName,
        RequestStack $requestStack
    )
    {
        $this->domainName = $domainName;
        $this->requestStack = $requestStack;
    }

    /**
     * @return string
     */
    public function getCurrentDomainName()
    {
        return $this->requestStack->getCurrentRequest()->getHost();
    }

    /**
     * @return array
     */
    public function getDomainName()
    {
        return $this->domainName[0];
    }

    /**
     * @return array
     */
    public function getCurrentDomainNameFormParams()
    {
        return [
            'type' => ChoiceType::class,
            'options' => [
                'choices' => $this->getDomainName(),
                'choices_as_values' => true,
                'choice_label'      => function ($value) {
                    return $value;
                },
            ]
        ];
    }
}