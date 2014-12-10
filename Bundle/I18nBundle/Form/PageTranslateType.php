<?php
namespace Victoire\Bundle\I18nBundle\Form;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Victoire\Bundle\PageBundle\Entity\PageStatus;
use Victoire\Bundle\PageBundle\Form\PageType;

/**
 * Edit Page Type
 */
class PageTranslateType extends PageType
{

    /**
    * Constructor
    */
    public function __construct($availableLocales, RequestStack $requestStack)
    {
        parent::__construct($availableLocales, $requestStack);
    }

    /**
     * define form fields
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder
            ->remove('layout')
            ->remove('url')
            ->remove('parent')
            ->remove('template');
    }

    /**
     * get form name
     */
    public function getName()
    {
        return 'victoire_page_translate_type';
    }
}
