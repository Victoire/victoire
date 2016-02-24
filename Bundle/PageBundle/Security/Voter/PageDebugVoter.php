<?php

namespace Victoire\Bundle\PageBundle\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * This class decides yes or no if the user is granted to see the debug.
 */
class PageDebugVoter extends Voter
{
    protected $userClass;

    /**
     * Constructor.
     *
     * @param string $userClass
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
        return null != $subject && 'PAGE_DEBUG' === $attribute;
    }

    /**
     * {@inheritdoc}
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        return $token->getUser() instanceof $this->userClass && $token->getUser()->hasRole('ROLE_VICTOIRE');
    }
}
