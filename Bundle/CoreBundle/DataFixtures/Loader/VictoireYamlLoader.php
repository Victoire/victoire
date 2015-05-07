<?php

namespace Victoire\Bundle\CoreBundle\DataFixtures\Loader;

use Symfony\Component\Yaml\Yaml as YamlParser;
use Nelmio\Alice\Loader\Base as AliceBaseLoader;

/**
 * This YamlLoader just changes User class before importing DataFixtures
 * because Victoire allows you to set your own USer class and auto inject the metadata
 * making the relation dynamic. We have to change Default Victoire User class into the project one
 */
class VictoireYamlLoader extends AliceBaseLoader
{
    /**
     * {@inheritDoc}
     */
    public function load($file)
    {
        ob_start();
        $loader = $this;
        $includeWrapper = function () use ($file, $loader) {
            return include $file;
        };
        $data = $includeWrapper();
        if (true !== $data) {
            $yaml = ob_get_clean();
            $data = YamlParser::parse($yaml);
        }

        if (!is_array($data)) {
            throw new \UnexpectedValueException('Yaml files must parse to an array of data');
        }

        foreach ($data as $key => $value) {
            if ($key == "Victoire\Bundle\UserBundle\Entity\User") {
                $data[$this->userClass] = $value;
            }
            unset($data[$key]);
        }

        return parent::load($data);
    }
}
