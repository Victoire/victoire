<?php
namespace Victoire\Bundle\BlogBundle\Form;

use Symfony\Component\Form\FormBuilderInterface;
use Victoire\Bundle\BlogBundle\Entity\Article;

/**
 * Edit Article Type
 */
class ArticleSettingsType extends ArticleType
{

    /**
     * define form fields
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder
            ->add('slug', null, array(
                'label' => 'form.page.type.slug.label'
            ))
            ->add('status', 'choice', array(
                'label'   => 'form.page.type.status.label',
                'choices' => array(
                    Article::DRAFT       => 'form.page.type.status.choice.label.draft',
                    Article::PUBLISHED   => 'form.page.type.status.choice.label.published',
                    Article::UNPUBLISHED => 'form.page.type.status.choice.label.unpublished',
                    Article::SCHEDULED   => 'form.page.type.status.choice.label.scheduled',
                )
            ))
            ->add('publishedAt', null, array(
                'widget'         => 'single_text',
                'datetimepicker' => true
            ))
            ->add('image', 'media');
    }

    /**
     * get form name
     */
    public function getName()
    {
        return 'victoire_article_settings_type';
    }
}
