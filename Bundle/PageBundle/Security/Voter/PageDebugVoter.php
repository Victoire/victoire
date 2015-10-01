<?php

namespace Victoire\Bundle\PageBundle\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

/**
 * This class decides yes or no if the user is granted to see the debug.
 */
class PageDebugVoter implements VoterInterface
{
    protected $userClass;

    /**
     * Constructor.
     *
     * @param unknown $userClass
     */
    public function __construct($userClass)
    {
        $this->userClass = $userClass;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsAttribute($attribute)
    {
        return 'PAGE_DEBUG' === $attribute;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsClass($class)
    {
        return null != $class;
    }

    /**
     * {@inheritdoc}
     */
    public function vote(TokenInterface $token, $view, array $attributes)
    {
        foreach ($attributes as $attribute) {
            if ($this->supportsAttribute($attribute) && $this->supportsClass($view)) {
                $userClass = $this->userClass;

                if ($token->getUser() instanceof $userClass
                    &&
                    $token->getUser()->hasRole('ROLE_VICTOIRE')
                    ) {
                    return VoterInterface::ACCESS_GRANTED;
                }
            }
        }

        return VoterInterface::ACCESS_DENIED;
    }
}
