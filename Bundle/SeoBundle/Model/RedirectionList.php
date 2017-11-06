<?php

namespace Victoire\Bundle\SeoBundle\Model;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Victoire\Bundle\SeoBundle\Entity\Redirection;

/**
 * Class RedirectionList.
 *
 * @ORM\Entity()
 */
class RedirectionList
{
    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Victoire\Bundle\SeoBundle\Entity\Redirection")
     */
    private $redirections;

    /**
     * RedirectionList constructor.
     */
    public function __construct()
    {
        $this->redirections = new ArrayCollection();
    }

    /**
     * @param Redirection $redirection
     */
    public function addRedirection(Redirection $redirection)
    {
        $this->redirections[$redirection->getId()] = $redirection;
    }

    /**
     * @param Redirection $redirection
     */
    public function removeRedirection(Redirection $redirection)
    {
        $this->redirections->remove($redirection);
    }

    /**
     * @return ArrayCollection
     */
    public function getRedirections()
    {
        return $this->redirections;
    }

    /**
     * @param ArrayCollection $redirections
     *
     * @return $this
     */
    public function setRedirections($redirections)
    {
        $this->redirections = $redirections;

        return $this;
    }
}