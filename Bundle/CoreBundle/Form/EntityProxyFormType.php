<?php
namespace Victoire\Bundle\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Create an entity proxy for the widget
 *
 * @author Paul Andrieux
 *
 */
class EntityProxyFormType extends AbstractType
{
    /**
     * define form fields
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        //add the link to the business entity instance
        //it depends of the form
        $builder
            ->add($options['entity_name'], 'entity', array(
                'label'       => 'entity_proxy.form.label',
                'required'    => false,
                'empty_value' => 'entity_proxy.form.empty_value',
                'class'       => $options['namespace'],
                'attr'        => array(
                    'class' => 'add_'.$options['entity_name'].'_link picker_entity_select',
                ),
            ));
    }

    /**
     * bind to Menu entity
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class'         => 'Victoire\Bundle\CoreBundle\Entity\EntityProxy',
            'entity_name'        => null,
            'namespace'          => null,
            'widget'             => null,
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
        return 'entity_proxy';
    }
}
