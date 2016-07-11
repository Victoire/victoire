<?php

namespace Victoire\Bundle\WidgetBundle\Command;

use Doctrine\DBAL\Types\Type;
use Sensio\Bundle\GeneratorBundle\Command\GenerateBundleCommand;
use Sensio\Bundle\GeneratorBundle\Command\Helper\QuestionHelper;
use Sensio\Bundle\GeneratorBundle\Command\Validators;
use Sensio\Bundle\GeneratorBundle\Generator\DoctrineEntityGenerator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Victoire\Bundle\WidgetBundle\Generator\WidgetGenerator;

/**
 * Create a new Widget for VictoireCMS.
 */
class CreateWidgetCommand extends GenerateBundleCommand
{
    protected $skeletonDirs;

    /**
     * {@inheritdoc}
     */
    public function configure()
    {
        parent::configure();

        $this
            ->setName('victoire:generate:widget')
            ->setDefinition([
                new InputOption('namespace', '', InputOption::VALUE_REQUIRED, 'The namespace of the widget bundle to create'),
                new InputOption('dir', '', InputOption::VALUE_REQUIRED, 'The directory where to create the bundle'),
                new InputOption('bundle-name', '', InputOption::VALUE_REQUIRED, 'The optional bundle name'),
                new InputOption('orgname', '', InputOption::VALUE_REQUIRED, 'Your organisation name'),
                new InputOption('widget-name', '', InputOption::VALUE_REQUIRED, 'The widget name'),
                new InputOption('format', '', InputOption::VALUE_REQUIRED, 'Use the format for configuration files (php, xml, yml, or annotation)'),
                new InputOption('structure', '', InputOption::VALUE_NONE, 'Whether to generate the whole directory structure'),
                new InputOption('fields', '', InputOption::VALUE_REQUIRED, 'The fields to create with the new entity'),
                new InputOption('entity', '', InputOption::VALUE_REQUIRED, 'The entity class name to initialize (shortcut notation)'),
                new InputOption('parent', '', InputOption::VALUE_REQUIRED, 'The widget this widget will extends'),
                new InputOption('packagist-parent-name', '', InputOption::VALUE_REQUIRED, 'The packagist name of the widget you want to extends'),
                new InputOption('content-resolver', '', InputOption::VALUE_NONE, 'Whether to generate a blank ContentResolver to customize widget rendering logic'),
                new InputOption('cache', '', InputOption::VALUE_NONE, 'Use redis cache to store widgets until next modification'),
            ])
            ->setDescription('Generate a new widget')
            ->setHelp(<<<'EOT'
The <info>victoire:generate:widget</info> command helps you to generate new widgets.

By default, the command interacts with the developer to tweak the generation.
Any passed option will be used as a default value for the interaction
(<comment>--widget-name</comment> is the only one needed if you follow the
conventions):

<info>php app/console victoire:generate:widget --widget-name=myAwesomeWidget</info>

If you want to disable any user interaction, use <comment>--no-interaction</comment> but don't forget to pass all needed options:

Love you guys, you're awesome xxx
EOT
            );
    }

