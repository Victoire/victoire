<?php

namespace Victoire\Bundle\I18nBundle\Listener;

use Victoire\Bundle\TemplateBundle\Listener\TemplateMenuListener;
use Victoire\Bundle\CoreBundle\Menu\MenuBuilder;

class I18nTemplateMenuListener extends TemplateMenuListener
{

    /**
     * {@inheritDoc}
     */
    public function __construct(MenuBuilder $menuBuilder)
    {
        parent::__construct($menuBuilder);
    }

    /**
     * This method is call to replace the base contextual TemplateMenuListener to add a new item in the menu when I18n is activated
     *
     * @param TemplateMenuContextualEvent $event
     *
     * @return Ambigous <\Knp\Menu\ItemInterface, NULL>
     */
    public function addContextual($event)
    {
        $mainItem = $this->getMainItem();
        $template = $event->getTemplate();

        $mainItem->addChild('menu.template.i18n.addTranslation',
            array(
                'route' => 'victoire_template_translate',
                'routeParameters' => array('slug' => $template->getSlug())
                )
        )->setLinkAttribute('data-toggle', 'vic-modal');

        return $mainItem;
    }
}
