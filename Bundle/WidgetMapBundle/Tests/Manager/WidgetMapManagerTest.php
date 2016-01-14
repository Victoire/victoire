<?php

namespace Bundle\WidgetMapBundle\Tests\Manager;

use Prophecy\Argument;
use Victoire\Bundle\CoreBundle\Entity\View;
use Victoire\Bundle\PageBundle\Entity\Page;
use Victoire\Bundle\TemplateBundle\Entity\Template;
use Victoire\Bundle\WidgetBundle\Entity\Widget;
use Victoire\Bundle\WidgetMapBundle\Builder\WidgetMapBuilder;
use Victoire\Bundle\WidgetMapBundle\Entity\WidgetMap;
use Victoire\Bundle\WidgetMapBundle\Manager\WidgetMapManager;
use Victoire\Widget\TextBundle\Entity\WidgetText;

class WidgetMapManagerTest extends \PHPUnit_Framework_TestCase
{

    private $prophet;

    public function testMoveWithGrandTemplate()
    {


        $builder = new WidgetMapBuilder();
        $grandtemplate = new Template();
        $template = new Template();
        $template->setTemplate($grandtemplate);
        $view = new Page();
        $view->setTemplate($template);


        $widgetMap3 = $this->newWidgetMap(3, null, null, $grandtemplate, $this->newWidget(3));
        $widgetMap2 = $this->newWidgetMap(2, $widgetMap3, WidgetMap::POSITION_BEFORE, $view, $this->newWidget(2));
        $widgetMap1 = $this->newWidgetMap(1, $widgetMap2, WidgetMap::POSITION_AFTER, $view, $this->newWidget(1));
        $widgetMap4 = $this->newWidgetMap(4, $widgetMap3, WidgetMap::POSITION_AFTER, $template, $this->newWidget(4));

        $em = $this->prophet->prophesize('Doctrine\ORM\EntityManager');
        $widgetMapRepo = $this->prophet->prophesize('Victoire\Bundle\WidgetMapBundle\Repository\WidgetMapRepository');

        $em->getRepository('VictoireWidgetMapBundle:WidgetMap')->willReturn($widgetMapRepo);

        $em->persist(Argument::type('object'))->will(function($args) use ($widgetMapRepo) {
                $args[0]->setId(hexdec(uniqid()));

                $widgetMapRepo->find($args[0]->getId())->willReturn($args[0]);
            });

        $widgetMapRepo->find(1)->willReturn($widgetMap1);
        $widgetMapRepo->find(2)->willReturn($widgetMap2);
        $widgetMapRepo->find(3)->willReturn($widgetMap3);
        $widgetMapRepo->find(4)->willReturn($widgetMap4);

        $manager = new WidgetMapManager($em->reveal(), $builder);

        $builtWidgetMap = $builder->build($view);

        $this->assertEquals(2, $builtWidgetMap['content'][0]->getWidget()->getId());
        $this->assertEquals(1, $builtWidgetMap['content'][1]->getWidget()->getId());
        $this->assertEquals(3, $builtWidgetMap['content'][2]->getWidget()->getId());
        $this->assertEquals(4, $builtWidgetMap['content'][3]->getWidget()->getId());

        $order = [2,1,3,4];

        $this->withWidgetMap($builtWidgetMap, $order, $view, $manager, $builder);

    }

    public function testMoveWithTemplate()
    {
        $builder = new WidgetMapBuilder();
        $template = new Template();
        $view = new Page();
        $view->setTemplate($template);


        $widgetMap3 = $this->newWidgetMap(3, null, null, $template, $this->newWidget(3));
        $widgetMap2 = $this->newWidgetMap(2, $widgetMap3, WidgetMap::POSITION_BEFORE, $view, $this->newWidget(2));
        $widgetMap1 = $this->newWidgetMap(1, $widgetMap2, WidgetMap::POSITION_AFTER, $view, $this->newWidget(1));
        $widgetMap4 = $this->newWidgetMap(4, $widgetMap3, WidgetMap::POSITION_AFTER, $template, $this->newWidget(4));

        $em = $this->prophet->prophesize('Doctrine\ORM\EntityManager');
        $widgetMapRepo = $this->prophet->prophesize('Victoire\Bundle\WidgetMapBundle\Repository\WidgetMapRepository');

        $em->getRepository('VictoireWidgetMapBundle:WidgetMap')->willReturn($widgetMapRepo);

        $em->persist(Argument::type('object'))->will(function($args) use ($widgetMapRepo) {
                $args[0]->setId(hexdec(bin2hex(openssl_random_pseudo_bytes(4))));

                $widgetMapRepo->find($args[0]->getId())->willReturn($args[0]);
            });

        $widgetMapRepo->find(1)->willReturn($widgetMap1);
        $widgetMapRepo->find(2)->willReturn($widgetMap2);
        $widgetMapRepo->find(3)->willReturn($widgetMap3);
        $widgetMapRepo->find(4)->willReturn($widgetMap4);
        $this->em = $em->reveal();

        $manager = new WidgetMapManager($em->reveal(), $builder);

        $builtWidgetMap = $builder->build($view);

        $builtTemplateWidgetMap = $builder->build($template);

        $this->assertEquals(3, $builtTemplateWidgetMap['content'][0]->getWidget()->getId());
        $this->assertEquals(4, $builtTemplateWidgetMap['content'][1]->getWidget()->getId());


        $this->assertEquals(2, $builtWidgetMap['content'][0]->getWidget()->getId());
        $this->assertEquals(1, $builtWidgetMap['content'][1]->getWidget()->getId());
        $this->assertEquals(3, $builtWidgetMap['content'][2]->getWidget()->getId());
        $this->assertEquals(4, $builtWidgetMap['content'][3]->getWidget()->getId());

        $order = [2,1,3,4];

        $this->withWidgetMap($builtWidgetMap, $order, $view, $manager, $builder);

    }

