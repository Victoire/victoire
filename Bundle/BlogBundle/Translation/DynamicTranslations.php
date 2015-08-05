<?php
namespace Victoire\Bundle\BlogBundle\Translation;

use JMS\TranslationBundle\Model\Message;
use JMS\TranslationBundle\Translation\TranslationContainerInterface;

/**
 *
 */
class DynamicTranslations implements TranslationContainerInterface
{
    /**
     * Get the translations
     *
     * @return multitype:\JMS\TranslationBundle\Model\Message
     */
    public static function getTranslationMessages()
    {
        return array(
            new Message('menu.blog', 'victoire'),
            new Message('menu.blog.settings', 'victoire'),
            new Message('menu.blog.new', 'victoire'),
            new Message('widget_filter.category_filter', 'victoire'),
            new Message('widget_filter.tag_filter', 'victoire'),
            new Message('widget_filter.date_filter', 'victoire'),
        );
    }
}
