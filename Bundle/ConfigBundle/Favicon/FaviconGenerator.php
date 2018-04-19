<?php

namespace Victoire\Bundle\ConfigBundle\Favicon;

use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use Victoire\Bundle\ConfigBundle\Entity\GlobalConfig;

class FaviconGenerator
{
    /**
     * @var FaviconConfigDumper
     */
    private $faviconConfigDumper;
    /**
     * @var string
     */
    public $nodePath;
    /**
     * @var string
     */
    private $realFaviconPath;
    /**
     * @var string
     */
    public $target;

    /**
     * FaviconGenerator constructor.
     *
     * @param FaviconConfigDumper $faviconConfigDumper
     * @param string              $nodePath
     * @param string              $realFaviconPath
     * @param string              $target
     * @param string              $cwd                 current working directory for process
     */
    public function __construct(FaviconConfigDumper $faviconConfigDumper, string $nodePath, string $realFaviconPath, string $target, string $cwd)
    {
        $this->nodePath = $nodePath;
        $this->faviconConfigDumper = $faviconConfigDumper;
        $this->realFaviconPath = $realFaviconPath;
        $this->target = $target;
        $this->cwd = $cwd;
    }

    /**
     * Generate favicons from realfavicon generator and a GlobalConfig object.
     *
     * @return array The generated files
     */
    public function generate(GlobalConfig $globalConfig, $path = 'faviconConfig.json')
    {
        return $this->generateFromConfigFile($this->faviconConfigDumper->dump($globalConfig, $path));
    }

    /**
     * Generate favicons from a config file.
     *
     * @return array The generated files
     */
    public function generateFromConfigFile(string $configPath)
    {
        try {
            $fileSystem = new Filesystem();
            if (!$fileSystem->exists($configPath)) {
                throw new FileNotFoundException(sprintf('The file %s does not exist.', $configPath));
            }

            $faviconRequestPath = $this->cwd.'/faviconRequest.json';
            $fileSystem = new Filesystem();
            if (!$fileSystem->exists($faviconRequestPath)) {
                $fileSystem->copy($configPath, $faviconRequestPath);
            }
            $this->process = new Process(sprintf(
                '%s %s generate %s %s %s',
                $this->nodePath,
                $this->realFaviconPath,
                $configPath,
                $faviconRequestPath,
                $this->target
            ), $this->cwd);
            $this->process->mustRun();

            $response = json_decode(file_get_contents($faviconRequestPath));
            if ($response->result->status === 'success') {
                $fileSystem->remove($faviconRequestPath);
                $files = [];
                foreach ($response->favicon->files_urls as $file) {
                    $path = parse_url($file, PHP_URL_PATH);
                    $pathFragments = explode('/', $path);
                    $files[] = sprintf('%s/%s', $this->target, end($pathFragments));
                }

                return $files;
            }
            throw new GenerateFaviconException(
                sprintf(
                        'An issue occured with the real favicon generation, you can dive into the %s file',
                        $faviconRequestPath
                    )
                );
        } catch (FileNotFoundException | ProcessFailedException $e) {
            throw $e;
        }
    }
}