    /**
     * Take arguments and options defined in $this->interact() and generate a new Widget.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @see Command
     *
     * @throws \InvalidArgumentException When namespace doesn't end with Bundle
     * @throws \RuntimeException         When bundle can't be executed
     *
     * @return int|null
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $questionHelper = $this->getQuestionHelper();

        if ($input->isInteractive()) {
            $question = new ConfirmationQuestion($questionHelper->getQuestion('Do you confirm generation', 'yes', '?'), true);
            if (!$questionHelper->ask($input, $output, $question)) {
                $output->writeln('<error>Command aborted</error>');

                return 1;
            }
        }

        foreach (['namespace', 'dir'] as $option) {
            if (null === $input->getOption($option)) {
                throw new \RuntimeException(sprintf('The "%s" option must be provided.', $option));
            }
        }

        $namespace = Validators::validateBundleNamespace($input->getOption('namespace'));

        if (!$bundle = $input->getOption('bundle-name')) {
            $bundle = strtr($namespace, ['\\' => '']);
        }

        $orgname = $input->getOption('orgname');

        if (null === $input->getOption('orgname')) {
            $orgname = $input->setOption('orgname', 'friendsofvictoire');
        }

        $parent = $input->getOption('parent');

        if (null === $input->getOption('parent')) {
            $parent = $input->setOption('parent', null);
        }

        $packagistParentName = $input->getOption('packagist-parent-name');

        if (null === $input->getOption('packagist-parent-name')) {
            $packagistParentName = $input->setOption('packagist-parent-name', null);
        }

        $bundle = Validators::validateBundleName($bundle);
        $dir = Validators::validateTargetDir($input->getOption('dir'), $bundle, $namespace);

        if (null === $input->getOption('format')) {
            $input->setOption('format', 'annotation');
        }

        $format = Validators::validateFormat($input->getOption('format'));
        $structure = $input->getOption('structure');

        $contentResolver = $input->getOption('content-resolver');
        $cache = $input->getOption('cache');

        $questionHelper->writeSection($output, 'Bundle generation');

        if (!$this->getContainer()->get('filesystem')->isAbsolutePath($dir)) {
            $dir = getcwd().'/'.$dir;
        }

        $fields = $this->parseFields($input->getOption('fields'));

        $parentContentResolver = $this->getContainer()->has('victoire_core.widget_'.strtolower($parent).'_content_resolver');

        $generator = $this->getGenerator();
        $generator->generate($namespace, $bundle, $dir, $format, $structure, $fields, $parent, $packagistParentName, $contentResolver, $parentContentResolver, $orgname, $cache);

        $output->writeln('Generating the bundle code: <info>OK</info>');

        $errors = [];
        $runner = $questionHelper->getRunner($output, $errors);

        // check that the namespace is already autoloaded
        $runner($this->checkAutoloader($output, $namespace, $bundle, $dir));

        // register the bundle in the Kernel class
        $runner($this->updateKernel($questionHelper, $input, $output, $this->getContainer()->get('kernel'), $namespace, $bundle));

        $questionHelper->writeGeneratorSummary($output, $errors);
    }

    /**
     * get a generator for given widget and type, and attach it skeleton dirs.
     *
     * @return $generator
     */
    protected function getEntityGenerator()
    {
        $dirs[] = $this->getContainer()->get('file_locator')->locate('@VictoireWidgetBundle/Resources/skeleton/');
        $dirs[] = $this->getContainer()->get('file_locator')->locate('@VictoireWidgetBundle/Resources/');

        $generator = $this->createEntityGenerator();

        $this->skeletonDirs = array_merge($this->getSkeletonDirs(), $dirs);
        $generator->setSkeletonDirs($this->skeletonDirs);
        $this->setGenerator($generator);

        return $generator;
    }

    /**
     * get a generator for given widget and type, and attach it skeleton dirs.
     *
     * @return $generator
     */
    protected function getGenerator(BundleInterface $bundle = null)
    {
        $dirs[] = $this->getContainer()->get('file_locator')->locate('@VictoireWidgetBundle/Resources/skeleton/');
        $dirs[] = $this->getContainer()->get('file_locator')->locate('@VictoireWidgetBundle/Resources/');

        $generator = $this->createWidgetGenerator();

        $this->skeletonDirs = array_merge($this->getSkeletonDirs(), $dirs);
        $generator->setSkeletonDirs($this->skeletonDirs);
        $this->setGenerator($generator);

        return $generator;
    }

    /**
     * Collect options and arguments.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return void
     */
    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $questionHelper = $this->getQuestionHelper();
        $questionHelper->writeSection($output, 'Welcome to the Victoire widget bundle generator');

        ///////////////////////
        //                   //
        //   Create Bundle   //
        //                   //
        ///////////////////////

        // namespace
        $namespace = null;
        try {
            $namespace = $input->getOption('namespace') ? Validators::validateBundleNamespace($input->getOption('namespace')) : null;
        } catch (\Exception $error) {
            $output->writeln($questionHelper->getHelperSet()->get('formatter')->formatBlock($error->getMessage(), 'error'));
        }

        if (null === $namespace) {
            $output->writeln([
                '',
                'Your application code must be written in <comment>widget bundles</comment>. This command helps',
                'you generate them easily.',
                '',
                'Each widget is hosted under a namespace (like <comment>Victoire/Widget/YourAwesomeWidgetNameBundle</comment>).',
                '',
                'If you want for example a BlogWidget, the Widget Name should be Blog',
            ]);

            $question = new Question($questionHelper->getQuestion('Widget name', $input->getOption('bundle-name')));
            $question->setValidator(function ($answer) {
                return self::validateWidgetName($answer, false);
            });

            $name = $questionHelper->ask(
                $input,
                $output,
                $question
            );

            $bundle = 'VictoireWidget'.$name.'Bundle';
            $input->setOption('bundle-name', $bundle);
            $namespace = 'Victoire\\Widget\\'.$name.'Bundle';
            $input->setOption('namespace', $namespace);
        }

        $orgname = $input->getOption('orgname');

