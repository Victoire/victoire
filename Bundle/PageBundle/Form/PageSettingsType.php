<?php
namespace Victoire\Bundle\PageBundle\Form;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;
use Victoire\Bundle\CoreBundle\Entity\Repository\PageRepository;
use Victoire\Bundle\PageBundle\Entity\Page;
use Victoire\Bundle\PageBundle\Form\PageType;

/**
 * Edit Page Type
 */
class PageSettingsType extends PageType
{

    /**
     * define form fields
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder
            ->add('slug', null, array(
                'label' => 'form.page.type.slug.label'
            ))
            ->remove('layout');
    }

    /**
     * get form name
     */
    public function getName()
    {
        return 'victoire_page_settings_type';
    }
}

