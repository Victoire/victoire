<?php

namespace Bundle\WidgetMapBundle\Tests\Builder;

use Victoire\Bundle\CoreBundle\Entity\View;
use Victoire\Bundle\PageBundle\Entity\Page;
use Victoire\Bundle\TemplateBundle\Entity\Template;
use Victoire\Bundle\WidgetBundle\Entity\Widget;
use Victoire\Bundle\WidgetMapBundle\Builder\WidgetMapBuilder;
use Victoire\Bundle\WidgetMapBundle\Entity\WidgetMap;
use Victoire\Widget\TextBundle\Entity\WidgetText;

class WidgetMapBuilderTest extends \PHPUnit_Framework_TestCase
{

    public function testBuildPage()
    {
        $builder = new WidgetMapBuilder();
        $view = new Page();
        $widget3 = $this->newWidgetMap(3, null, null, $view, $this->newWidget(3));
        $widget2 = $this->newWidgetMap(2, $widget3, WidgetMap::POSITION_BEFORE, $view, $this->newWidget(2));
        $widget1 = $this->newWidgetMap(1, $widget2, WidgetMap::POSITION_AFTER, $view, $this->newWidget(1));
        $widget4 = $this->newWidgetMap(4, $widget3, WidgetMap::POSITION_AFTER, $view, $this->newWidget(4));

        $builtWidgetMap = $builder->build($view);


        $this->assertEquals(2, $builtWidgetMap['content'][0]->getWidget()->getId());
        $this->assertEquals(1, $builtWidgetMap['content'][1]->getWidget()->getId());
        $this->assertEquals(3, $builtWidgetMap['content'][2]->getWidget()->getId());
        $this->assertEquals(4, $builtWidgetMap['content'][3]->getWidget()->getId());

    }

    public function testBuildPageWithTemplate()
    {
        $builder = new WidgetMapBuilder();
        $template = new Template();
        $view = new Page();
        $view->setTemplate($template);


        $widget3 = $this->newWidgetMap(3, null, null, $template, $this->newWidget(3));
        $widget2 = $this->newWidgetMap(2, $widget3, WidgetMap::POSITION_BEFORE, $view, $this->newWidget(2));
        $widget1 = $this->newWidgetMap(1, $widget2, WidgetMap::POSITION_AFTER, $view, $this->newWidget(1));
        $widget4 = $this->newWidgetMap(4, $widget3, WidgetMap::POSITION_AFTER, $view, $this->newWidget(4));


        $builtWidgetMap = $builder->build($view);

        $this->assertEquals(2, $builtWidgetMap['content'][0]->getWidget()->getId());
        $this->assertEquals(1, $builtWidgetMap['content'][1]->getWidget()->getId());
        $this->assertEquals(3, $builtWidgetMap['content'][2]->getWidget()->getId());
        $this->assertEquals(4, $builtWidgetMap['content'][3]->getWidget()->getId());
    }

    public function testBuildPageWithTemplateTemplate()
    {
        $builder = new WidgetMapBuilder();
        $grandtemplate = new Template();
        $template = new Template();
        $template->setTemplate($grandtemplate);
        $view = new Page();
        $view->setTemplate($template);


        $widget3 = $this->newWidgetMap(3, null, null, $grandtemplate, $this->newWidget(3));
        $widget2 = $this->newWidgetMap(2, $widget3, WidgetMap::POSITION_BEFORE, $template, $this->newWidget(2));
        $widget1 = $this->newWidgetMap(1, $widget2, WidgetMap::POSITION_AFTER, $view, $this->newWidget(1));
        $widget4 = $this->newWidgetMap(4, $widget3, WidgetMap::POSITION_AFTER, $view, $this->newWidget(4));


        $builtWidgetMap = $builder->build($view);
        $this->assertEquals(2, $builtWidgetMap['content'][0]->getWidget()->getId());
        $this->assertEquals(1, $builtWidgetMap['content'][1]->getWidget()->getId());
        $this->assertEquals(3, $builtWidgetMap['content'][2]->getWidget()->getId());
        $this->assertEquals(4, $builtWidgetMap['content'][3]->getWidget()->getId());

    }


    protected function newWidgetMap($id, $parent, $position, View $view, Widget $widget)
    {

        $widgetMap = new WidgetMap();
        $widgetMap->setId($id);
        if ($parent) {
            $widgetMap->setParent($parent);
            $parent->addChild($widgetMap);
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
}
