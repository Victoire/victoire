<?php
namespace Victoire\Bundle\CoreBundle\DataCollector;


use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;
use Victoire\Bundle\WidgetBundle\Entity\Widget;

/**
 * This collector displays a victoire element in toolbar
 */
class VictoireCollector extends DataCollector
{
    protected $cachedWidgets = array();

    /**
     * @param Request         $request
     * @param Response        $response
     * @param \Exception|null $exception
     */
    public function collect(Request $request, Response $response, \Exception $exception = null)
    {
        $this->data = array(
            'cachedWidgetCount' => count($this->cachedWidgets),
        );
    }

    /**
     * Add a widget to the stack of cached widgets
     * @param Widget $widget
     */
    public function addCachedWidget(Widget $widget)
    {
        $this->cachedWidgets[] = $widget;
    }

    /**
     * @return mixed
     */
    public function getCachedWidgetCount()
    {
        return $this->data['cachedWidgetCount'];
    }

    public function getName()
    {
        return 'victoire_core.victoire_collector';
    }

}
