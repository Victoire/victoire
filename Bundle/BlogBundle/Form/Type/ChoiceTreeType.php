<?php

namespace Victoire\Bundle\BlogBundle\Form\Type;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Exception\LogicException;
use Symfony\Component\Form\Extension\Core\EventListener\FixRadioInputListener;
use Symfony\Component\Form\Extension\Core\EventListener\FixCheckboxInputListener;
use Symfony\Component\Form\Extension\Core\EventListener\MergeCollectionListener;
use Symfony\Component\Form\Extension\Core\DataTransformer\ChoiceToValueTransformer;
use Symfony\Component\Form\Extension\Core\DataTransformer\ChoiceToBooleanArrayTransformer;
use Symfony\Component\Form\Extension\Core\DataTransformer\ChoicesToValuesTransformer;
use Symfony\Component\Form\Extension\Core\DataTransformer\ChoicesToBooleanArrayTransformer;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Class ChoiceTreeType based on ChoiceType
 * @package Victoire\Bundle\BlogBundle\Form\Type
 */
class ChoiceTreeType extends ChoiceType
{
    /**
     * Caches created choice lists.
     *
     * @var array
     */
    private $treeChoiceListCache = array();

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if (!$options['choice_list'] && !is_array($options['choices']) && !$options['choices'] instanceof \Traversable) {
            throw new LogicException('Either the option "choices" or "choice_list" must be set.');
        }

        if ($options['expanded']) {
            $preferredViews = $options['choice_list']->getPreferredViews();
            $remainingViews = $options['choice_list']->getRemainingViews();

            // Check if the choices already contain the empty value
            // Only add the empty value option if this is not the case
            if (null !== $options['placeholder'] && 0 === count($options['choice_list']->getChoicesForValues(array('')))) {
                $placeholderView = new TreeChoiceView(null, '', $options['placeholder'], 0);

                // "placeholder" is a reserved index
                $this->addSubForms($builder, array('placeholder' => $placeholderView), $options);
            }

            $this->addSubForms($builder, $preferredViews, $options);
            $this->addSubForms($builder, $remainingViews, $options);

            if ($options['multiple']) {
                $builder->addViewTransformer(new ChoicesToBooleanArrayTransformer($options['choice_list']));
                $builder->addEventSubscriber(new FixCheckboxInputListener($options['choice_list']), 10);
            } else {
                $builder->addViewTransformer(new ChoiceToBooleanArrayTransformer($options['choice_list'], $builder->has('placeholder')));
                $builder->addEventSubscriber(new FixRadioInputListener($options['choice_list'], $builder->has('placeholder')), 10);
            }
        } else {
            if ($options['multiple']) {
                $builder->addViewTransformer(new ChoicesToValuesTransformer($options['choice_list']));
            } else {
                $builder->addViewTransformer(new ChoiceToValueTransformer($options['choice_list']));
            }
        }

        if ($options['multiple'] && $options['by_reference']) {
            // Make sure the collection created during the client->norm
            // transformation is merged back into the original collection
            $builder->addEventSubscriber(new MergeCollectionListener(true, true));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        parent::setDefaultOptions($resolver);

        //set our custom choice list
        $treeChoiceListCache = &$this->treeChoiceListCache;

        $treeChoiceList = function (Options $options) use (&$treeChoiceListCache) {
            // Harden against NULL values (like in EntityType and ModelType)
            $choices = null !== $options['choices'] ? $options['choices'] : array();

            // Reuse existing choice lists in order to increase performance
            $hash = hash('sha256', serialize(array($choices, $options['preferred_choices'])));

            if (!isset($treeChoiceListCache[$hash])) {
                $treeChoiceListCache[$hash] = new TreeChoiceList($choices, $options['preferred_choices']);
            }

            return $treeChoiceListCache[$hash];
        };

        $resolver->setDefaults(array('choice_list' => $treeChoiceList));
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'choice_tree';
    }

    /**
     * Adds the sub fields for an expanded choice field.
     *
     * @param FormBuilderInterface $builder     The form builder.
     * @param array                $choiceViews The choice view objects.
     * @param array                $options     The build options.
     */
    private function addSubForms(FormBuilderInterface $builder, array $choiceViews, array $options)
    {
        foreach ($choiceViews as $i => $choiceView) {

            if (is_array($choiceView)) {
                // Flatten groups
                $this->addSubForms($builder, $choiceView, $options);
            } else {
                $choiceOpts = array(
                    'value' => $choiceView->value,
                    'label' => $choiceView->label,
                    'level' => $choiceView->level,
                    'translation_domain' => $options['translation_domain'],
                    'block_name' => 'entry',
                );

                if ($options['multiple']) {
                    $choiceType = 'checkbox_level';
                    // The user can check 0 or more checkboxes. If required
                    // is true, he is required to check all of them.
                    $choiceOpts['required'] = false;
                } else {
                    $choiceType = 'radio_level';
                }
                $builder->add($i, $choiceType, $choiceOpts);
            }
        }
    }
}
