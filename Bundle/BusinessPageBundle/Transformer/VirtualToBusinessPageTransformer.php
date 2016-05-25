<?php

namespace Victoire\Bundle\BusinessPageBundle\Transformer;

use Victoire\Bundle\BusinessPageBundle\Entity\BusinessPage;
use Victoire\Bundle\BusinessPageBundle\Entity\VirtualBusinessPage;

class VirtualToBusinessPageTransformer
{
    public function transform(VirtualBusinessPage &$sourceObject)
    {
        $bp = new BusinessPage();

        $sourceReflection = new \ReflectionObject($sourceObject);
        $sourceProperties = $sourceReflection->getProperties();
        foreach ($sourceProperties as $sourceProperty) {
            $sourceProperty->setAccessible(true);
            $name = $sourceProperty->getName();

            $value = $sourceProperty->getValue($sourceObject);
            $method = 'set'.ucfirst($name);
            if (method_exists($bp, $method) && $value !== null) {
                $bp->$method($value);
            }
        }

        foreach ($sourceObject->getTranslations() as $translation) {
            $bp->addTranslation($translation);
        }

        $sourceObject = $bp;
    }
}
