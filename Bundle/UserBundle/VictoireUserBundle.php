<?php

namespace Victoire\Bundle\UserBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class VictoireUserBundle extends Bundle
{
    /**
     * Who's your daddy.
     *
     * @return string Your father bundle's name
     */
    public function getParent()
    {
        return 'FOSUserBundle';
    }
}
