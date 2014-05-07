<?php
namespace Kunstmaan\MediaBundle\Translation;

use JMS\TranslationBundle\Model\Message;
use JMS\TranslationBundle\Translation\TranslationContainerInterface;

class DynamicTranslations implements TranslationContainerInterface
{
    public static function getTranslationMessages()
    {
        return array(
            new Message('menu.media'),
        );
    }
}
