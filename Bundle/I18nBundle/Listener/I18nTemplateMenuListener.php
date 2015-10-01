<?php

namespace Victoire\Bundle\I18nBundle\Listener;

use Victoire\Bundle\CoreBundle\Menu\MenuBuilder;
use Victoire\Bundle\TemplateBundle\Listener\TemplateMenuListener;

class I18nTemplateMenuListener extends TemplateMenuListener
{
    /**
     * {@inheritdoc}
     */
    public function __construct(MenuBuilder $menuBuilder)
    {
        parent::__construct($menuBuilder);
    }

    /**
     * This method is call to replace the base contextual TemplateMenuListener to add a new item in the menu when I18n is activated.
     *
     * @param TemplateMenuContextualEvent $event
     *
     * @return \Knp\Menu\ItemInterface <\Knp\Menu\ItemInterface, NULL>
     */
    public function addContextual($event)
    {
        $mainItem = $this->getMainItem();
        $template = $event->getTemplate();

        $mainItem->addChild('menu.template.i18n.addTranslation',
            [
                'route'           => 'victoire_template_translate',
                'routeParameters' => ['slug' => $template->getSlug()],
                ]
        )->setLinkAttribute('data-toggle', 'vic-modal');

        return $mainItem;
    }
}
