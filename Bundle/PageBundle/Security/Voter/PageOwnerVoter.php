<?php

namespace Victoire\Bundle\PageBundle\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Victoire\Bundle\PageBundle\Entity\Page;

/**
 * This class decides yes or no if the user is granted to do some action on a given page.
 */
class PageOwnerVoter implements VoterInterface
{
    private $userClass;

    public function __construct($userClass)
    {
        $this->userClass = $userClass;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsAttribute($attribute)
    {
        return 'PAGE_OWNER' === $attribute;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsClass($page)
    {
        return $page instanceof Page;
    }

    /**
     * {@inheritdoc}
     */
    public function vote(TokenInterface $token, $page, array $attributes)
    {
        foreach ($attributes as $attribute) {
            if ($this->supportsAttribute($attribute) && $this->supportsClass($page)) {
                $userClass = $this->userClass;
                if ($token->getUser() instanceof $userClass && (
                        $token->getUser()->hasRole('ROLE_VICTOIRE') || $token->getUser()->hasRole('ROLE_VICTOIRE_DEVELOPER')
                    ) || $page->getAuthor() === $token->getUser()
                    ) {
                    return VoterInterface::ACCESS_GRANTED;
                }
            }
        }

        return VoterInterface::ACCESS_DENIED;
    }
}
