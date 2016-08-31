<?php

namespace Victoire\Bundle\BlogBundle\Form;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Victoire\Bundle\FormBundle\Form\Type\SlugType;
use Victoire\Bundle\MediaBundle\Form\Type\MediaType;
use Victoire\Bundle\PageBundle\Entity\PageStatus;

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
            ->add('status', ChoiceType::class, [
                'label'   => 'form.page.type.status.label',
                'choices' => [
                    'form.page.type.status.choice.label.draft'       => PageStatus::DRAFT,
                    'form.page.type.status.choice.label.published'   => PageStatus::PUBLISHED,
                    'form.page.type.status.choice.label.unpublished' => PageStatus::UNPUBLISHED,
                    'form.page.type.status.choice.label.scheduled'   => PageStatus::SCHEDULED,
                ],
                'choices_as_values' => true,
                'attr'              => [
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
            case PageStatus::SCHEDULED:
                $form->add('publishedAt', null, [
                        'widget'             => 'single_text',
                        'vic_datetimepicker' => true,
                        'label'              => 'form.article.settings.type.publish.label',
                    ]);
                break;
            default:
                $form->remove('publishedAt');
                break;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setDefault('validation_groups', ['edition', 'Default']);
    }

}
