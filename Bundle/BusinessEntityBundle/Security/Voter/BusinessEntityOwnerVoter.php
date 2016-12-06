<?php

namespace Victoire\Bundle\BusinessEntityBundle\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * This class decides yes or no if the user is granted to do some action on a given entity.
 */
class BusinessEntityOwnerVoter extends Voter
{
    private $userClass;

    /**
     * @param $userClass
     */
    public function __construct($userClass)
    {
        $this->userClass = $userClass;
    }

    /**
     * {@inheritdoc}
     */
    protected function supports($attribute, $subject)
    {
        return method_exists($subject, 'getAuthor')
            && 'BUSINESS_ENTITY_OWNER' === $attribute;
    }

    /**
     * {@inheritdoc}
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        return $token->getUser() instanceof $this->userClass
            && ($token->getUser()->hasRole('ROLE_VICTOIRE')
                || $token->getUser()->hasRole('ROLE_VICTOIRE_DEVELOPER')
                || $subject->getAuthor() === $token->getUser()
            );
    }
}
