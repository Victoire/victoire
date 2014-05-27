<?php
namespace Victoire\Bundle\ThemeBundle\Command;

use Sensio\Bundle\GeneratorBundle\Command\GenerateBundleCommand;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Victoire\Bundle\ThemeBundle\Generator\ThemeGenerator;
use Sensio\Bundle\GeneratorBundle\Generator\DoctrineEntityGenerator;
use Sensio\Bundle\GeneratorBundle\Command\Validators;
use Sensio\Bundle\GeneratorBundle\Command\Helper\DialogHelper;
use Doctrine\DBAL\Types\Type;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;

/**
 * Create a new Theme for a VictoireCMS widget
 */
class CreateThemeCommand extends GenerateBundleCommand
{
    protected $skeletonDirs;

    /**
     * {@inheritDoc}
     */
    public function configure()
    {
        parent::configure();

        $this
            ->setName('victoire:generate:theme')
            ->setDefinition(array(
                new InputOption('namespace', '', InputOption::VALUE_REQUIRED, 'The namespace of the theme bundle to create'),
                new InputOption('dir', '', InputOption::VALUE_REQUIRED, 'The directory where to create the bundle'),
                new InputOption('bundle-name', '', InputOption::VALUE_REQUIRED, 'The optional bundle name'),
                new InputOption('theme-name', '', InputOption::VALUE_REQUIRED, 'The theme name'),
                new InputOption('widget-name', '', InputOption::VALUE_REQUIRED, 'The widget name'),
                new InputOption('widget-entity-namespace', '', InputOption::VALUE_REQUIRED, 'The widget entity namespace'),
                new InputOption('format', '', InputOption::VALUE_REQUIRED, 'Use the format for configuration files (php, xml, yml, or annotation)'),
                new InputOption('structure', '', InputOption::VALUE_NONE, 'Whether to generate the whole directory structure'),
                new InputOption('fields', '', InputOption::VALUE_REQUIRED, 'The fields to create with the new entity'),
                new InputOption('entity', '', InputOption::VALUE_REQUIRED, 'The entity class name to initialize (shortcut notation)'),
            ))
            ->setDescription('Generate a new theme')
            ->setHelp(<<<EOT
The <info>victoire:generate:theme</info> command helps you to generate new themes.

By default, the command interacts with the developer to tweak the generation.
Any passed option will be used as a default value for the interaction
(<comment>--theme-name</comment> is the only one needed if you follow the
conventions):

<info>php app/console victoire:generate:theme --theme-name=myAwesomeListingTheme --widget-name=listing</info>

If you want to disable any user interaction, use <comment>--no-interaction</comment> but don't forget to pass all needed options.
EOT
            );
    }

    /**
     * Take arguments and options defined in $this->interact() and generate a new Theme
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @see Command
     * @throws \InvalidArgumentException When namespace doesn't end with Bundle
     * @throws \RuntimeException         When bundle can't be executed
     *
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dialog = $this->getDialogHelper();

        if ($input->isInteractive()) {
            if (!$dialog->askConfirmation($output, $dialog->getQuestion('Do you confirm generation', 'yes', '?'), true)) {
                $output->writeln('<error>Command aborted</error>');

                return 1;
            }
        }

        foreach (array('namespace', 'dir') as $option) {
            if (null === $input->getOption($option)) {
                throw new \RuntimeException(sprintf('The "%s" option must be provided.', $option));
            }
        }

        $namespace = Validators::validateBundleNamespace($input->getOption('namespace'));

        if (!$bundle = $input->getOption('bundle-name')) {
            $bundle = strtr($namespace, array('\\' => ''));
        }

        $bundle = Validators::validateBundleName($bundle);
        $dir    = Validators::validateTargetDir($input->getOption('dir'), $bundle, $namespace);

        if (null === $input->getOption('format')) {
            $input->setOption('format', 'annotation');
        }

        $format = Validators::validateFormat($input->getOption('format'));
        $structure = $input->getOption('structure');

        $dialog->writeSection($output, 'Bundle generation');

        if (!$this->getContainer()->get('filesystem')->isAbsolutePath($dir)) {
            $dir = getcwd().'/'.$dir;
        }


        $fields = $this->parseFields($input->getOption('fields'));

        $generator = $this->getGenerator();

        $generator->generate($namespace, $bundle, $input->getOption('theme-name'), $input->getOption('widget-name'), $input->getOption('widget-entity-namespace'), $dir, $format, $structure, $fields);

        $output->writeln('Generating the bundle code: <info>OK</info>');

        $errors = array();
        $runner = $dialog->getRunner($output, $errors);

        // check that the namespace is already autoloaded
        $runner($this->checkAutoloader($output, $namespace, $bundle, $dir));

        // register the bundle in the Kernel class
        $runner($this->updateKernel($dialog, $input, $output, $this->getContainer()->get('kernel'), $namespace, $bundle));

        $dialog->writeGeneratorSummary($output, $errors);
    }


    /**
     * get a generator for given widget and type, and attach it skeleton dirs
     * @return $generator
     */
    protected function getEntityGenerator()
    {

        $dirs[] = __DIR__.'/../Resources/skeleton';
        $dirs[] = __DIR__.'/../Resources';

        $generator = $this->createEntityGenerator();

        $this->skeletonDirs = array_merge($this->getSkeletonDirs(), $dirs);
        $generator->setSkeletonDirs($this->skeletonDirs);
        $this->setGenerator($generator);

        return $generator;
    }

