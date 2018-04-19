<?php

namespace Victoire\Bundle\ConfigBundle\Favicon;

use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Response;
use Victoire\Bundle\ConfigBundle\Entity\GlobalConfig;

class GenerateFaviconException extends \Exception
{
}
