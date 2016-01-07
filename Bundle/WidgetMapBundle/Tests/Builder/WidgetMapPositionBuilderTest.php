<?php

namespace Bundle\WidgetMapBundle\Tests\Builder;

use Doctrine\ORM\EntityManager;
use Victoire\Bundle\CoreBundle\Entity\View;
use Victoire\Bundle\PageBundle\Entity\Page;
use Victoire\Bundle\TemplateBundle\Entity\Template;
use Victoire\Bundle\WidgetMapBundle\Builder\WidgetMapBuilder;
use Victoire\Bundle\WidgetMapBundle\Builder\WidgetMapPositionBuilder;
use Victoire\Bundle\WidgetMapBundle\Entity\WidgetMap;
use Victoire\Widget\TextBundle\Entity\WidgetText;

class WidgetMapPositionBuilderTest extends \PHPUnit_Framework_TestCase
{

    private $prophet;

    public function testGenerateWidgetPosition()
    {
        $builder = new WidgetMapBuilder();
        $view = new Page();
        $widget1 = $this->newWidget(1, 1, 0, $view);
        $widget2 = $this->newWidget(2, 2, 0, $view);
        $widget3 = $this->newWidget(3, 3, 0, $view);

        $builtWidgetMap = $builder->build($view);
print_r($builtWidgetMap);exit;

        $positionReference = 2;
        $widget = new WidgetText();
        $widget->setId(4);

        $widgetMapEntry = new WidgetMap();
        $widgetMapEntry->setAction(WidgetMap::ACTION_CREATE);
        $widgetMapEntry->setWidget($widget);
        $widgetMapEntry->setSlot('content');


        $widgetMapPositionBuilder = new WidgetMapPositionBuilder();
        $em = $this->prophet->prophesize('Doctrine\ORM\EntityManager');
        $widgetRepo = $this->prophet->prophesize('Victoire\Bundle\WidgetBundle\Repository\WidgetRepository');
        $widgetRepo->find($positionReference)->willReturn($widget2);
        $em->getRepository('Victoire\Bundle\WidgetBundle\Entity\Widget')->willReturn($widgetRepo);
        $widgetMapEntry = $widgetMapPositionBuilder->generateWidgetPosition($em->reveal(), $widgetMapEntry, $builtWidgetMap, $positionReference, $view);

        $this->assertEquals(3, $widgetMapEntry->getPosition());
        $this->assertEquals(null, $widgetMapEntry->getPositionReference());
        $this->assertEquals(4, $builtWidgetMap['content'][2]->getWidget()->getId());
        $this->assertEquals(3, $builtWidgetMap['content'][2]->getWidget->getPosition());
        $this->assertEquals(3, $builtWidgetMap['content'][3]->getWidget()->getId());
        $this->assertEquals(4, $builtWidgetMap['content'][3]->getWidget->getPosition());


    }
    protected function newWidget($id, $position, $positionReference, View $view)
    {
        $widget = new WidgetText();
        $widget->setId($id);
        $widgetMap = new WidgetMap();
        $widgetMap->setPosition($position);
        $widgetMap->setPositionReference($positionReference);
        $widgetMap->setAction(WidgetMap::ACTION_CREATE);
        $widgetMap->setWidget($widget);
        $widgetMap->setSlot('content');
        $view->addWidgetMap($widgetMap);

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
