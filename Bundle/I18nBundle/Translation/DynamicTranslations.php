<?php

namespace Victoire\Bundle\I18nBundle\Translation;

use JMS\TranslationBundle\Model\Message;
use JMS\TranslationBundle\Translation\TranslationContainerInterface;

/**
 *
 * @author Paul Andrieux
 *
 */
class DynamicTranslations implements TranslationContainerInterface
{
    /**
     * Get the translations
     *
     * @return array The translations
     */
    public static function getTranslationMessages()
    {
        return array(
            new Message('menu.page.i18n.addTranslation', 'victoire')
        );
    }
}
