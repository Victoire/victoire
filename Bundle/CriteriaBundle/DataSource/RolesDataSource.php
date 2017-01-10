<?php

namespace Victoire\Bundle\CriteriaBundle\DataSource;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

class RolesDataSource
{
    /**
     * @var TokenStorage
     */
    private $tokenStorage;

    /*
     * @var array
     */
    private $roleHierarchy;

    /**
     * RolesDataSource constructor.
     *
     * @param TokenStorage $tokenStorage
     * @param array        $roleHierarchy
     */
    public function __construct(TokenStorage $tokenStorage, $roleHierarchy)
    {
        $this->tokenStorage = $tokenStorage;
        $this->roleHierarchy = $roleHierarchy;
    }

    /**
     * @return mixed
     */
    public function getRoles()
    {
        $user = $this->tokenStorage->getToken()->getUser();

        return $user;
    }

    /**
     * @return array
     */
    public function getRolesFormParams()
    {
        return [
            'type'    => ChoiceType::class,
            'options' => [
                'choices'           => $this->getAllAvailableRoles($this->roleHierarchy),
                'choices_as_values' => true,
                'choice_label'      => function ($value) {
                    return $value;
                },
            ],
        ];
    }

    /**
     * flatten the array of all roles defined in role_hierarchy.
     *
     * @param array $roleHierarchy
     *
     * @return array
     */
    public function getAllAvailableRoles($roleHierarchy)
    {
        $roles = [];
        foreach ($roleHierarchy as $key => $value) {
            if (is_array($value)) {
                $roles = array_merge($roles, $this->getAllAvailableRoles($value));
            }
            if (is_string($key)) {
                $roles[] = $key;
            }
            if (is_string($value)) {
                $roles[] = $value;
            }
        }

        return $roles;
    }
}
