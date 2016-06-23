<?php

namespace Victoire\Bundle\CriteriaBundle\DataSource;


use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;

class IsLoggedDataSource
{
    private $authorizationChecker;
    
    public function __construct(AuthorizationChecker $authorizationChecker)
    {
        $this->authorizationChecker = $authorizationChecker;
    }
    
    public function getLoggedStatus(){

        if($this->authorizationChecker->isGranted('IS_AUTHENTICATED_FULLY')){
            return true;
        }elseif($this->authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED')){
            return true;
        }

        return false;
    }

    public function getLoggedStatusFormParams(){

        return [
            'type' => ChoiceType::class,
            'options' => [
                'choices' => [
                    true => 'victoire_criteria.request_user.is.connected.criteria.label',
                    false => 'victoire_criteria.request_user.is..not.connected.criteria.label'
                ],
                'expanded' => true,
                'multiple' => false
            ]
        ];
    }
}