<?php

namespace Victoire\Bundle\FormBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Type to choose a font thanks to font awesome library.
 */
class FontAwesomePickerType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'options'    => [
                'title'        => false, // Popover title (optional) only if specified in the template
                'selected'     => false, // use this value as the current item and ignore the original
                'defaultValue' => false, // use this value as the current item if input or element value is empty
                'placement'    => 'bottom', // (has some issues with auto and CSS). auto, top, bottom, left, right
                'collision'    => 'none', // If true, the popover will be repositioned to another position when collapses with the window borders
                'animation'    => true, // fade in/out on show/hide ?
                //hide iconpicker automatically when a value is picked. it is ignored if mustAccept is not false and the accept button is visible
                'hideOnSelect'        => false,
                'showFooter'          => false,
                'searchInFooter'      => false, // If true, the search will be added to the footer instead of the title
                'mustAccept'          => false, // only applicable when there's an iconpicker-btn-accept button in the popover footer
                'selectedCustomClass' => 'bg-primary', // Appends this class when to the selected item
                // 'icons' => [], // list of icon classes (declared at the bottom of this script for maintainability)
                'input'       => 'input,.iconpicker-input', // children input selector
                'inputSearch' => true, // use the input as a search box too?
                'container'   => false, //  Appends the popover to a specific element. If not set, the selected element or element parent is used
                'component'   => '.input-group-addon,.iconpicker-component', // children component jQuery selector or object, relative to the container element
                // Plugin 'templates' =>
                'templates' => [
                    'popover'        => '<div class="iconpicker-popover popover"><div class="arrow"></div><div class="popover-title"></div><div class="popover-content"></div></div>',
                    'footer'         => '<div class="popover-footer"></div>',
                    'buttons'        => '<button class="iconpicker-btn iconpicker-btn-cancel btn btn-default btn-sm">Cancel</button><button class="iconpicker-btn iconpicker-btn-accept btn btn-primary btn-sm">Accept</button>',
                    'search'         => '<input type="search" class="form-control iconpicker-search" placeholder="Type to filter" />',
                    'iconpicker'     => '<div class="iconpicker"><div class="iconpicker-items"></div></div>',
                    'iconpickerItem' => '<div role="button" class="iconpicker-item"><i></i></div>',
                ],
            ],
            'data_class' => null,
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->setAttribute('options', $options['options']);
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $defaultOptions = $form->getConfig()->getType()->getOptionsResolver()->resolve();
        $fontAwesomePicker = $options['options'];

        // There is a configuration in form type, merge with default options
        if (!empty($fontAwesomePicker)) {
            $fontAwesomePicker = array_merge($defaultOptions['options'], $fontAwesomePicker);
        } else {
            $fontAwesomePicker = $defaultOptions['options'];
        }

        $view->vars['font_awesome_picker_settings'] = $fontAwesomePicker;
    }

    public function getParent()
    {
        return TextType::class;
    }
}
