<?php

namespace Victoire\Bundle\MediaBundle\Translation;

use JMS\TranslationBundle\Model\Message;
use JMS\TranslationBundle\Translation\TranslationContainerInterface;

class DynamicTranslations implements TranslationContainerInterface
{
    public static function getTranslationMessages()
    {
        return [
            new Message('menu.media'),
        ];
    }
}
