<?php

namespace Victoire\Bundle\CriteriaBundle\DataSource;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;

class IsLoggedDataSource
{
    /**
     * @var AuthorizationChecker
     */
    private $authorizationChecker;

    /**
     * IsLoggedDataSource constructor.
     *
     * @param AuthorizationChecker $authorizationChecker
     */
    public function __construct(AuthorizationChecker $authorizationChecker)
    {
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * Check actual status of current user return true if logged false if not.
     *
     * @return bool
     */
    public function getLoggedStatus()
    {
        if ($this->authorizationChecker->isGranted('IS_AUTHENTICATED_FULLY')) {
            return true;
        } elseif ($this->authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            return true;
        }

        return false;
    }

    /**
     * @return array
     */
    public function getLoggedStatusFormParams()
    {
        return [
            'type'    => ChoiceType::class,
            'options' => [
                'choices' => [
                    true  => 'victoire_criteria.request_user.is.connected.criteria.label',
                    false => 'victoire_criteria.request_user.is.not.connected.criteria.label',
                ],
                'multiple' => false,
            ],
        ];
    }
}
