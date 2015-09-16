<?php

namespace Victoire\Bundle\BusinessPageBundle\Chain;

use Symfony\Component\HttpFoundation\Request;
use Victoire\Bundle\BusinessPageBundle\Entity\BusinessTemplate;

/**
 * BusinessTemplate chain
 */
class BusinessTemplateChain
{
    private $patterns;

    public function __construct()
    {
        $this->patterns = array();
    }

    /**
     * @param BusinessTemplate $pattern
     * @param $alias
     */
    public function addBusinessTemplate(BusinessTemplate $pattern, $alias)
    {
        $this->patterns[$alias] = $pattern;
    }

    /**
     * @param string $alias
     *
     * @return BusinessTemplate
     */
    public function getBusinessTemplate($alias)
    {
        if (array_key_exists($alias, $this->patterns)) {
            return $this->patterns[$alias];
        }

        return $this->patterns['default'];
    }
    /**
     *
     * @return array
     */
    public function getBusinessTemplates()
    {
        return $this->patterns;
    }
}
