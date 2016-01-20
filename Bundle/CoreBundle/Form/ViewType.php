<?php

namespace Victoire\Bundle\CoreBundle\Form;

use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\RequestStack;
use Victoire\Bundle\BlogBundle\Entity\ArticleTemplate;
use Victoire\Bundle\BusinessPageBundle\Entity\BusinessTemplate;
use Victoire\Bundle\CoreBundle\Entity\View;
use Victoire\Bundle\TemplateBundle\Entity\Template;

/**
 * Page Type.
 */
abstract class ViewType extends AbstractType
{
    protected $availableLocales;
    protected $currentLocale;
    protected $isNew;

    public function __construct($availableLocales, RequestStack $requestStack)
    {
        $this->availableLocales = $availableLocales;
        $this->currentLocale = $requestStack->getCurrentRequest()->getLocale();
    }

    /**
     * define form fields.
     *
     * @param FormBuilderInterface $builder
     * @param array                $options
     *
     * @return null
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $view = $event->getData();
            $form = $event->getForm();

            $this->isNew = !$view || null === $view->getId();

            // vérifie si l'objet Product est "nouveau"
            // Si aucune donnée n'est passée au formulaire, la donnée est "null".
            // Ce doit être considéré comme une nouvelle "View"
            if ($this->isNew) {
                $getAllTemplateWithoutMe = function (EntityRepository $tr) {
                    return $tr->getAll()->getInstance();
                };
            } else {
                $getAllTemplateWithoutMe = function (EntityRepository $tr) use ($view) {
                    return $tr->getAll()
                        ->getInstance()
                        ->andWhere('template.id != :templateId')
                        ->setParameter('templateId', $view->getId());
                };
            }
            if (!$form->has('template')) {
                $form->add('template', null, [
                    'label'         => 'form.view.type.template.label',
                    'property'      => 'name',
                    'required'      => !$view instanceof Template || $view instanceof BusinessTemplate,
                    'query_builder' => $getAllTemplateWithoutMe,
                ]);
            }
            if (!$form->has('locale') && count($choices = $this->getAvailableLocales($view)) > 1) {
                $data = $view->getLocale() ?: $this->currentLocale;
                $form->add('locale', 'choice', [
                        'expanded' => false,
                        'multiple' => false,
                        'choices'  => $choices,
                        'label'    => 'form.view.type.locale.label',
                        'data'     => $data,
                    ]
                );
            }

            //If view is an Article BEP, we do not allow to choose parent because it will be set automatically
            if (!$view instanceof ArticleTemplate && ClassUtils::getClass($view) != 'Victoire\Bundle\TemplateBundle\Entity\Template') {
                if (!$view || null === $view->getId()) {
                    $getAllPageWithoutMe = function (EntityRepository $repo) {
                        return $repo->getAll()->getInstance();
                    };
                } else {
                    $getAllPageWithoutMe = function (EntityRepository $repo) use ($view) {
                        return $repo->getAll()
                            ->getInstance()
                            ->andWhere('page.id != :pageId')
                            ->setParameter('pageId', $view->getId());
                    };
                }

                $form->add(
                    'parent',
                    null,
                    [
                        'class'         => 'Victoire\Bundle\PageBundle\Entity\BasePage',
                        'label'         => 'form.view.type.parent.label',
                        'query_builder' => $getAllPageWithoutMe,
                        'required'      => true,
                    ]
                );
            }
        });

        $builder
            ->add('name', null, [
                'label' => 'form.view.type.name.label',
            ]);
    }

    protected function getAvailableLocales(View $view)
    {
        $choices = [];
        $i18n = $view->getI18n();

        foreach ($this->availableLocales as $localeVal) {
            $choices[$localeVal] = 'victoire.i18n.viewType.locale.'.$localeVal;
        }

        return $choices;
    }
}
