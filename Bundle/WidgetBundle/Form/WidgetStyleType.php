<?php
namespace Victoire\Bundle\WidgetBundle\Form;

use Symfony\Component\Finder\Finder;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
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
    private $victoire_twig_responsive;

    public function __construct(Kernel $kernel, FileLocator $fileLocator)
    {
        $this->kernel = $kernel;
        $this->fileLocator = $fileLocator;

        //@todo make it dynamic from the global variable (same name) or twig bundle parameter
        $this->victoire_twig_responsive = ['', 'XS', 'SM', 'MD', 'LG'];
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
        $builder
            ->add('containerTag', 'choice', array(
                'label' => 'widget_layout.form.containerTag.label',
                'vic_help_block' => 'widget_layout.form.containerTag.help_block',
                'choices' => array_combine(Widget::$tags, Widget::$tags),
            ))
            ->add('containerClass', null, array(
                'label' => 'widget_layout.form.containerClass.label',
                'required' => false,
            ))
            ->add('containerBackground', null, array(
                'label' => 'widget_layout.form.containerBackground.label',
                'vic_help_block' => 'widget_layout.form.containerBackground.help_block',
                'required' => false,
            ))
            ->add('vicActiveTab', 'hidden', array(
                'required' => false,
                'mapped' => false,
            ))
            ->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) {
                /**
                 * Generate form fields for each part of form
                 * Example, generate Static content, XS and SM as color forms
                 * Whereas MD and LG are image forms
                 */
                foreach ($this->victoire_twig_responsive as $key) {
                    self::generateBackgroundFields($event->getForm(), $key, $event->getData()->{'getContainerBackgroundType'.$key}());
                }
            })
        ;

        foreach ($this->victoire_twig_responsive as $key) {
            /**
             * Build global fields for all parts of form
             */
            $builder
                ->add('containerMargin'.$key, null, array(
                    'label' => 'widget_layout.form.containerMargin'.$key.'.label',
                    'vic_help_block' => 'widget_layout.form.containerMargin.help_block',
                    'required' => false,
                ))
                ->add('containerPadding'.$key, null, array(
                    'label' => 'widget_layout.form.containerPadding'.$key.'.label',
                    'vic_help_block' => 'widget_layout.form.containerPadding.help_block',
                    'required' => false,
                ))
                ->add('containerWidth'.$key, null, array(
                    'label' => 'widget_layout.form.containerWidth'.$key.'.label',
                    'vic_help_block' => 'widget_layout.form.containerWidth.help.label',
                ))
                ->add('containerHeight'.$key, null, array(
                    'label' => 'widget_layout.form.containerHeight'.$key.'.label',
                    'vic_help_block' => 'widget_layout.form.containerHeight.help.label',
                ))
                ->add('textAlign'.$key, 'choice', array(
                    'label' => 'widget_layout.form.textAlign'.$key.'.label',
                    'required'    => false,
                    'empty_value' => true,
                    'choices'     => array(
                        ''        => '',
                        'left'    => 'widget_layout.form.textAlign.choices.left.label',
                        'center'  => 'widget_layout.form.textAlign.choices.center.label',
                        'right'   => 'widget_layout.form.textAlign.choices.right.label',
                        'justify' => 'widget_layout.form.textAlign.choices.justify.label',
                    ),
                ))
                ->add('containerBackgroundType'.$key, 'choice', array(
                    'label' => 'widget_layout.form.containerBackgroundType'.$key.'.label',
                    'choices'     => array(
                        'color'   => 'widget_layout.form.containerBackgroundType.choices.color.label',
                        'image'   => 'widget_layout.form.containerBackgroundType.choices.image.label',
                    ),
                    'attr' => [
                        'data-refreshOnChange' => "true",
                    ],
                ))
                ->get('containerBackgroundType'.$key)->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) use ($key) {
                    self::generateBackgroundFields($event->getForm()->getParent(), $key, $event->getData());
                })
            ;

        }

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
                ->in($this->fileLocator->locate('@'.$widgetBundle.'/Resources/views', null, false))
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

    private function generateBackgroundFields(FormInterface $form, $responsiveKey, $type = null) {
        /**
         * Build the part of form as the good type
         * Exemple: XS will have background color field whereas SM will have background image field
         */
        if ($type == 'image') {
            $form->
                remove('containerBackgroundColor'.$responsiveKey)
                ->add('containerBackgroundImage'.$responsiveKey, 'media', array(
                    'label' => 'widget_layout.form.containerBackgroundImage'.$responsiveKey.'.label',
                ))
                ->add('containerBackgroundRepeat'.$responsiveKey, 'choice', array(
                    'label' => 'widget_layout.form.containerBackgroundRepeat'.$responsiveKey.'.label',
                    'vic_help_block' => 'widget_layout.form.containerBackgroundRepeat.help.label',
                    'choices' => array(
                        'no-repeat' => 'widget_layout.form.containerBackgroundRepeat.choices.noRepeat.label',
                        'repeat'    => 'widget_layout.form.containerBackgroundRepeat.choices.repeat.label',
                        'repeat-x'  => 'widget_layout.form.containerBackgroundRepeat.choices.repeatX.label',
                        'repeat-y'  => 'widget_layout.form.containerBackgroundRepeat.choices.repeatY.label',
                    ),
                ))
                ->add('containerBackgroundPosition'.$responsiveKey, 'choice', array(
                    'label' => 'widget_layout.form.containerBackgroundPosition'.$responsiveKey.'.label',
                    'vic_help_block' => 'widget_layout.form.containerBackgroundPosition.help.label',
                    'choices' => array(
                        'center center' => 'widget_layout.form.containerBackgroundRepeat.choices.center.center.label',
                        'center right'  => 'widget_layout.form.containerBackgroundRepeat.choices.center.right.label',
                        'center left'   => 'widget_layout.form.containerBackgroundRepeat.choices.center.left.label',
                        'top center'    => 'widget_layout.form.containerBackgroundRepeat.choices.top.center.label',
                        'top right'     => 'widget_layout.form.containerBackgroundRepeat.choices.top.right.label',
                        'top left'      => 'widget_layout.form.containerBackgroundRepeat.choices.top.left.label',
                        'bottom center' => 'widget_layout.form.containerBackgroundRepeat.choices.bottom.center.label',
                        'bottom right'  => 'widget_layout.form.containerBackgroundRepeat.choices.bottom.right.label',
                        'bottom left'   => 'widget_layout.form.containerBackgroundRepeat.choices.bottom.left.label',
                    ),
                ))
                ->add('containerBackgroundSize'.$responsiveKey, null, array(
                    'label' => 'widget_layout.form.containerBackgroundSize'.$responsiveKey.'.label',
                    'vic_help_block' => 'widget_layout.form.containerBackgroundSize.help.label',
                ))
                ->add('containerBackgroundOverlay'.$responsiveKey, null, array(
                    'label' => 'widget_layout.form.containerBackgroundOverlay'.$responsiveKey.'.label',
                    'vic_help_block' => 'widget_layout.form.containerBackgroundOverlay.help.label',
                ))
            ;
        }
        else {
            $form
                ->remove('containerBackgroundImage'.$responsiveKey)
                ->remove('containerBackgroundRepeat'.$responsiveKey)
                ->remove('containerBackgroundPosition'.$responsiveKey)
                ->remove('containerBackgroundSize'.$responsiveKey)
                ->remove('containerBackgroundOverlay'.$responsiveKey)
                ->add('containerBackgroundColor'.$responsiveKey, null, array(
                    'label' => 'widget_layout.form.containerBackgroundColor'.$responsiveKey.'.label',
                    'vic_help_block' => 'widget_layout.form.containerBackgroundColor.help.label',
                ))
            ;
        }
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
