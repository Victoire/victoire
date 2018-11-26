<?php

namespace Victoire\Bundle\SeoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class Error404.
 *
 * @ORM\Entity()
 */
class Error404 extends HttpError
{
    public function __construct($errorMessage = 'Page not found')
    {
        parent::__construct($errorMessage);
    }
}