    /**
     * get a generator for given widget and type, and attach it skeleton dirs
     * @param BundleInterface $bundle The bundle interface
     *
     * @return $generator
     */
    protected function getGenerator(BundleInterface $bundle = null)
    {

        $dirs[] = __DIR__.'/../Resources/skeleton';
        $dirs[] = __DIR__.'/../Resources';

        $generator = $this->createThemeGenerator();

        $this->skeletonDirs = array_merge($this->getSkeletonDirs(), $dirs);
        $generator->setSkeletonDirs($this->skeletonDirs);
        $this->setGenerator($generator);

        return $generator;
    }

    /**
     * Collect options and arguments
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return void
     */
    protected function interact(InputInterface $input, OutputInterface $output)
    {

        $dialog = $this->getDialogHelper();
        $dialog->writeSection($output, 'Welcome to the Victoire widget bundle generator');


        //////////////////////////////
        //                          //
        //   Create Theme Bundle    //
        //                          //
        //////////////////////////////


        // namespace
        $namespace = null;
        try {
            $namespace = $input->getOption('namespace') ? Validators::validateBundleNamespace($input->getOption('namespace')) : null;
        } catch (\Exception $error) {
            $output->writeln($dialog->getHelperSet()->get('formatter')->formatBlock($error->getMessage(), 'error'));
        }

        if (null === $namespace) {
            $output->writeln(array(
                '',
                'Your application code must be written in <comment>widget and theme bundles</comment>. This command helps',
                'you generate them easily.',
                '',
                'Each widget is hosted under a namespace (like <comment>Victoire/Theme/{{ BaseWidget }}/{{ YourAwesomeThemeName }}Bundle</comment>).',
                '',
                'See http://victoire.appventus.com to learn more about developping Victoire'
            ));

            $themeName = $dialog->askAndValidate($output, $dialog->getQuestion('Theme name', $input->getOption('theme-name')), array('Victoire\Bundle\ThemeBundle\Command\CreateThemeCommand', 'validateThemeName'), false, $input->getOption('theme-name'));
            $input->setOption('theme-name', $themeName);
            $widgetName = $dialog->askAndValidate($output, $dialog->getQuestion('Widget name', $input->getOption('widget-name')), array('Victoire\Bundle\ThemeBundle\Command\CreateThemeCommand', 'validateThemeName'), false, $input->getOption('widget-name'));
            $input->setOption('widget-name', $widgetName);

            $bundle = 'VictoireTheme' . $widgetName .  $themeName . 'Bundle';
            $input->setOption('bundle-name', $bundle);
            $namespace = "Victoire\\Theme\\". $widgetName."\\". $themeName ."Bundle";
            $input->setOption('namespace', $namespace);
        }

        if ($this->getContainer()->hasParameter('victoire_'.$widgetName.'.entityClass')) {
            $widgetEntityNamespace = $this->getContainer()->getParameter('victoire_'.$widgetName.'.entityClass');
        } else {
            $widgetEntityNamespace = $dialog->askAndValidate($output, $dialog->getQuestion('Widget\'s entity namespace (please use "/" instead of backslashes)', $input->getOption('widget-entity-namespace')), array('Victoire\Bundle\ThemeBundle\Command\CreateThemeCommand', 'validateNamespace'), false, $input->getOption('widget-entity-namespace'));
        }

        $input->setOption('widget-entity-namespace', $widgetEntityNamespace);

        $dir = dirname($this->getContainer()->getParameter('kernel.root_dir')).'/src';

        $output->writeln(array(
            '',
            'The bundle can be generated anywhere. The suggested default directory uses',
            'the standard conventions.',
            '',
        ));

        $dir = $dialog->askAndValidate(
            $output,
            $dialog->getQuestion('Target directory', $dir),
            function ($dir) use ($bundle, $namespace) {
                return Validators::validateTargetDir($dir, $bundle, $namespace);
            },
            false,
            $dir
        );

        $input->setOption('dir', $dir);


        // format
        $format = null;
        try {
            $format = $input->getOption('format') ? Validators::validateFormat($input->getOption('format')) : null;
        } catch (\Exception $error) {
            $output->writeln($dialog->getHelperSet()->get('formatter')->formatBlock($error->getMessage(), 'error'));
        }

        if (null === $format) {
            $output->writeln(array(
                '',
                'Determine the format to use for the generated configuration.',
                '',
            ));
            $format = $dialog->askAndValidate($output, $dialog->getQuestion('Configuration format (yml, xml, php, or annotation)', "annotation"), array('Sensio\Bundle\GeneratorBundle\Command\Validators', 'validateFormat'), false, "annotation");
            $input->setOption('format', $format);
        }

        $input->setOption('structure', false);


        ///////////////////////
        //                   //
        //   Create Entity   //
        //                   //
        ///////////////////////

        $input->setOption('fields', $this->addFields($input, $output, $dialog));
        $entity = "Theme".$widgetName.$themeName;
        $input->setOption('entity', $bundle.':'.$entity);


        // summary
        $output->writeln(array(
            '',
            $this->getHelper('formatter')->formatBlock('Summary before generation', 'bg=blue;fg=white', true),
            '',
            sprintf("You are going to generate a \"<info>%s\\%s</info>\" theme bundle\nin \"<info>%s</info>\" using the \"<info>%s</info>\" format.", $namespace, $bundle, $dir, $format),
            '',
        ));
    }