        if (null === $orgname) {
            $output->writeln([
                '',
                'A composer.json file will be generated, we need to know under which organisation you will publish the widget',
                '',
                'The default organisation will be friendsofvictoire',
            ]);
            $question = new Question($questionHelper->getQuestion('Under which organisation do you want to publish your widget ?', 'friendsofvictoire'), 'friendsofvictoire');

            $orgname = $questionHelper->ask($input, $output, $question);
        }

        $input->setOption('orgname', $orgname);

        $parent = $input->getOption('parent');

        $question = new ConfirmationQuestion($questionHelper->getQuestion('Does your widget extends another widget ?', 'no', '?'), false);

        if (null === $parent && $questionHelper->ask($input, $output, $question)) {
            $output->writeln([
                '',
                'A widget can extends another to reproduce it\'s behavior',
                '',
                'If you wabt to do so, please give the name of the widget to extend',
                '',
                'If you want to extends the TestWidget, the widget name should be Test',
            ]);

            $question = new Question($questionHelper->getQuestion('Parent widget name', false));
            $question->setValidator(function ($answer) {
                return self::validateWidgetName($answer, false);
            });
            $parent = $questionHelper->ask($input, $output, $question);

            $input->setOption('parent', $parent);

            $packagistParentName = 'friendsofvictoire/'.strtolower($parent).'-widget';
            $question = new Question($questionHelper->getQuestion('Parent widget packagist name', $packagistParentName));

            $parent = $questionHelper->ask($input, $output, $question);

            $input->setOption('packagist-parent-name', $packagistParentName);
        }

        $dir = dirname($this->getContainer()->getParameter('kernel.root_dir')).'/src';

        $output->writeln([
            '',
            'The bundle can be generated anywhere. The suggested default directory uses',
            'the standard conventions.',
            '',
        ]);

        $question = new Question($questionHelper->getQuestion('Target directory', $dir), $dir);
        $question->setValidator(function ($dir) use ($bundle, $namespace) {
            return Validators::validateTargetDir($dir, $bundle, $namespace);
        });
        $dir = $questionHelper->ask($input, $output, $question);
        $input->setOption('dir', $dir);

        // format
        $format = null;
        try {
            $format = $input->getOption('format') ? Validators::validateFormat($input->getOption('format')) : null;
        } catch (\Exception $error) {
            $output->writeln($questionHelper->getHelperSet()->get('formatter')->formatBlock($error->getMessage(), 'error'));
        }

        if (null === $format) {
            $output->writeln([
                '',
                'Determine the format to use for the generated configuration.',
                '',
            ]);

            $question = new Question($questionHelper->getQuestion('Configuration format (yml, xml, php, or annotation)', 'annotation'), 'annotation');
            $question->setValidator(
                ['Sensio\Bundle\GeneratorBundle\Command\Validators', 'validateFormat']
            );
            $format = $questionHelper->ask($input, $output, $question);
            $input->setOption('format', $format);
        }

        $input->setOption('structure', false);

        $contentResolver = $input->getOption('content-resolver');

        $question = new ConfirmationQuestion($questionHelper->getQuestion('Do you want to customize widget rendering logic ?', 'no', '?'), false);
        if (!$contentResolver && $questionHelper->ask($input, $output, $question)) {
            $contentResolver = true;
        }
        $input->setOption('content-resolver', $contentResolver);

        ///////////////////////
        //                   //
        //   Create Entity   //
        //                   //
        ///////////////////////

        $input->setOption('fields', $this->addFields($input, $output, $questionHelper));
        $entity = 'Widget'.$name;
        $input->setOption('entity', $bundle.':'.$entity);

        $cache = $input->getOption('cache');
        $question = new ConfirmationQuestion($questionHelper->getQuestion('Do you want use cache for this widget ?', 'yes', '?'));
        if (null !== $cache) {
            $cache = $questionHelper->ask($input, $output, $question);
        }
        $input->setOption('cache', $cache);

