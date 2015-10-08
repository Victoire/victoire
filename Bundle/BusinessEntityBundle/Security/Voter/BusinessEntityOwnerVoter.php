<?php

namespace Victoire\Bundle\BusinessEntityBundle\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Victoire\Bundle\BusinessEntityBundle\Helper\BusinessEntityHelper;

/**
 * This class decides yes or no if the user is granted to do some action on a given entity.
 */
class BusinessEntityOwnerVoter implements VoterInterface
{
    private $userClass;
    private $businessEntityHelper;

    /**
     * @param $userClass
     * @param BusinessEntityHelper $businessEntityHelper
     */
    public function __construct($userClass, BusinessEntityHelper $businessEntityHelper)
    {
        $this->userClass = $userClass;
        $this->businessEntityHelper = $businessEntityHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsAttribute($attribute)
    {
        return 'BUSINESS_ENTITY_OWNER' === $attribute;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsClass($entity)
    {
        return method_exists($entity, 'getAuthor') && $this->businessEntityHelper->findByEntityInstance($entity);
    }

    /**
     * {@inheritdoc}
     */
    public function vote(TokenInterface $token, $entity, array $attributes)
    {
        foreach ($attributes as $attribute) {
            if ($this->supportsAttribute($attribute) && $this->supportsClass($entity)) {
                $userClass = $this->userClass;
                if ($token->getUser() instanceof $userClass
                    &&
                    $token->getUser()->hasRole('ROLE_VICTOIRE')
                    || $entity->getAuthor() === $token->getUser()
                ) {
                    return VoterInterface::ACCESS_GRANTED;
                }
            }
        }

        return VoterInterface::ACCESS_DENIED;
    }
}
