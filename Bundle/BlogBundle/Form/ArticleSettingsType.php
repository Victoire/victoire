<?php

namespace Victoire\Bundle\BlogBundle\Form;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Victoire\Bundle\BlogBundle\Entity\Article;
use Victoire\Bundle\FormBundle\Form\Type\SlugType;
use Victoire\Bundle\MediaBundle\Form\Type\MediaType;

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
            ->add('slug', SlugType::class, [
                'label' => 'form.page.type.slug.label',
            ])
            ->add('status', ChoiceType::class, [
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
            ->add('image', MediaType::class);

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
     * Add the related status according to the status field.
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
}
