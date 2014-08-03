<?php
namespace Victoire\Bundle\CoreBundle\Tests\Generator;

use Victoire\Bundle\CoreBundle\Tests\Utils\BaseTestCase;
use Victoire\Bundle\CoreBundle\Generator\WidgetGenerator;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Tests the CmsExtension class
 */
class WidgetGeneratorTest extends BaseTestCase
{
    protected $filesystem;
    protected $generator;

    public function testGenerateYaml()
    {

            $data = array('columnName' => 'title', 'fieldName' => 'title', 'type' => 'string');
            $fields['title'] = $data;

        $this->getGenerator()->generate('Victoire\BarBundle', 'VictoireBarBundle', $this->tmpDir, 'yml', false, $fields);

        $files = array(
            'VictoireBarBundle.php',
            'Resources/config/services.yml',
            'Resources/config/config.yml',
            'Resources/views/Bootstrap/Widget/bar/new.html.twig',
            'Resources/views/Bootstrap/Widget/bar/edit.html.twig',
            'Resources/views/Bootstrap/Widget/bar/show.html.twig',
            'Resources/views/Foundation/Widget/bar/new.html.twig',
            'Resources/views/Foundation/Widget/bar/edit.html.twig',
            'Resources/views/Foundation/Widget/bar/show.html.twig',
            'DependencyInjection/Configuration.php',
            'DependencyInjection/VictoireBarExtension.php',
            'Widget/Manager/WidgetBarManager.php',
            'Entity/WidgetBar.php',
            'Form/WidgetBarType.php',
        );
        foreach ($files as $file) {
            $this->assertTrue(file_exists($this->tmpDir.'/Victoire/BarBundle/'.$file), sprintf('%s has been generated', $file));
            $this->assertTrue(file_exists($this->tmpDir.'/Victoire/BarBundle/'.$file), sprintf('%s has been generated', $file));
        }

        $content = file_get_contents($this->tmpDir.'/Victoire/BarBundle/VictoireBarBundle.php');
        $this->assertContains('namespace Victoire\\BarBundle', $content);

        // $content = file_get_contents($this->tmpDir.'/Victoire/BarBundle/Resources/views/Default/index.html.twig');
        // $this->assertContains('Hello {{ name }}!', $content);
    }

    protected function getGenerator()
    {
        $bundle = new \Sensio\Bundle\GeneratorBundle\SensioGeneratorBundle();
        $dirs[] = __DIR__.'/../../Resources/skeleton';
        $dirs[] = __DIR__.'/../../Resources';

        $generator = new WidgetGenerator($this->filesystem, array('bootstrap', 'foundation'));
        $generator->setTemplating($this->container->get('twig'));

        $this->generator = $generator;

        $this->skeletonDirs = array_merge($this->getSkeletonDirs($bundle), $dirs);
        $generator->setSkeletonDirs($this->skeletonDirs);

        return $generator;
    }

    protected function getSkeletonDirs($bundle = null)
    {
        $generatorCommand;
        $skeletonDirs = array();
        $skeletonDirs[] =  $bundle->getPath()."/Resources/skeleton";
        if (isset($bundle) && is_dir($dir = $bundle->getPath().'/Resources/SensioGeneratorBundle/skeleton')) {
            $skeletonDirs[] = $dir;
        }

        if (is_dir($dir = $this->container->get('kernel')->getRootdir().'/Resources/SensioGeneratorBundle/skeleton')) {
            $skeletonDirs[] = $dir;
        }

        return $skeletonDirs;
    }

    public function setUp()
    {
        $this->tmpDir = sys_get_temp_dir().'/sf2';
        $this->filesystem = new Filesystem();
        $this->filesystem->remove($this->tmpDir);
        parent::setUp();
    }

    public function tearDown()
    {
        parent::tearDown();
        $this->filesystem->remove($this->tmpDir);
    }
}
