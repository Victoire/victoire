<?php

namespace Bundle\WidgetMapBundle\Tests\Manager;

use Victoire\Bundle\CoreBundle\Entity\View;
use Victoire\Bundle\PageBundle\Entity\Page;
use Victoire\Bundle\WidgetBundle\Entity\Widget;
use Victoire\Bundle\WidgetMapBundle\Builder\WidgetMapBuilder;
use Victoire\Bundle\WidgetMapBundle\Entity\WidgetMap;
use Victoire\Bundle\WidgetMapBundle\Manager\WidgetMapManager;
use Victoire\Widget\TextBundle\Entity\WidgetText;

class WidgetMapManagerTest extends \PHPUnit_Framework_TestCase
{
    private $prophet;

    public function testMove()
    {
        $builder = new WidgetMapBuilder();
        $view = new Page();
        $widgetMap3 = $this->newWidgetMap(3, null, null, $view, $this->newWidget(3));
        $widgetMap2 = $this->newWidgetMap(2, $widgetMap3, WidgetMap::POSITION_BEFORE, $view, $this->newWidget(2));
        $widgetMap1 = $this->newWidgetMap(1, $widgetMap2, WidgetMap::POSITION_AFTER, $view, $this->newWidget(1));
        $widgetMap4 = $this->newWidgetMap(4, $widgetMap3, WidgetMap::POSITION_AFTER, $view, $this->newWidget(4));

        $em = $this->prophet->prophesize('Doctrine\ORM\EntityManager');
        $widgetMapRepo = $this->prophet->prophesize('Doctrine\ORM\EntityRepository');

        $em->getRepository('VictoireWidgetMapBundle:WidgetMap')->willReturn($widgetMapRepo);

        $widgetMapRepo->find(1)->willReturn($widgetMap1);
        $widgetMapRepo->find(2)->willReturn($widgetMap2);
        $widgetMapRepo->find(3)->willReturn($widgetMap3);
        $widgetMapRepo->find(4)->willReturn($widgetMap4);

        $manager = new WidgetMapManager($em->reveal(), $builder);

        $builtWidgetMap = $builder->build($view);

        $order = [2, 1, 3, 4];
        $i = 0;
        foreach ($builtWidgetMap['content'] as $widgetMap) {
            $this->assertEquals($order[$i++], $widgetMap->getWidget()->getId());
        }

        $this->moveWidgetMap($builtWidgetMap, $order, $view, $manager, $builder);
    }

    /**
     * @param integer[] $order
     * @param Page $view
     * @param WidgetMapManager $manager
     * @param WidgetMapBuilder $builder
     */
    protected function moveWidgetMap($builtWidgetMap, $order, $view, $manager, $builder)
    {
        $sortedWidget = [
            'widgetMapReference' => null,
            'position'           => null,
            'slot'               => 'content',
            'widgetMap'          => null,
        ];

        for ($i = 1; $i <= 1000; $i++) {
            $buildSortedWidget = function ($builtWidgetMap) use (&$order, &$buildSortedWidget, $view) {

                $sortedWidget['widgetMap'] = $builtWidgetMap['content'][array_rand($builtWidgetMap['content'])];
                $availablePositions = [];
                $positions = [WidgetMap::POSITION_AFTER, WidgetMap::POSITION_BEFORE];
                $shuffled = $builtWidgetMap['content'];
                shuffle($shuffled);
                foreach ($shuffled as $widgetMap) {
                    if ($widgetMap->getId() !== $sortedWidget['widgetMap']->getId()) {
                        foreach ($positions as $position) {
                            if (!$widgetMap->hasChild($position, $view)) {
                                $availablePositions[] = [
                                    'widgetMapReference' => $widgetMap,
                                    'position'           => $position,
                                ];
                                if (array_rand([0, 1]) === 0) {
                                    break;
                                }
                            }
                        }
                    }
                }

                $randomPosition = $availablePositions[array_rand($availablePositions)];
                $offset = array_search(
                        $randomPosition['widgetMapReference']->getWidget()->getId(),
                        $order
                    ) + ($randomPosition['position'] == WidgetMap::POSITION_AFTER ? 1 : 0);
                if (!empty($order[$offset]) && $order[$offset] == $sortedWidget['widgetMap']->getId()) {
                    return $buildSortedWidget($builtWidgetMap);
                }

                $sortedWidget = array_merge($sortedWidget, $randomPosition);

                $order[array_search($sortedWidget['widgetMap']->getWidget()->getId(), $order)] = null;
                $offset = array_search(
                        $sortedWidget['widgetMapReference']->getWidget()->getId(),
                        $order
                    ) + ($sortedWidget['position'] == WidgetMap::POSITION_AFTER ? 1 : 0);
                array_splice($order, $offset, 0, $sortedWidget['widgetMap']->getWidget()->getId());

                unset($order[array_search(null, $order)]);

                $order = array_values($order);
                $sortedWidget['widgetMap'] = $sortedWidget['widgetMap']->getId();
                $sortedWidget['widgetMapReference'] = $sortedWidget['widgetMapReference']->getId();

                return $sortedWidget;

            };

            $sortedWidget = array_merge($sortedWidget, $buildSortedWidget($builtWidgetMap));

            $manager->move($view, $sortedWidget);
            $newBuiltWidgetMap = $builder->build($view);

            $newOrder = [];
            foreach ($newBuiltWidgetMap['content'] as $newWidgetMap) {
                $newOrder[] = $newWidgetMap->getWidget()->getId();
            }

            $this->assertEquals($order, $newOrder,
                sprintf("move widget %s %s widget %s didn't worked at iteration %s",
                    $sortedWidget['widgetMap'], $sortedWidget['position'], $sortedWidget['widgetMapReference'], $i));

            $builtWidgetMap = $newBuiltWidgetMap;
        }
    }

    /**
     * @param integer $id
     * @param null|WidgetMap $parent
     */
    protected function newWidgetMap($id, $parent, $position, View $view, Widget $widget)
    {
        $widgetMap = new WidgetMap();
        $widgetMap->setId($id);
        if ($parent) {
            $widgetMap->setParent($parent);
        }
        $widgetMap->setPosition($position);
        $widgetMap->setWidget($widget);
        $widgetMap->setSlot('content');
        $widgetMap->setAction(WidgetMap::ACTION_CREATE);
        $view->addWidgetMap($widgetMap);

        return $widgetMap;
    }

    /**
     * @param integer $id
     */
    protected function newWidget($id)
    {
        $widget = new WidgetText();
        $widget->setId($id);

        return $widget;
    }

    protected function setup()
    {
        $this->prophet = new \Prophecy\Prophet();
    }

    protected function tearDown()
    {
        $this->prophet->checkPredictions();
    }
}