    /**
     * Check that provided theme name is correct
     *
     * @param string $theme The theme name
     *
     * @return string $theme
     */
    public static function validateThemeName($theme)
    {
        if (!preg_match('/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/', $theme)) {
            throw new \InvalidArgumentException('The theme name contains invalid characters.');
        }

        if (!preg_match('/^([A-Z][a-z]+)+$/', $theme)) {
            throw new \InvalidArgumentException('The theme name must be PascalCased.');
        }

        return $theme;
    }


    /**
     * Check the namespace entered is correct
     * @param string $namespace The entity namespace (full format)
     *
     * @return boolean Is the namespace correct ?
     */
    public static function validateNamespace($namespace)
    {
        $namespace = strtr($namespace, '/', '\\');
        if (!preg_match('/^(?:[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*\\\?)+$/', $namespace)) {
            throw new \InvalidArgumentException('The namespace contains invalid characters.');
        }

        // validate reserved keywords
        $reserved = Validators::getReservedWords();
        foreach (explode('\\', $namespace) as $word) {
            if (in_array(strtolower($word), $reserved)) {
                throw new \InvalidArgumentException(sprintf('The namespace cannot contain PHP reserved words ("%s").', $word));
            }
        }

        // validate that the namespace is at least one level deep
        if (false === strpos($namespace, '\\')) {
            $msg = array();
            $msg[] = sprintf('The namespace must contain a vendor namespace (e.g. "VendorName\%s" instead of simply "%s").', $namespace, $namespace);
            $msg[] = 'If you\'ve specified a vendor namespace, did you forget to surround it with quotes (init:bundle "Acme\BlogBundle")?';

            throw new \InvalidArgumentException(implode("\n\n", $msg));
        }

        return $namespace;
    }

    /**
     * Instanciate a new ThemeGenerator
     *
     * @return $generator
     */
    protected function createThemeGenerator()
    {
        $generator = new ThemeGenerator(
            $this->getContainer()->get('filesystem'),
            $this->getContainer()->getParameter('victoire_core.available_frameworks')
        );
        $generator->setTemplating($this->getContainer()->get('twig'));

        return $generator;
    }


    /**
     * Instanciate a new Entity generator
     *
     * @return $generator
     */
    protected function createEntityGenerator()
    {
        return new DoctrineEntityGenerator($this->getContainer()->get('filesystem'), $this->getContainer()->get('doctrine'));
    }



