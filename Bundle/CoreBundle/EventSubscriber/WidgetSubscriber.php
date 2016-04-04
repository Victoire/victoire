<?php

namespace Victoire\Bundle\CoreBundle\EventSubscriber;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\UnitOfWork;
use Victoire\Bundle\CoreBundle\Builder\ViewCssBuilder;
use Victoire\Bundle\CoreBundle\Entity\View;
use Victoire\Bundle\CoreBundle\Repository\ViewRepository;
use Victoire\Bundle\TemplateBundle\Entity\Template;
use Victoire\Bundle\WidgetBundle\Entity\Widget;
use Victoire\Bundle\WidgetBundle\Repository\WidgetRepository;
use Victoire\Bundle\WidgetMapBundle\Builder\WidgetMapBuilder;
use Victoire\Bundle\WidgetMapBundle\Entity\WidgetMap;

class WidgetSubscriber implements EventSubscriber
{
    private $viewCssBuilder;
    private $widgetMapBuilder;
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
    public function __construct(ViewCssBuilder $viewCssBuilder, WidgetMapBuilder $widgetMapBuilder)
    {
        $this->viewCssBuilder = $viewCssBuilder;
        $this->widgetMapBuilder = $widgetMapBuilder;
    }

    /**
     * Get SubscribedEvents.
     *
     * @return array
     */
    public function getSubscribedEvents()
    {
        return [
            'onFlush',
        ];
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

        //Update View's CSS and inheritors of updated and deleted widgets
        foreach (array_merge($updatedEntities, $deletedEntities) as $entity) {
            if (!($entity instanceof Widget)) {
                continue;
            }

            /** @var Widget $entity */
            $widgetMap = $entity->getWidgetMap();
            if ($widgetMap->getAction() !== WidgetMap::ACTION_DELETE) {
                $view = $widgetMap->getView();
                $this->updateViewCss($view);
                $this->setTemplateInheritorsCssToUpdate($view);
            }

        }

        //Remove CSS of deleted View and update its inheritors
        foreach ($deletedEntities as $entity) {
            if (!($entity instanceof View)) {
                continue;
            }

            $this->viewCssBuilder->removeCssFile($entity->getCssHash());
            $this->setTemplateInheritorsCssToUpdate($entity);
        }

        //Update CSS of updated View and its inheritors
        foreach ($updatedEntities as $entity) {
            if (!($entity instanceof View)) {
                continue;
            }

            $this->updateViewCss($entity);
            $this->setTemplateInheritorsCssToUpdate($entity);
        }

        $this->uow->computeChangeSets();
    }

    /**
     * Change view cssHash, update css file and persist new cssHash.
     *
     * @param View $view
     */
    public function updateViewCss(View $view)
    {
        $view->changeCssHash();

        //Update css file
        $this->widgetMapBuilder->build($view, $this->em, true);
        $widgets = $this->widgetRepo->findAllWidgetsForView($view);

        //Generate CSS file and set View's CSS as up to date
        $oldHash = $view->getCssHash();
        $view->changeCssHash();
        $this->viewCssBuilder->updateViewCss($oldHash, $view, $widgets);
        $view->setCssUpToDate(true);

        //Persist new hash and upToDate bool
        $metadata = $this->em->getClassMetadata(get_class($view));
        $this->uow->recomputeSingleEntityChangeSet($metadata, $view);
    }

    /**
     * Set a Template inheritors (View) as not up to date.
     *
     * @param View $view
     */
    public function setTemplateInheritorsCssToUpdate(View $view)
    {
        if (!($view instanceof Template)) {
            return;
        }
        foreach ($view->getInheritors() as $inheritor) {
            $inheritor->setCssUpToDate(false);
            $this->uow->persist($inheritor);
            $this->setTemplateInheritorsCssToUpdate($inheritor);
        }
    }
}
