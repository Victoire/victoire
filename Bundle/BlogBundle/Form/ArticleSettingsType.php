<?php

namespace Victoire\Bundle\BlogBundle\Form;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Victoire\Bundle\BlogBundle\Entity\Article;

/**
 * Edit Article Type.
 */
class ArticleSettingsType extends ArticleType
{
    /**
     * define form fields.
     *
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder
            ->add('slug', 'slug', [
                'label' => 'form.page.type.slug.label',
            ])
            ->add('status', 'choice', [
                'label'   => 'form.page.type.status.label',
                'choices' => [
                    Article::DRAFT       => 'form.page.type.status.choice.label.draft',
                    Article::PUBLISHED   => 'form.page.type.status.choice.label.published',
                    Article::UNPUBLISHED => 'form.page.type.status.choice.label.unpublished',
                    Article::SCHEDULED   => 'form.page.type.status.choice.label.scheduled',
                ],
                'attr' => [
                    'data-refreshOnChange' => 'true',
                ],
            ])
            ->add('image', 'media');

            // manage conditional related status in preset data
            $builder->get('status')->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
                $data = $event->getData();
                $form = $event->getForm();
                self::manageRelatedStatus($data, $form->getParent());
            });

            // manage conditional related status in pre submit (ajax call to refresh view)
            $builder->get('status')->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
                $form = $event->getForm();
                $data = $event->getData();
                self::manageRelatedStatus($data, $form->getParent());
            });
    }

    /**
     * Add the related  status according to the status field.
     **/
    public static function manageRelatedStatus($status, $form)
    {
        switch ($status) {
            case Article::SCHEDULED:
                $form
                    ->add('publishedAt', null, [
                        'widget'             => 'single_text',
                        'vic_datetimepicker' => true,
                        'label'              => 'form.article.settings.type.publish.label',
                    ]);
                break;
            default:
                $form
                    ->remove('publishedAt');

                break;
        }
    }

    /**
     * get form name.
     */
    public function getName()
    {
        return 'victoire_article_settings_type';
    }
}
