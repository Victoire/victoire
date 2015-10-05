<?php

namespace Victoire\Bundle\TwigBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class VictoireTwigBundle extends Bundle
{
    /**
     * Get parent bundle.
     *
     * @return string
     **/
    public function getParent()
    {
        return 'TwigBundle';
    }
}
