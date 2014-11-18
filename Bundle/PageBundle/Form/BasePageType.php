<?php
namespace Victoire\Bundle\PageBundle\Form;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Victoire\Bundle\CoreBundle\Form\ViewType;

/**
 * Page Type
 */
abstract class BasePageType extends ViewType
{

    /*
    * Constructor
    */
    public function __construct($applicationLocales) 
    {
        parent::__construct($applicationLocales);
    }
    
    /**
     * define form fields
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $view = $event->getData();
            $form = $event->getForm();

            // vérifie si l'objet Product est "nouveau"
            // Si aucune donnée n'est passée au formulaire, la donnée est "null".
            // Ce doit être considéré comme une nouvelle "View"
            if (!$view || null === $view->getId()) {
                $getAllPageWithoutMe = function (EntityRepository $bpr) {
                    return $bpr->getAll()->getInstance();
                };
            } else {
                $getAllPageWithoutMe = function (EntityRepository $bpr) use ($view) {
                    return $bpr->getAll()
                        ->getInstance()
                        ->andWhere('page.id != :pageId')
                        ->setParameter('pageId', $view->getId());
                };
            }

            $form->add('parent', null, array(
                'label'         => 'form.view.type.parent.label',
                'query_builder' => $getAllPageWithoutMe,
            ));
        });
    }
}