        // summary
        $output->writeln([
            '',
            $this->getHelper('formatter')->formatBlock('Summary before generation', 'bg=blue;fg=white', true),
            '',
            sprintf("You are going to generate a \"<info>%s\\%s</info>\" widget bundle\nin \"<info>%s</info>\" using the \"<info>%s</info>\" format.", $namespace, $bundle, $dir, $format),
            '',
        ]);
    }

    /**
     * Check that provided widget name is correct.
     *
     * @param string $widget
     *
     * @return string $widget
     */
    public static function validateWidgetName($widget)
    {
        if (!preg_match('/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/', $widget)) {
            throw new \InvalidArgumentException('The widget name contains invalid characters.');
        }

        if (!preg_match('/^([A-Z][a-z]+)+$/', $widget)) {
            throw new \InvalidArgumentException('The widget name must be PascalCased.');
        }

        return $widget;
    }

    /**
     * Instanciate a new WidgetGenerator.
     *
     * @return $generator
     */
    protected function createWidgetGenerator()
    {
        $generator = new WidgetGenerator($this->getContainer()->get('filesystem'));
        $generator->setTemplating($this->getContainer()->get('twig'));

        return $generator;
    }

    /**
     * Instanciate a new Entity generator.
     *
     * @return $generator
     */
    protected function createEntityGenerator()
    {
        return new DoctrineEntityGenerator($this->getContainer()->get('filesystem'), $this->getContainer()->get('doctrine'));
    }

    /**
     * transform console's output string fields into an array of fields.
     *
     * @param string $input
     *
     * @return array $fields
     */
    private function parseFields($input)
    {
        if (is_array($input)) {
            return $input;
        }

        $fields = [];
        foreach (explode(' ', $input) as $value) {
            $elements = explode(':', $value);
            $name = $elements[0];
            if (strlen($name)) {
                $type = isset($elements[1]) ? $elements[1] : 'string';
                preg_match_all('/(.*)\((.*)\)/', $type, $matches);
                $type = isset($matches[1][0]) ? $matches[1][0] : $type;
                $length = isset($matches[2][0]) ? $matches[2][0] : null;

                $fields[$name] = ['fieldName' => $name, 'type' => $type, 'length' => $length];
            }
        }

        return $fields;
    }

    /**
     * Interactively ask user to add field to his new Entity.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @param QuestionHelper  $questionHelper
     *
     * @return $fields
     */
    private function addFields(InputInterface $input, OutputInterface $output, QuestionHelper $questionHelper)
    {
        $fields = $this->parseFields($input->getOption('fields'));
        $output->writeln([
            '',
            'Instead of starting with a blank entity, you can add some fields now.',
            'Note that the primary key will be added automatically (named <comment>id</comment>).',
            '',
        ]);
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

            $result = filter_var($length, FILTER_VALIDATE_INT, [
                'options' => ['min_range' => 1],
            ]);

            if (false === $result) {
                throw new \InvalidArgumentException(sprintf('Invalid length "%s".', $length));
            }

            return $length;
        };

        while (true) {
            $output->writeln('');
            $generator = $this->getEntityGenerator();

            $question = new Question($questionHelper->getQuestion('New field name (press <return> to stop adding fields)', null));
            $question->setValidator(
                function ($name) use ($fields, $generator) {
                    if (isset($fields[$name]) || 'id' == $name) {
                        throw new \InvalidArgumentException(sprintf('Field "%s" is already defined.', $name));
                    }

                    // check reserved words by database
                    if ($generator->isReservedKeyword($name)) {
                        throw new \InvalidArgumentException(sprintf('Name "%s" is a reserved word.', $name));
                    }
                    // check reserved words by victoire
                    if ($this->isReservedKeyword($name)) {
                        throw new \InvalidArgumentException(sprintf('Name "%s" is a Victoire reserved word.', $name));
                    }

                    return $name;
                }
            );

            $columnName = $questionHelper->ask($input, $output, $question);
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

            $question = new Question($questionHelper->getQuestion('Field type', $defaultType), $defaultType);
            $question->setValidator($fieldValidator);
            $question->setAutocompleterValues($types);
            $type = $questionHelper->ask($input, $output, $question);

            $data = ['columnName' => $columnName, 'fieldName' => lcfirst(Container::camelize($columnName)), 'type' => $type];

            if ($type == 'string') {
                $question = new Question($questionHelper->getQuestion('Field length', 255), 255);
                $question->setValidator($lengthValidator);
                $data['length'] = $questionHelper->ask($input, $output, $question);
            }

            $fields[$columnName] = $data;
        }

        return $fields;
    }

    /**
     * Validate Entity short namepace.
     *
     * @param string $shortcut
     *
     * @return $shortcut
     */
    protected function parseShortcutNotation($shortcut)
    {
        $entity = str_replace('/', '\\', $shortcut);

        if (false === $pos = strpos($entity, ':')) {
            throw new \InvalidArgumentException(sprintf('The entity name must contain a : ("%s" given, expecting something like AcmeBlogBundle:Blog/Post)', $entity));
        }

        return [substr($entity, 0, $pos), substr($entity, $pos + 1)];
    }

    protected function isReservedKeyword($keyword)
    {
        return in_array($keyword, ['widget']);
    }
}
