<?php
namespace Victoire\Bundle\WidgetBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Victoire\Bundle\WidgetBundle\Entity\Widget;

/**
 * Widget Style form type
 */
class WidgetStyleType extends AbstractType
{
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
                'vic_help_block' => 'widget_layout.form.containerMargin'.$key.'.help_block',
                'required' => false,
            ));
            $builder->add('containerPadding'.$key, null, array(
                'label' => 'widget_layout.form.containerPadding'.$key.'.label',
                'vic_help_block' => 'widget_layout.form.containerPadding'.$key.'.help_block',
                'required' => false,
            ));
        }

        $builder->add('containerBackground', null, array(
            'label' => 'widget_layout.form.containerBackground.label',
            'vic_help_block' => 'widget_layout.form.containerBackground.help_block',
            'required' => false,
        ));
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
}