    public function testMove()
    {

        $builder = new WidgetMapBuilder();
        $view = new Page();
        $widgetMap3 = $this->newWidgetMap(3, null, null, $view, $this->newWidget(3));
        $widgetMap2 = $this->newWidgetMap(2, $widgetMap3, WidgetMap::POSITION_BEFORE, $view, $this->newWidget(2));
        $widgetMap1 = $this->newWidgetMap(1, $widgetMap2, WidgetMap::POSITION_AFTER, $view, $this->newWidget(1));
        $widgetMap4 = $this->newWidgetMap(4, $widgetMap3, WidgetMap::POSITION_AFTER, $view, $this->newWidget(4));

        $em = $this->prophet->prophesize('Doctrine\ORM\EntityManager');
        $widgetMapRepo = $this->prophet->prophesize('Victoire\Bundle\WidgetMapBundle\Repository\WidgetMapRepository');

        $em->getRepository('VictoireWidgetMapBundle:WidgetMap')->willReturn($widgetMapRepo);

        $widgetMapRepo->find(1)->willReturn($widgetMap1);
        $widgetMapRepo->find(2)->willReturn($widgetMap2);
        $widgetMapRepo->find(3)->willReturn($widgetMap3);
        $widgetMapRepo->find(4)->willReturn($widgetMap4);

        $manager = new WidgetMapManager($em->reveal(), $builder);

        $builtWidgetMap = $builder->build($view);

        $this->assertEquals(2, $builtWidgetMap['content'][0]->getWidget()->getId());
        $this->assertEquals(1, $builtWidgetMap['content'][1]->getWidget()->getId());
        $this->assertEquals(3, $builtWidgetMap['content'][2]->getWidget()->getId());
        $this->assertEquals(4, $builtWidgetMap['content'][3]->getWidget()->getId());

        $order = [2,1,3,4];

        $this->withWidgetMap($builtWidgetMap, $order, $view, $manager, $builder);


    }


    protected function withWidgetMap($builtWidgetMap, $order, $view, $manager, $builder)
    {

        $sortedWidget = [
            'widgetMapReference' => null,
            'position' => null,
            'slot' => 'content',
            'widgetMap' => null,
        ];


        for ($i = 1; $i <= 1000; $i++) {

            $sortedWidget['widgetMap'] = $builtWidgetMap['content'][array_rand($builtWidgetMap['content'])];

            $availablePositions = [];
            $positions = [WidgetMap::POSITION_AFTER, WidgetMap::POSITION_BEFORE];
            foreach ($builtWidgetMap['content'] as $widgetMap) {
                if ($widgetMap->getId() !== $sortedWidget['widgetMap']->getId()) {
                    foreach ($positions as $position) {
                        if (!$widgetMap->hasChild($position)) {
                            $availablePositions[] = [
                                'widgetMapReference' => $widgetMap,
                                'position' => $position,
                            ];
                        }
                    }
                }
            }

            $randomPosition = $availablePositions[array_rand($availablePositions)];


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

            $manager->move($view, $sortedWidget);
            $newBuiltWidgetMap = $builder->build($view);


            $newOrder = [];
            foreach ($newBuiltWidgetMap['content'] as $newWidgetMap) {
                $newOrder[] = $newWidgetMap->getWidget()->getId();
            }

            $this->assertEquals($order, $newOrder,
                sprintf("move widget %s %s widget %s didn't worked",
                    $sortedWidget['widgetMap'], $sortedWidget['position'], $sortedWidget['widgetMapReference']));

            $builtWidgetMap = $newBuiltWidgetMap;
        }
    }

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
        $view->addWidgetMap($widgetMap);

        return $widgetMap;
    }
    protected function newWidget($id)
    {
        $widget = new WidgetText();
        $widget->setId($id);

        return $widget;
    }


    protected function setup()
    {
        $this->prophet = new \Prophecy\Prophet;
    }

    protected function tearDown()
    {
        $this->prophet->checkPredictions();
    }
}
