<?php
namespace Victoire\Bundle\I18nBundle\Form;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Victoire\Bundle\CoreBundle\Entity\View;

/**
 * Edit Page Type
 */
class PageTranslateType extends AbstractType
{

    protected $availableLocales;
    protected $currentLocale;
    /**
    * Constructor
    */
    public function __construct($availableLocales, RequestStack $requestStack)
    {
        $this->availableLocales = $availableLocales;
        $this->currentLocale = $requestStack->getCurrentRequest()->getLocale();
    }

    /**
     * define form fields
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {     
        $builder
            ->add('name');

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $view = $event->getData();
            $form = $event->getForm();

             if (!$form->has('locale')) {
                $form->add('locale', 'choice', array(
                        'expanded' => false,
                        'multiple' => false,
                        'choices'  => $this->getAvailableLocales($view),
                        'label'    => 'form.view.type.locale.label',
                        'data'     => $this->currentLocale
                    )
                );
            }
        });
    }

    /**
     * get form name
     */
    public function getName()
    {
        return 'victoire_view_translate_type';
    }

    protected function getAvailableLocales(View $view)
    {
        $choices = array();
        $i18n = $view->getI18n();

        foreach ($this->availableLocales as $localeVal) {
            if ($i18n->getTranslation($localeVal) === null ) {
                $choices[$localeVal] = 'victoire.i18n.viewType.locale.'.$localeVal;
            }
        }

        return $choices;
    }
}

