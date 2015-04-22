<?php
namespace Victoire\Bundle\WidgetBundle\Generator;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\DependencyInjection\Container;
use Sensio\Bundle\GeneratorBundle\Generator\Generator;

/**
 * This class build all classes, views and config files relative to a WidgetBundle
 */
class WidgetGenerator extends Generator
{
    private $filesystem;
    private $templating;
    private $skeletonDirs;

    /**
     * Constructor
     *
     * @param Filesystem $filesystem
     * @param unknown    $frameworks
     */
    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    /**
     * set templating
     *
     * @param unknown $templating
     */
    public function setTemplating($templating)
    {
        $this->templating = $templating;
    }

    /**
     * build WidgetBundle files
     * @param string $namespace
     * @param string $format
     */
    public function generate($namespace, $bundle, $dir, $format, $structure, $fields = null, $parent = null, $packagistParentName = null, $contentResolver = false, $parentContentResolver = false, $orgname = null)
    {

        $dir .= '/'.strtr($namespace, '\\', '/');
        if (file_exists($dir)) {
            if (!is_dir($dir)) {
                throw new \RuntimeException(sprintf('Unable to generate the bundle as the target directory "%s" exists but is a file.', realpath($dir)));
            }
            $files = scandir($dir);
            if ($files !== array('.', '..')) {
                throw new \RuntimeException(sprintf('Unable to generate the bundle as the target directory "%s" is not empty.', realpath($dir)));
            }
            if (!is_writable($dir)) {
                throw new \RuntimeException(sprintf('Unable to generate the bundle as the target directory "%s" is not writable.', realpath($dir)));
            }
        }

        $basename = substr($bundle, 0, -6);
        $widget = str_replace('VictoireWidget', '', $basename);
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
            'namespace'               => $namespace,
            'bundle'                  => $bundle,
            'parent'                  => $parent,
            'packagistParentName'     => $packagistParentName,
            'orgname'                 => $orgname,
            'widget'                  => $widget,
            'format'                  => $format,
            'fields'                  => $fields,
            'toStringProperty'        => $toStringProperty,
            'bundle_basename'         => $basename,
            'content_resolver'        => $contentResolver,
            'parent_content_resolver' => $parentContentResolver,
            'extension_alias'         => Container::underscore($basename),
        );

        $this->renderFile('bundle/Bundle.php.twig', $dir.'/'.$bundle.'.php', $parameters);
        $this->renderFile('README.md.twig', $dir.'/README.md', $parameters);
        $this->renderFile('composer.json.twig', $dir.'/composer.json', $parameters);
        $this->renderFile('bundle/Extension.php.twig', $dir.'/DependencyInjection/'.$basename.'Extension.php', array_merge($parameters, array('format' => 'yml')));
        $this->renderFile('bundle/Configuration.php.twig', $dir.'/DependencyInjection/Configuration.php', $parameters);

        $this->renderFile('widget/entity.php.twig', $dir.'/Entity/Widget'.$widget.'.php', $parameters);
        $this->renderFile('widget/form.php.twig', $dir.'/Form/Widget'.$widget.'Type.php', $parameters);

        $this->renderFile('widget/config.yml.twig', $dir.'/Resources/config/config.yml', $parameters);
        $this->renderFile('widget/services.yml.twig', $dir.'/Resources/config/services.yml', $parameters);

        $this->renderFile('widget/victoire.xliff.twig', $dir.'/Resources/translations/victoire.en.xliff', $parameters);
        $this->renderFile('widget/victoire.xliff.twig', $dir.'/Resources/translations/victoire.fr.xliff', $parameters);

        //Generate new and Edit views
        $this->renderFile('widget/views/new.html.twig.twig', $dir.'/Resources/views/new.html.twig', $parameters);
        $this->renderFile('widget/views/edit.html.twig.twig', $dir.'/Resources/views/edit.html.twig', $parameters);
        $this->renderFile('widget/views/show.html.twig.twig', $dir.'/Resources/views/show.html.twig', $parameters);

        if ($contentResolver) {
            $parameters['parentResolver'] = class_exists('Victoire\\Widget\\'.$parent.'Bundle\\Widget\\Resolver\\Widget'.$parent.'ContentResolver');
            $this->renderFile('widget/ContentResolver.php.twig', $dir.'/Widget/Resolver/Widget'.$widget.'ContentResolver.php', $parameters);
        }

    }

    /**
     * write WidgetBundle files
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
