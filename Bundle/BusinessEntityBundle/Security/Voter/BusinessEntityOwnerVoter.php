<?php

namespace Victoire\Bundle\BusinessEntityBundle\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Victoire\Bundle\BusinessEntityBundle\Helper\BusinessEntityHelper;

/**
 * This class decides yes or no if the user is granted to do some action on a given entity.
 */
class BusinessEntityOwnerVoter extends Voter
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
