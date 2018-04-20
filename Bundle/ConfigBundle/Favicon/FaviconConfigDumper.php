<?php

namespace Victoire\Bundle\ConfigBundle\Favicon;

use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Response;
use Victoire\Bundle\ConfigBundle\Entity\GlobalConfig;

class FaviconConfigDumper
{
    /**
     * @var \Twig_Environment
     */
    private $environment;
    /**
     * @var string
     */
    private $webDir;

    public function __construct(\Twig_Environment $environment, string $webDir)
    {
        $this->environment = $environment;
        $this->webDir = $webDir;
    }

    /**
     * @param GlobalConfig $globalConfig
     * @param string       $path         The location where dump the json config file
     *
     * @return null|string
     */
    public function dump(GlobalConfig $globalConfig, string $path = 'faviconConfig.json'): ?string
    {
        $fileSystem = new Filesystem();
        $path = rtrim($path, '/');
        if ($fileSystem->exists($path)) {
            $fileSystem->remove($path);
        }
        $fileSystem->touch($path);
        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');

        try {
            $fileSystem->dumpFile(
                $path, $this->environment->render('VictoireConfigBundle:global:faviconConfig.json.twig', [
                'globalConfig' => $globalConfig,
                'webDir'       => $this->webDir,
            ])
            );

            return $path;
        } catch (IOException $e) {
            throw $e;
        }
    }
}
