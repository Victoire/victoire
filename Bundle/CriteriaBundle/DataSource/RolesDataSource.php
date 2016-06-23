<?php

namespace Victoire\Bundle\CriteriaBundle\DataSource;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Role\RoleHierarchy;

class RolesDataSource
{
    private $tokenStorage;
    private $roleHierarchy;

    public function __construct(TokenStorage $tokenStorage, RoleHierarchy $roleHierarchy)
    {
        $this->tokenStorage = $tokenStorage;
        $this->roleHierarchy = $roleHierarchy;
    }

    public function getRoles()
    {
        $user = $this->tokenStorage->getToken()->getUser();

        return $user;
    }

    public function getRolesFormParams()
    {
        return [
            'type' => ChoiceType::class,
            'options' => [
                'choices' => $this->getAllAvailableRoles(),
                'choices_as_values' => true,
                'choice_label' => function($value){
                    return $value;
                }
            ],
        ];
    }

    public function getAllAvailableRoles(){

        $roles = array();

        array_walk_recursive($this->roleHierarchy, function($val) use (&$roles) {
            $roles[] = $val;
        });

        return array_unique($roles);

    }
}
