<?php

namespace Victoire\Bundle\PageBundle\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Victoire\Bundle\PageBundle\Entity\Page;

/**
 * This class decides yes or no if the user is granted to do some action on a given page.
 */
class PageOwnerVoter extends Voter
{
    private $userClass;

    /**
     * PageOwnerVoter constructor.
     *
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
        return $subject instanceof Page && 'PAGE_OWNER' === $attribute;
    }

    /**
     * {@inheritdoc}
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        return $token->getUser() instanceof $this->userClass
            && (
                $token->getUser()->hasRole('ROLE_VICTOIRE')
             || $token->getUser()->hasRole('ROLE_VICTOIRE_DEVELOPER')
             || $subject->getAuthor() === $token->getUser()
            );
    }
}
