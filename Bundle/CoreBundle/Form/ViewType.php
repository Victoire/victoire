<?php

namespace Victoire\Bundle\CoreBundle\Form;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Victoire\Bundle\TemplateBundle\Entity\Template;

/**
 * Page Type
 */
abstract class ViewType extends AbstractType
{

    /**
     * define form fields
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

            // vérifie si l'objet Product est "nouveau"
            // Si aucune donnée n'est passée au formulaire, la donnée est "null".
            // Ce doit être considéré comme une nouvelle "View"
            if (!$view || null === $view->getId()) {

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
                $form->add('template', null, array(
                    'label'         => 'form.view.type.template.label',
                    'property'      => 'name',
                    'required'      => !$view instanceof Template,
                    'query_builder' => $getAllTemplateWithoutMe,
                ));
            }
        });

        $builder
            ->add('name', null, array(
                'label' => 'form.view.type.name.label'
            ));
    }

}
