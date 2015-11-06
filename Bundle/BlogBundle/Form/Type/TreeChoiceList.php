<?php

namespace Victoire\Bundle\BlogBundle\Form\Type;

use Symfony\Component\Form\Exception\InvalidConfigurationException;
use Symfony\Component\Form\Extension\Core\ChoiceList\SimpleChoiceList;
use Symfony\Component\Form\FormConfigBuilder;

class TreeChoiceList extends SimpleChoiceList
{
    protected function addChoices(array &$bucketForPreferred, array &$bucketForRemaining, $choices, array $labels, array $preferredChoices, $level = 0)
    {
        // Add choices to the nested buckets
        if (array_key_exists('label', $choices)) {
            $this->addChoice(
                $bucketForPreferred,
                $bucketForRemaining,
                $choices['value'],
                $choices['label'],
                $preferredChoices,
                $level
            );

            if (count($choices['choice_list']) > 0) {
                $this->addChoices($bucketForPreferred, $bucketForRemaining, $choices['choice_list'], $labels, $preferredChoices, $level + 1);
            }
        } else {
            foreach ($choices as $choice => $label) {
                if (is_array($label)) {
                    $this->addChoices(
                        $bucketForPreferred,
                        $bucketForRemaining,
                        $label,
                        $labels,
                        $preferredChoices,
                        $level
                    );
                } else {
                    $this->addChoice(
                        $bucketForPreferred,
                        $bucketForRemaining,
                        $choice,
                        $label,
                        $preferredChoices,
                        $level
                    );
                }
            }
        }
    }

    /**
     * Adds a new choice.
     *
     * @param array  $bucketForPreferred The bucket where to store the preferred
     *                                   view objects.
     * @param array  $bucketForRemaining The bucket where to store the
     *                                   non-preferred view objects.
     * @param mixed  $choice             The choice to add.
     * @param string $label              The label for the choice.
     * @param array  $preferredChoices   The preferred choices.
     *
     * @throws InvalidConfigurationException If no valid value or index could be created.
     */
    protected function addChoice(array &$bucketForPreferred, array &$bucketForRemaining, $choice, $label, array $preferredChoices, $level = 0)
    {
        $index = $this->createIndex($choice);

        if ('' === $index || null === $index || !FormConfigBuilder::isValidName((string) $index)) {
            throw new InvalidConfigurationException(sprintf('The index "%s" created by the choice list is invalid. It should be a valid, non-empty Form name.', $index));
        }

        $value = $this->createValue($choice);

        if (!is_string($value)) {
            throw new InvalidConfigurationException(sprintf('The value created by the choice list is of type "%s", but should be a string.', gettype($value)));
        }
        $view = new TreeChoiceView($choice, $value, $label, $level);

        $this->choices[$index] = $this->fixChoice($choice);
        $this->values[$index] = $value;

        if ($this->isPreferred($choice, $preferredChoices)) {
            $bucketForPreferred[$index] = $view;
        } else {
            $bucketForRemaining[$index] = $view;
        }
    }
}
