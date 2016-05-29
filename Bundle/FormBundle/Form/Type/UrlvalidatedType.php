<?php

namespace Victoire\Bundle\FormBundle\Form\Type;

use A2lix\TranslationFormBundle\Form\Type\TranslationsFieldsType;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Victoire\Bundle\CoreBundle\Entity\View;
use Victoire\Bundle\CoreBundle\Helper\UrlBuilder;
use Victoire\Bundle\I18nBundle\Entity\ViewTranslation;

/**
 * Form field with some slug validation and domain prefix
 */
class UrlvalidatedType extends AbstractType
{
    /**
     * @var Router
     */
    private $router;

    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $locale = null;
        while ($form->getParent() && !($page = $form->getData()) instanceof View) {
            $form = $form->getParent();
            // when the form is an a2lix_translationsFields, then it's name is the current locale,
            // we store it to generate the link in the good locale
            if ('a2lix_translationsFields' === $form->getConfig()->getType()->getName()) {
                $locale = $form->getName();
            }
        }

        if (!$locale) {
            $locale = $page->getCurrentLocale();
        }

        $url = $this->router->generate('victoire_core_page_show', ['url' => $page->getParent()->getUrl(), '_locale' => $locale], Router::ABSOLUTE_URL);
        $view->vars['base_url'] = $url;

        parent::buildView($view, $form, $options);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
    }

    public function getParent()
    {
        return SlugType::class;
    }
}
