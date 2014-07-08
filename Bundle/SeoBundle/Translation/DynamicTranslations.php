<?php

namespace Victoire\Bundle\SeoBundle\Translation;

use JMS\TranslationBundle\Model\Message;
use JMS\TranslationBundle\Translation\TranslationContainerInterface;
use JMS\TranslationBundle\Model\FileSource;

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
            new Message('menu.page.seoSettings', 'victoire'),
            new Message('form.pageSeo.ogTitle.help_block', 'victoire'),
            new Message('form.pageSeo.twitterCard.help_block', 'victoire'),
            new Message('form.pageSeo.redirectTo.help_block', 'victoire')
        );
    }
}
