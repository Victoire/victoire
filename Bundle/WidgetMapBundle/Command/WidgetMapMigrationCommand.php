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
use Victoire\Bundle\WidgetMapBundle\Helper\WidgetMapHelper;

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
     * Takes each view widgetmap array and convert it to persisted WidgetMaps.
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

            $pageRepo = $em->getRepository('VictoirePageBundle:BasePage');
            $pages = $pageRepo->findAll();
            $errorRepo = $em->getRepository('VictoireTwigBundle:ErrorPage');
            $errorPages = $errorRepo->findAll();

            $views = array_merge($templates, array_merge($pages, $errorPages));
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
                        if ($b['position'] - $a['position'] == 0) {
                            return 1;
                        }

                        return $b['position'] - $a['position'];
                    });

                    foreach ($oldWidgetMap as $key => $item) {
                        if ($item['action'] !== 'create') {
                            unset($oldWidgetMap[$key]);
                            $oldWidgetMap[] = $item;
                        }
                    }

                    foreach ($oldWidgetMap as $key => $item) {
                        if ($item['positionReference'] != null) {
                            foreach ($oldWidgetMap as $_key => $_item) {
                                if ($_item['widgetId'] == $item['positionReference']) {
                                    array_splice($oldWidgetMap[$_key], 0, 0, [$item]);
                                    unset($oldWidgetMap[$key]);
                                }
                            }
                        }
                    }

                    foreach ($oldWidgetMap as $position => $_oldWidgetMap) {
                        $output->writeln('==========================');
                        $output->writeln($slot);

                        $widget = $widgetRepo->find($_oldWidgetMap['widgetId']);
                        if (!$widget) {
                            $output->writeln('widget does not exists');
                            continue;
                        }
                        $widgetMap = new WidgetMap();
                        $referencedWidgetMap = null;
                        if ($_oldWidgetMap['positionReference'] != 0 && $_oldWidgetMap['positionReference'] != null) {
                            $output->writeln('has positionReference');
                            $referencedWidget = $widgetRepo->find($_oldWidgetMap['positionReference']);
                            $output->writeln($referencedWidget->getId());
                            $referencedWidgetMap = WidgetMapHelper::getWidgetMapByWidgetAndView($referencedWidget, $view);
                            while ($referencedWidgetMap->getChild(WidgetMap::POSITION_AFTER)) {
                                $referencedWidgetMap = $referencedWidgetMap->getChild(WidgetMap::POSITION_AFTER);
                            }
                            $output->writeln('set parent'.$referencedWidgetMap->getWidget()->getId());
                            $widgetMap->setParent($referencedWidgetMap);
                            $widgetMap->setPosition(WidgetMap::POSITION_AFTER);
                        } else {
                            $output->writeln('has no positionReference');
                            if ($position == 0) {
                                if (!isset($view->getBuiltWidgetMap()[$slot])) {
                                    $widgetMap->setPosition(null);
                                    $output->writeln('set parent'.null);
                                    $widgetMap->setParent(null);
                                } else {
                                    $widgetMap->setPosition(WidgetMap::POSITION_BEFORE);

                                    $_rootBuilt = null;

                                    foreach ($view->getBuiltWidgetMap()[$slot] as $_built) {
                                        if (!$_built->getParent()) {
                                            $_rootBuilt = $_built;
                                            break;
                                        }
                                    }
                                    while (null !== $child = $_rootBuilt->getChild(WidgetMap::POSITION_BEFORE)) {
                                        $_rootBuilt = $_rootBuilt->getChild(WidgetMap::POSITION_BEFORE);
                                    }
                                    $widgetMap->setParent($_rootBuilt);
                                }
                            } else {
                                $widgetMap->setPosition(WidgetMap::POSITION_BEFORE);
                                if (!empty(array_slice($widgetMaps, -1))) {
                                    $widgetMap->setParent(array_slice($widgetMaps, -1)[0]);
                                }
                            }
                        }

                        if (WidgetMap::ACTION_OVERWRITE == $_oldWidgetMap['action']) {
                            $output->writeln('is overwrite');

                            /* @var Widget $replacedWidget */
                            if ($_oldWidgetMap['replacedWidgetId']) {
                                $output->writeln('has replacedWidgetId');
                                $replacedWidget = $widgetRepo->find($_oldWidgetMap['replacedWidgetId']);
                                $supplicantWidget = $widgetRepo->find($_oldWidgetMap['widgetId']);
                                $replacedWidgetView = $replacedWidget->getView();
                                $this->getContainer()->get('victoire_widget_map.builder')->build($replacedWidgetView);
                                $replacedWidgetMap = WidgetMapHelper::getWidgetMapByWidgetAndView($replacedWidget, $replacedWidgetView);
                                // If replaced widgetMap does not exists, this is not an overwrite but a create
                                if ($replacedWidgetMap) {
                                    $output->writeln('has replacedWidgetMap');
                                    $widgetMap->setReplaced($replacedWidgetMap);
                                    $output->writeln('replace '.$replacedWidget->getId().' by '.$supplicantWidget->getId());
                                    $widgetMap->setWidget($supplicantWidget);
                                    $widgetMap->setPosition($replacedWidgetMap->getPosition());
                                    $output->writeln('set parent'.($replacedWidgetMap->getParent() ? $replacedWidgetMap->getParent()->getWidget()->getId() : null));
                                    $widgetMap->setParent($replacedWidgetMap->getParent());
                                }
                            } elseif ($referencedWidgetMap) {
                                $output->writeln('move');

                                $this->getContainer()->get('victoire_widget_map.manager')->move($view, [
                                    'position'           => WidgetMap::POSITION_AFTER,
                                    'slot'               => $slot,
                                    'parentWidgetMap'    => $referencedWidgetMap,
                                    'widgetMap'          => $_oldWidgetMap['widgetId'],
                                ]);
                            } else {
                                $_oldWidgetMap['action'] = WidgetMap::ACTION_CREATE;
                            }
                        } elseif (WidgetMap::ACTION_DELETE == $_oldWidgetMap['action']) {
                            $output->writeln('is delete');
                            $replacedWidget = $widgetRepo->find($_oldWidgetMap['widgetId']);
                            $widgetMap->setPosition(null);
                            $output->writeln('set parent'.null);
                            $widgetMap->setParent(null);
                            $deletedWidgetMap = WidgetMapHelper::getWidgetMapByWidgetAndView($replacedWidget, $view);
                            if ($deletedWidgetMap) {
                                $replacedWidgetView = $replacedWidget->getView();
                                $this->getContainer()->get('victoire_widget_map.builder')->build($replacedWidgetView);
                                $replacedWidgetMap = WidgetMapHelper::getWidgetMapByWidgetAndView($replacedWidget, $replacedWidgetView);
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
                        $output->writeln('add widgetMap for widget '.$widgetMap->getWidget()->getId().' to view '.$view->getId());
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
