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

    protected $em;

    /**
     * constructor
     * @param EntityManager $em
     */
    public function __construct($em)
    {
        $this->em = $em;
    }

    /**
     * define form fields
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $transformer = new PageToTemplateTransformer($this->em);
        $builder
            ->add('title')
            ->add('slug')
            ->add('template')
            ->add('layout')

            ->addModelTransformer($transformer)
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
                'translation_domain' => 'victoire'
            )
        );
    }


    /**
     * get form name
     */
    public function getName()
    {
        return 'victoire_page_type';
    }
}
