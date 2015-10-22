<?php

namespace Victoire\Bundle\CoreBundle\EventSubscriber;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Victoire\Bundle\CoreBundle\Builder\ViewCssBuilder;
use Victoire\Bundle\CoreBundle\Entity\View;
use Victoire\Bundle\TemplateBundle\Entity\Template;
use Victoire\Bundle\WidgetBundle\Entity\Widget;
use Victoire\Bundle\WidgetBundle\Repository\WidgetRepository;
use Victoire\Bundle\CoreBundle\Repository\ViewRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\UnitOfWork;

class WidgetSubscriber implements EventSubscriber
{

    private $viewCssBuilder;
    /* @var UnitOfWork $uow */
    private $uow;
    /* @var EntityManager $em */
    private $em;
    /* @var WidgetRepository $widgetRepo */
    private $widgetRepo;
    /* @var ViewRepository $viewRepo */
    private $viewRepo;

    /**
     * Construct.
     *
     * @param ViewCssBuilder $viewCssBuilder
     */
    public function __construct(ViewCssBuilder $viewCssBuilder)
    {
        $this->viewCssBuilder = $viewCssBuilder;
    }

    /**
     * Get SubscribedEvents.
     *
     * @return array
     */
    public function getSubscribedEvents()
    {
        return array(
            'onFlush'
        );
    }

    /**
     * Change cssHash of views when a widget is updated or deleted.
     *
     * @param OnFlushEventArgs $args
     */
    public function onFlush(OnFlushEventArgs $args)
    {
        $this->em = $args->getEntityManager();
        $this->uow = $this->em->getUnitOfWork();
        $this->widgetRepo = $this->em->getRepository('Victoire\Bundle\WidgetBundle\Entity\Widget');
        $this->viewRepo = $this->em->getRepository('Victoire\Bundle\CoreBundle\Entity\View');

        $updatedEntities = $this->uow->getScheduledEntityUpdates();
        $deletedEntities = $this->uow->getScheduledEntityDeletions();

        //Update CSS of this widget View and its inheritors if this View is a Template
        foreach (array_merge($updatedEntities, $deletedEntities) as $entity) {

            if (!($entity instanceof Widget)) {
                continue;
            }

            $view = $entity->getView();
            $this->updateViewCss($view);
            $this->updateTemplateInheritorsCss($view);

        }

        //Remove CSS of this widget View and update its inheritors if this View is a Template
        foreach ($deletedEntities as $entity) {

            if (!($entity instanceof View)) {
                continue;
            }

            $this->viewCssBuilder->removeCssFile($entity->getCssHash());
            $this->updateTemplateInheritorsCss($entity);

        }
    }

    /**
     * Change view cssHash, update css file and persist new cssHash
     *
     * @param View $view
     */
    public function updateViewCss(View $view)
    {
        $oldHash = $view->getCssHash();
        $view->changeCssHash();

        //Update css file
        $widgets = $this->widgetRepo->findAllWidgetsForView($view);
        $this->viewCssBuilder->updateViewCss($oldHash, $view, $widgets);

        //Update hash in database
        $metadata = $this->em->getClassMetadata(get_class($view));
        $this->uow->recomputeSingleEntityChangeSet($metadata, $view);
    }

    /**
     * Update a Template inheritors (View) if necessary
     *
     * @param View $view
     */
    public function updateTemplateInheritorsCss(View $view)
    {
        if(!($view instanceof Template)) {
            return;
        }
        foreach($view->getInheritors() as $inheritor) {
            $this->updateViewCss($inheritor);
            $this->updateTemplateInheritorsCss($inheritor);
        }
    }

}