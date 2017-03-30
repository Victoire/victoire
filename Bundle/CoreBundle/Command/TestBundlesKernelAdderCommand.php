<?php

namespace Victoire\Bundle\CoreBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

class TestBundlesKernelAdderCommand extends ContainerAwareCommand
{
    private $appKernel = __DIR__ . '/../../../Tests/App/app/AppKernel.php';

    /**
     * {@inheritdoc}
     */
    public function configure()
    {
        parent::configure();

        $this
            ->setName('victoire:testBundles:kernelAdd')
            ->setDescription('Automatically add Victoire Bundles (like Widgets) to Tests AppKernel');
    }

    /**
     * Automatically add unregistered Victoire Bundles to App/app/AppKernel for tests purposes.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @throws \ErrorException
     *
     * @return bool
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!file_exists($this->appKernel)) {
            throw new \ErrorException(sprintf("Could not locate file %s", $this->appKernel));
        }

        if (!is_writable($this->appKernel)) {
            throw new \ErrorException(sprintf('Cannot write into AppKernel (%s)', $this->appKernel));
        }

        $appKernelContent = file_get_contents($this->appKernel);
        $newBundles = '';
        $finder = new Finder();
        $finder->files()->in(__DIR__ . '/../../../vendor/victoire')->exclude('victoire')->name('*Bundle.php');

        foreach ($finder as $file) {

            //Extract Bundle namespace and class name
            /* @var $file \SplFileInfo */
            $fileContent = file_get_contents($file->getRealPath());
            preg_match('/namespace (.*);/', $fileContent, $namespaceMatches);
            preg_match('/class (.*) extends/', $fileContent, $classMatches);

            //Add Bundle if not already loaded in AppKernel
            $newBundle = 'new ' . $namespaceMatches[1] . '\\' . $classMatches[1] . '(),' . PHP_EOL;
            if (false === strpos($appKernelContent, $newBundle)) {
                $newBundles .= $newBundle;
            }
        }

        //Write into AppKernel file
        file_put_contents(
            $this->appKernel,
            str_replace(
                '//TestBundlesKernelAdderSpot',
                $newBundles,
                $appKernelContent
            )
        );

        return true;
    }
}