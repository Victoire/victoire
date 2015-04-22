<?php

namespace Bundle\WidgetBundle\Tests\Generator;
use Sensio\Bundle\GeneratorBundle\Model\Bundle;
use Sensio\Bundle\GeneratorBundle\Tests\Generator\GeneratorTest;
use Victoire\Bundle\WidgetBundle\Generator\WidgetGenerator;


class WidgetGeneratorTest extends GeneratorTest
{
    public function testGenerate()
    {

        $this->getGenerator()->generate(
            $namespace = 'Victoire\\Widget\\TestBundle',
            $bundle = 'VictoireWidgetTestBundle',
            $dir = $this->tmpDir,
            $format = 'annotation',
            $structure = null,
            $fields = array(
                'test' => array(
                    'columnName' => 'test',
                    'fieldName' => 'test',
                    'type' => 'string',
                    'length' => 255,
                )
            ),
            $parent = 'Anakin',
            $packagistParentName = 'friendsofvictoire/anakin-widget',
            $contentResolver = false,
            $parentContentResolver = false,
            $orgname = 'friendsofvictoire'
        );
        $files = array(
            'README.md',
        );
        foreach ($files as $file) {
            $this->assertTrue(file_exists($this->tmpDir.'/Victoire/Widget/TestBundle/'.$file), sprintf('%s has been generated', $file));
        }
    }
    protected function getGenerator()
    {
        $generator = new WidgetGenerator($this->filesystem);
        $skeletonDirs = array(
            __DIR__.'/../../../../vendor/sensio/generator-bundle/Sensio/Bundle/GeneratorBundle/Resources/skeleton',
            __DIR__.'/../../Resources/skeleton',
        );
        $generator->setSkeletonDirs($skeletonDirs);

        $generator->setTemplating(new \Twig_Environment(new \Twig_Loader_Filesystem($skeletonDirs), array(
            'debug'            => true,
            'cache'            => false,
            'strict_variables' => true,
            'autoescape'       => false,
        )));

        return $generator;
    }
}
