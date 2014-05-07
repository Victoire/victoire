<?php
namespace Victoire\Bundle\PageBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;
use Victoire\Bundle\CoreBundle\Entity\Repository\PageRepository;
use Victoire\Bundle\PageBundle\Entity\Page;

/**
 * Page Type
 */
class PageType extends AbstractType
{

    protected $layouts;

    public function __construct($layouts)
    {
        $this->layouts = $layouts;
    }

    /**
     * define form fields
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', null, array(
                'label' => 'form.page.type.title.label'
            ))
            ->add('parent', null, array(
                'label' => 'form.page.type.parent.label'
            ))
            ->add('layout', 'choice', array(
                'label' => 'form.page.type.layout.label',
                'choices' => $options['layouts']
            ))
            ->add('template', null, array(
                'label' => 'form.page.type.template.label'
            ))
            ->add('bodyId', null, array(
                'label' => 'form.page.type.bodyId.label'
            ))
            ->add('bodyClass', null, array(
                'label' => 'form.page.type.bodyClass.label'
            ));
    }


    /**
     * bind to Page entity
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class'         => 'Victoire\Bundle\PageBundle\Entity\BasePage',
            'translation_domain' => 'victoire',
            'layouts' => $this->layouts,
        ));
    }


    /**
     * get form name
     */
    public function getName()
    {
        return 'victoire_page_type';
    }
}
