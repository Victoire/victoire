<?php
namespace Victoire\Bundle\WidgetBundle\Form;

use Symfony\Component\Finder\Finder;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Victoire\Bundle\WidgetBundle\Entity\Widget;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

/**
 * Widget Style form type
 */
class WidgetStyleType extends AbstractType
{
    private $kernel;
    private $fileLocator;

    public function __construct(Kernel $kernel, FileLocator $fileLocator)
    {
        $this->kernel = $kernel;
        $this->fileLocator = $fileLocator;
    }
    /**
     * Define form fields
     *
     * @param FormBuilderInterface $builder The builder
     * @param array                $options The options
     *
     * @throws Exception
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('containerTag', 'choice', array(
            'label' => 'widget_layout.form.containerTag.label',
            'vic_help_block' => 'widget_layout.form.containerTag.help_block',
            'choices' => array_combine(Widget::$tags, Widget::$tags),
        ));
        $builder->add('containerClass', null, array(
            'label' => 'widget_layout.form.containerClass.label',
            'required' => false,
        ));
        $builder->add('containerWidth', null, array(
            'label' => 'widget_layout.form.containerWidth.label',
            'vic_help_block' => 'widget_layout.form.containerWidth.help_block',
            'required' => false,
        ));
        $builder->add('containerMargin', null, array(
            'label' => 'widget_layout.form.containerMargin.label',
            'vic_help_block' => 'widget_layout.form.containerMargin.help_block',
            'required' => false,
        ));
        $builder->add('containerPadding', null, array(
            'label' => 'widget_layout.form.containerPadding.label',
            'vic_help_block' => 'widget_layout.form.containerPadding.help_block',
            'required' => false,
        ));
        $builder->add('textAlign', 'choice', array(
            'label'       => 'widget_layout.form.textAlign.label',
            'required'    => false,
            'empty_value' => true,
            'choices'     => array(
                ''        => '',
                'left'    => 'widget_layout.form.textAlign.choices.left.label',
                'center'  => 'widget_layout.form.textAlign.choices.center.label',
                'right'   => 'widget_layout.form.textAlign.choices.right.label',
                'justify' => 'widget_layout.form.textAlign.choices.justify.label',
            ),
        ));

        //@todo make it dynamic from the global variable (same name) or twig bundle parameter
        $victoire_twig_responsive = array(
            'XS', 'SM', 'MD', 'LG',
        );
        foreach ($victoire_twig_responsive as $key) {
            $builder->add('containerMargin'.$key, null, array(
                'label' => 'widget_layout.form.containerMargin'.$key.'.label',
                'required' => false,
            ));
            $builder->add('containerPadding'.$key, null, array(
                'label' => 'widget_layout.form.containerPadding'.$key.'.label',
                'required' => false,
            ));
        }

        $builder->add('containerBackground', null, array(
            'label' => 'widget_layout.form.containerBackground.label',
            'vic_help_block' => 'widget_layout.form.containerBackground.help_block',
            'required' => false,
        ));

        // add theme field
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $data = $event->getData();
            $form = $event->getForm();
            //guess the bundle name
            $widgetBundle = self::getBundleNameFromEntity(get_class($data), $this->kernel->getBundles());
            //search for available theme files
            $finder = Finder::create()
                ->files()
                ->name('/^show(.)+\.html\.twig$/')
                ->in($this->fileLocator->locate('@'.$widgetBundle.'/Resources/views'))
                ->sortByName();
            //add the default choice
            $choices = array(
                '' => 'victoire.theme.default.label',
            );
            //prepare choices by adding in each theme
            foreach ($finder as $key => $file) {
                $theme = $file->getRelativePathname();
                $theme = preg_replace('/show|\.html\.twig/', '', $theme);
                $choices[$theme] = 'victoire.'.$widgetBundle.'.theme.'.$theme.'.label';
            }
            //We add the theme type only if there is a choice
            if (count($choices) > 1) {
                $form->add('theme', 'choice', array(
                    'label' => 'widget.form.theme.label',
                    'choices' => $choices,
                ));
            }
        });
    }

    /**
     * bind form to WidgetRedactor entity
     * @param OptionsResolverInterface $resolver
     *
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class'         => 'Victoire\Bundle\WidgetBundle\Entity\Widget',
            'translation_domain' => 'victoire',
        ));
    }

    /**
     * get form name
     *
     * @return string
     */
    public function getName()
    {
        return 'victoire_widget_style_type';
    }

    /**
     * Get the bundle name from an Entity namespace
     *
     * @return string
     * @author lenybernard
     **/
    protected static function getBundleNameFromEntity($entityNamespace, $bundles)
    {
        $dataBaseNamespace = substr($entityNamespace, 0, strpos($entityNamespace, '\\Entity\\'));
        foreach ($bundles as $type => $bundle) {
            $bundleRefClass = new \ReflectionClass($bundle);
            if ($bundleRefClass->getNamespaceName() === $dataBaseNamespace) {
                return $type;
            }
        }
    }
}
