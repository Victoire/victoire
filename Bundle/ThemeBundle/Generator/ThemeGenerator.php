<?php
namespace Victoire\Bundle\ThemeBundle\Generator;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\DependencyInjection\Container;
use Sensio\Bundle\GeneratorBundle\Generator\Generator;

/**
 * This class build all classes, views and config files relative to a ThemeBundle
 */
class ThemeGenerator extends Generator
{
    private $filesystem;
    private $frameworks;
    private $templating;
    private $skeletonDirs;

    /**
     * {@inheritDoc}
     */
    public function __construct(Filesystem $filesystem, $frameworks)
    {
        $this->filesystem = $filesystem;
        $this->frameworks = $frameworks;
    }

    /**
     * set templating
     */
    public function setTemplating($templating)
    {
        $this->templating = $templating;
    }

    /**
     * build ThemeBundle files
     *
     * @param string $namespace             The namespace
     * @param string $bundle                The bundle
     * @param string $theme                 The themeName
     * @param string $widget                The widgetName
     * @param string $widgetEntityNamespace The widgetEntityNamespace
     * @param string $dir                   The dir
     * @param string $format                The format
     * @param string $structure             The structure
     * @param array  $fields                The fields
     */
    public function generate($namespace, $bundle, $theme, $widget, $widgetEntityNamespace, $widgetTypeNamespace, $dir, $format, $structure, $fields = null)
    {
        $dir .= '/'.strtr($namespace, '\\', '/');
        if (file_exists($dir)) {
            if (!is_dir($dir)) {
                throw new \RuntimeException(sprintf('Unable to generate the bundle as the target directory "%s" exists but is a file.', realpath($dir)));
            }
            $files = scandir($dir);
            if ($files != array('.', '..')) {
                throw new \RuntimeException(sprintf('Unable to generate the bundle as the target directory "%s" is not empty.', realpath($dir)));
            }
            if (!is_writable($dir)) {
                throw new \RuntimeException(sprintf('Unable to generate the bundle as the target directory "%s" is not writable.', realpath($dir)));
            }
        }

        $basename = substr($bundle, 0, -6);
        $_fields = array();

        foreach ($fields as $_field) {
            $_fields[] = $_field['fieldName'];
        }

        //guess the toString property we could use
        $toStringProperty = 'id';
        if (in_array('name', $_fields)) {
            $toStringProperty = 'name';
        } elseif (in_array('title', $_fields)) {
            $toStringProperty = 'title';
        } elseif (in_array('description', $_fields)) {
            $toStringProperty = 'description';
        }

        $parameters = array(
            'namespace'             => $namespace,
            'bundle'                => $bundle,
            'theme'                 => $theme,
            'underscore_theme'      => Container::underscore($theme),
            'widget'                => $widget,
            'widgetEntityNamespace' => $widgetEntityNamespace,
            'widgetTypeNamespace'   => $widgetTypeNamespace,
            'format'                => $format,
            'fields'                => $fields,
            'toStringProperty'      => $toStringProperty,
            'bundle_basename'       => $basename,
            'extension_alias'       => Container::underscore($basename),
        );

        $this->renderFile('bundle/Bundle.php.twig', $dir.'/'.$bundle.'.php', $parameters);
        $this->renderFile('README.md.twig', $dir.'/README.md', $parameters);
        $this->renderFile('composer.json.twig', $dir.'/composer.json', $parameters);
        $this->renderFile('theme/Extension.php.twig', $dir.'/DependencyInjection/'.$basename.'Extension.php', array_merge($parameters, array('format' => 'yml')));
        $this->renderFile('theme/Configuration.php.twig', $dir.'/DependencyInjection/Configuration.php', $parameters);

        $this->renderFile('theme/entity.php.twig', $dir.'/Entity/Theme'.$widget.$theme.'.php', $parameters);
        $this->renderFile('theme/form.php.twig', $dir.'/Form/Theme'.$widget.$theme.'Type.php', $parameters);

        $this->renderFile('theme/config.yml.twig', $dir.'/Resources/config/config.yml', $parameters);
        $this->renderFile('theme/services.yml.twig', $dir.'/Resources/config/services.yml', $parameters);

        $this->renderFile('theme/Manager.php.twig', $dir.'/Manager/Theme'.$widget.$theme.'Manager.php', $parameters);
        $this->renderFile('theme/victoire.xliff.twig', $dir.'/Resources/translations/victoire.en.xliff', $parameters);
        $this->renderFile('theme/victoire.xliff.twig', $dir.'/Resources/translations/victoire.fr.xliff', $parameters);

        //Generate new and Edit views
        $this->renderFile('theme/views/new.html.twig.twig', $dir.'/Resources/views/new.html.twig', $parameters);
        $this->renderFile('theme/views/edit.html.twig.twig', $dir.'/Resources/views/edit.html.twig', $parameters);
        $this->renderFile('theme/views/show.html.twig.twig', $dir.'/Resources/views/show.html.twig', $parameters);
    }

    /**
     * write ThemeBundle files
     */
    protected function render($template, $parameters)
    {
        $twig = $this->templating;
        $twig->setLoader(new \Twig_Loader_Filesystem($this->skeletonDirs));

        return $twig->render($template, $parameters);
    }

    /**
     * configure available skeletons twig files
     */
    public function setSkeletonDirs($skeletonDirs)
    {
        parent::setSkeletonDirs($skeletonDirs);
        $this->skeletonDirs = $skeletonDirs;
    }
}
