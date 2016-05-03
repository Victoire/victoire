<?php

namespace Victoire\Bundle\CriteriaBundle\DataSource;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\HttpFoundation\RequestStack;

class RequestDataSource
{
    /**
     * @var RequestStack
     */
    private $requestStack;
    private $availableLocales;

    /**
     * RequestCriteria constructor.
     *
     * @param RequestStack $requestStack
     * @param              $availableLocales
     */
    public function __construct(RequestStack $requestStack, $availableLocales)
    {
        $this->requestStack = $requestStack;
        $this->availableLocales = $availableLocales;
    }

    public function getLocale()
    {
        return $this->requestStack->getCurrentRequest()->getLocale();
    }

    public function getScheme()
    {
        return $this->requestStack->getCurrentRequest()->getScheme();
    }

    public function getLocaleFormParams()
    {
        return [
            'type'    => ChoiceType::class,
            'options' => [
                'choices' => $this->availableLocales,
            ],
        ];
    }

    public function getSchemeFormParams()
    {
        return [
            'type'    => ChoiceType::class,
            'options' => [
                'choices' => [
                    'http'  => 'http',
                    'https' => 'https',
                ],
            ],
        ];
    }
}
