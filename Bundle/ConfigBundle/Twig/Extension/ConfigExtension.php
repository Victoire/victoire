<?php

namespace Victoire\Bundle\ConfigBundle\Twig\Extension;

use Doctrine\ORM\EntityManager;
use Victoire\Bundle\ConfigBundle\Entity\GlobalConfig;

/**
 * Config extension.
 */
class ConfigExtension extends \Twig_Extension implements \Twig_Extension_GlobalsInterface
{
    protected $entityManager = null;

    /**
     * Constructor.
     *
     * @param EntityManager      $entityManager
     */
    public function __construct(EntityManager $entityManager) {
        $this->entityManager = $entityManager;
    }

    /**
     * Returns a list of global variables to add to the existing list.
     *
     * @return array An array of global variables
     */
    public function getGlobals()
    {
        return [
            'globalConfig' => $this->entityManager->getRepository(GlobalConfig::class)->find(1)
        ];
    }
}