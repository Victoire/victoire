<?php
namespace Victoire\Bundle\PageBundle\Security\Voter;

use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

use Victoire\Bundle\PageBundle\Entity\Page;

/**
 * This class decides yes or no if the user is granted to do some action on a given page
 */
class PageOwnerVoter implements VoterInterface
{

    private $userClass;

    public function __construct($userClass)
    {
        $this->userClass = $userClass;
    }

    /**
     * {@inheritDoc}
     */
    public function supportsAttribute($attribute)
    {
        return 'PAGE_OWNER' === $attribute;
    }

    /**
     * {@inheritDoc}
     */
    public function supportsClass($page)
    {
        return $page instanceof Page;
    }

    /**
     * {@inheritDoc}
     */
    public function vote(TokenInterface $token, $page, array $attributes)
    {
        foreach ($attributes as $attribute) {
            if ($this->supportsAttribute($attribute) && $this->supportsClass($page)) {
                $userClass = $this->userClass;
                if ($token->getUser() instanceof $userClass
                    &&
                    $token->getUser()->hasRole('ROLE_VICTOIRE')
                    || $page->getAuthor() === $token->getUser()
                    ) {
                    return VoterInterface::ACCESS_GRANTED;
                }
            }
        }

        return VoterInterface::ACCESS_DENIED;
    }
}
