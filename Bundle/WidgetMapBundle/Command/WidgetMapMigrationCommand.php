<?php

namespace Victoire\Bundle\WidgetMapBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Victoire\Bundle\CoreBundle\Entity\View;
use Victoire\Bundle\TemplateBundle\Entity\Template;
use Victoire\Bundle\WidgetBundle\Entity\Widget;
use Victoire\Bundle\WidgetMapBundle\Entity\WidgetMap;

class WidgetMapMigrationCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    public function configure()
    {
        parent::configure();

        $this
            ->setName('victoire:widget-map:migrate')
            ->addOption('view', null, InputArgument::OPTIONAL, 'view id', null)
            ->setDescription('persists widget map as a tree ofWidgetMap objects');
    }

    /**
     *
     * Takes each view widgetmap array and convert it to persisted WidgetMaps
     *
     * sort widget in inversed order to generate a reversed tree like:
     *       4
     *     3 ┴
     *   2 ┴
     * 1 ┴
     *
     * Then add the overwrited widgetmaps as:
     *       4
     *     3 ┴ 7
     *   2 ┴ 5
     * 1 ┴   ┴ 6
     *         ┴ 8
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');

        $widgetRepo = $em->getRepository('VictoireWidgetBundle:Widget');
        $widgetMapRepo = $em->getRepository('VictoireWidgetMapBundle:WidgetMap');

        if ($viewId = $input->getOption('view')) {
            $views = [$em->getRepository('VictoireCoreBundle:View')->find($viewId)];
        } else {

        $templateRepo = $em->getRepository('VictoireTemplateBundle:Template');
        $rootTemplates = $templateRepo->getInstance()
            ->where('template.template IS NULL')
            ->getQuery()
            ->getResult();
        $templates = [];
        $recursiveGetTemplates = function ($template) use (&$recursiveGetTemplates, &$templates) {
                array_push($templates, $template);
                foreach ($template->getInheritors() as $template) {
                    if ($template instanceof Template) {
                        $recursiveGetTemplates($template);
                    }
                }
        };

        foreach ($rootTemplates as $rootTemplate) {
            $recursiveGetTemplates($rootTemplate);
        }

        $viewRepo = $em->getRepository('VictoireCoreBundle:View');
        $views = $viewRepo->findAll();

        $views = $templates + $views;
        }

        /** @var View $view */
        foreach ($views as $view) {
            $this->getContainer()->get('victoire_widget_map.builder')->build($view);
            $widgets = [];
            foreach ($view->getWidgets() as $widget) {
                $widgets[$widget->getId()] = $widget;
            }

            $oldWidgetMaps = $view->getWidgetMap();
            if (!empty($oldWidgetMaps)) {

                foreach ($oldWidgetMaps as $slot => $oldWidgetMap) {
                    $widgetMaps = [];
                    usort($oldWidgetMap, function ($a, $b) {
                        if ($b['action'] != $a['action']) {
                            return 0;
                        }

                        return $b['position'] - $a['position'];
                    });

                    usort($oldWidgetMap, function ($a, $b) {

                        if ($b['action'] != WidgetMap::ACTION_CREATE) {
                            return -1;
                        }
                        return 1;
                    });
                    usort($oldWidgetMap, function ($a, $b) {

                        if ($b['positionReference'] != null && $b['positionReference'] == $a['widgetId']) {
                            return -1;
                        }
                        return 1;
                    });

                    var_dump($oldWidgetMap);
                    foreach ($oldWidgetMap as $position => $_oldWidgetMap) {
                        var_dump('replacedWidgetId = '.$_oldWidgetMap['replacedWidgetId']);

                        $widget = $widgetRepo->find($_oldWidgetMap['widgetId']);
                        if (!$widget) {
                            var_dump('widget does not exists');
                            continue;
                        }
                        $widgetMap = new WidgetMap();
                        $referencedWidgetMap = null;
                        var_dump('widget id:');
                        var_dump($_oldWidgetMap['widgetId']);
                        if ($_oldWidgetMap['positionReference']) {
                            var_dump($_oldWidgetMap['positionReference']);
                            $referencedWidget = $widgetRepo->find($_oldWidgetMap['positionReference']);
                            var_dump($referencedWidget->getId());
                            foreach ($view->getBuiltWidgetMap()['content'] as $_wm) {
                                var_dump(['id' => $_wm->getId(), 'widget' => $_wm->getWidget()->getId()]);;
                            }
                            $referencedWidgetMap = $view->getWidgetMapByWidget($referencedWidget);
                            while ($referencedWidgetMap->getChild(WidgetMap::POSITION_AFTER)) {
                                $referencedWidgetMap = $referencedWidgetMap->getChild(WidgetMap::POSITION_AFTER);
                            }
                                $widgetMap->setParent($referencedWidgetMap);
                                $widgetMap->setPosition(WidgetMap::POSITION_AFTER);
                        } else {
                            if ($position == 0) {
                                $widgetMap->setPosition(null);
                                $widgetMap->setParent(null);
                            } else {
                                $widgetMap->setPosition(WidgetMap::POSITION_BEFORE);
                                $widgetMap->setParent(array_slice($widgetMaps, -1)[0]);
                            }
                        }

                        if (WidgetMap::ACTION_OVERWRITE == $_oldWidgetMap['action']) {

                            /** @var Widget $replacedWidget */
                            if ($_oldWidgetMap['replacedWidgetId']) {
                                $replacedWidget = $widgetRepo->find($_oldWidgetMap['replacedWidgetId']);
                                $supplicantWidget = $widgetRepo->find($_oldWidgetMap['widgetId']);
                                $replacedWidgetView = $replacedWidget->getView();
                                $this->getContainer()->get('victoire_widget_map.builder')->build($replacedWidgetView);
                                $replacedWidgetMap = $replacedWidgetView->getWidgetMapByWidget($replacedWidget);
                                // If replaced widgetMap does not exists, this is not an overwrite but a create
                                if ($replacedWidgetMap) {
                                $widgetMap->setReplaced($replacedWidgetMap);

                                var_dump('replace '.$replacedWidget->getId().' by '.$supplicantWidget->getId());
                                $widgetMap->setWidget($supplicantWidget);
                                $widgetMap->setPosition($replacedWidgetMap->getPosition());
                                    var_dump('set parent'.($replacedWidgetMap->getParent() ? $replacedWidgetMap->getParent()->getWidget()->getId() : null));
                                $widgetMap->setParent($replacedWidgetMap->getParent());
                                }


                            } else if ($referencedWidgetMap) {

                                $this->getContainer()->get('victoire_widget_map.manager')->move($view, [
                                    'position' => WidgetMap::POSITION_AFTER,
                                    'slot' => $slot,
                                    'widgetMapReference' => $referencedWidgetMap,
                                    'widgetMap' => $_oldWidgetMap['widgetId']
                                ]);
                            } else {
                                $_oldWidgetMap['action'] = WidgetMap::ACTION_CREATE;
                            }

                        } else if (WidgetMap::ACTION_DELETE == $_oldWidgetMap['action']) {
                            $replacedWidget = $widgetRepo->find($_oldWidgetMap['widgetId']);
                            $widgetMap->setPosition(null);
                            $widgetMap->setParent(null);
                            $deletedWidgetMap = $view->getWidgetMapByWidget($replacedWidget);
                            if ($deletedWidgetMap) {

                            $replacedWidgetView = $replacedWidget->getView();
                            $this->getContainer()->get('victoire_widget_map.builder')->build($replacedWidgetView);
                            $replacedWidgetMap = $replacedWidgetView->getWidgetMapByWidget($replacedWidget);
                            $widgetMap->setReplaced($replacedWidgetMap);

                            $this->getContainer()->get('victoire_widget_map.manager')->moveChildren(
                                $view,
                                $deletedWidgetMap->getChild(WidgetMap::POSITION_BEFORE),
                                $deletedWidgetMap->getChild(WidgetMap::POSITION_AFTER),
                                $deletedWidgetMap->getParent(),
                                $deletedWidgetMap->getPosition()
                            );
                            } else {
                                continue;
                            }
                        }


                        $widgetMap->setAction($_oldWidgetMap['action']);
                        $widgetMap->setWidget($widget);
                        $widgetMap->setAsynchronous($_oldWidgetMap['asynchronous'] ? true : false);
                        $widgetMap->setSlot($slot);
                        var_dump('add widgetMap for widget '.$widgetMap->getWidget()->getId().' to view '.$view->getId());
                        $view->addWidgetMap($widgetMap);
                        $em->persist($widgetMap);
                        $widgetMaps[] = $widgetMap;

                        $this->getContainer()->get('victoire_widget_map.builder')->build($view);

                }
            }
            }
            $em->flush();
        }
    }
}