    /**
     * transform console's output string fields into an array of fields
     *
     * @param string $input
     * @return array $fields
     */
    private function parseFields($input)
    {
        if (is_array($input)) {
            return $input;
        }

        $fields = array();
        foreach (explode(' ', $input) as $value) {
            $elements = explode(':', $value);
            $name = $elements[0];
            if (strlen($name)) {
                $type = isset($elements[1]) ? $elements[1] : 'string';
                preg_match_all('/(.*)\((.*)\)/', $type, $matches);
                $type = isset($matches[1][0]) ? $matches[1][0] : $type;
                $length = isset($matches[2][0]) ? $matches[2][0] : null;

                $fields[$name] = array('fieldName' => $name, 'type' => $type, 'length' => $length);
            }
        }

        return $fields;
    }


    /**
     * Interactively ask user to add field to his new Entity
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @param DialogHelper    $dialog
     * @return $fields
     */
    private function addFields(InputInterface $input, OutputInterface $output, DialogHelper $dialog)
    {
        $fields = $this->parseFields($input->getOption('fields'));
        $output->writeln(array(
            '',
            'Instead of starting with a blank entity, you can add some fields now.',
            'Note that the primary key will be added automatically (named <comment>id</comment>).',
            '',
        ));
        $output->write('<info>Available types:</info> ');

        $types = array_keys(Type::getTypesMap());
        $count = 20;
        foreach ($types as $i => $type) {
            if ($count > 50) {
                $count = 0;
                $output->writeln('');
            }
            $count += strlen($type);
            $output->write(sprintf('<comment>%s</comment>', $type));
            if (count($types) != $i + 1) {
                $output->write(', ');
            } else {
                $output->write('.');
            }
        }
        $output->writeln('');

        $fieldValidator = function ($type) use ($types) {
            if (!in_array($type, $types)) {
                throw new \InvalidArgumentException(sprintf('Invalid type "%s".', $type));
            }

            return $type;
        };

        $lengthValidator = function ($length) {
            if (!$length) {
                return $length;
            }

            $result = filter_var($length, FILTER_VALIDATE_INT, array(
                'options' => array('min_range' => 1)
            ));

            if (false === $result) {
                throw new \InvalidArgumentException(sprintf('Invalid length "%s".', $length));
            }

            return $length;
        };

        while (true) {
            $output->writeln('');
            $generator = $this->getEntityGenerator();
            $columnName = $dialog->askAndValidate($output, $dialog->getQuestion('New field name (press <return> to stop adding fields)', null), function ($name) use ($fields, $generator) {
                if (isset($fields[$name]) || 'id' == $name) {
                    throw new \InvalidArgumentException(sprintf('Field "%s" is already defined.', $name));
                }

                // check reserved words
                if ($generator->isReservedKeyword($name)) {
                    throw new \InvalidArgumentException(sprintf('Name "%s" is a reserved word.', $name));
                }

                return $name;
            });
            if (!$columnName) {
                break;
            }

            $defaultType = 'string';

            // try to guess the type by the column name prefix/suffix
            if (substr($columnName, -3) == '_at') {
                $defaultType = 'datetime';
            } elseif (substr($columnName, -3) == '_id') {
                $defaultType = 'integer';
            } elseif (substr($columnName, 0, 3) == 'is_') {
                $defaultType = 'boolean';
            } elseif (substr($columnName, 0, 4) == 'has_') {
                $defaultType = 'boolean';
            }

            $type = $dialog->askAndValidate($output, $dialog->getQuestion('Field type', $defaultType), $fieldValidator, false, $defaultType, $types);

            $data = array('columnName' => $columnName, 'fieldName' => lcfirst(Container::camelize($columnName)), 'type' => $type);

            if ($type == 'string') {
                $data['length'] = $dialog->askAndValidate($output, $dialog->getQuestion('Field length', 255), $lengthValidator, false, 255);
            }

            $fields[$columnName] = $data;
        }

        return $fields;
    }



    /**
     * Validate Entity short namepace
     * @param string $shortcut
     * @return $shortcut
     */
    protected function parseShortcutNotation($shortcut)
    {
        $entity = str_replace('/', '\\', $shortcut);

        if (false === $pos = strpos($entity, ':')) {
            throw new \InvalidArgumentException(sprintf('The entity name must contain a : ("%s" given, expecting something like AcmeBlogBundle:Blog/Post)', $entity));
        }

        return array(substr($entity, 0, $pos), substr($entity, $pos + 1));
    }


}
