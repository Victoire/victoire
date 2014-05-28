<?php

namespace Victoire\Bundle\PageBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;
use Victoire\Bundle\CoreBundle\Entity\Repository\PageRepository;
use Victoire\Bundle\PageBundle\Form\DataTransformer\PageToTemplateTransformer;



/**
 * Template type
 */
class TemplateType extends AbstractType
{

    protected $layouts;

    /**
     * constructor
     * @param EntityManager $layouts
     */
    public function __construct($layouts)
    {
        $this->layouts = $layouts;
    }

    /**
     * define form fields
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('title', null, array(
                'label' => 'form.template.type.title.label'
            ))
            ->add('template', null, array(
                'label' => 'form.template.type.template.label'
            ))
            ->add('layout', 'choice', array(
                'label' => 'form.template.type.layout.label',
                'choices' => $options['layouts']
            ))
            ->add('bodyId', null, array(
                'label' => 'form.template.type.bodyId.label'
            ))
            ->add('bodyClass', null, array(
                'label' => 'form.template.type.bodyClass.label'
            ));

        ;
    }


    /**
     * bind to Template entity
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class'         => 'Victoire\Bundle\PageBundle\Entity\Template',
                'translation_domain' => 'victoire',
                'layouts' => $this->layouts,
            )
        );
    }


    /**
     * get form name
     */
    public function getName()
    {
        return 'victoire_template_type';
    }
}
