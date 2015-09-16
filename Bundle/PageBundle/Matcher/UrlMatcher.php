<?php
namespace Victoire\Bundle\PageBundle\Matcher;

use Victoire\Bundle\PageBundle\Helper\UrlHelper;
use Doctrine\ORM\EntityManager;
use Victoire\Bundle\BusinessPageBundle\Helper\BusinessPageHelper;
use Victoire\Bundle\BusinessEntityBundle\Helper\BusinessEntityHelper;

/**
 * ref: victoire_page.matcher.url_matcher
 */
class UrlMatcher
{
    protected $entityManager = null;
    protected $BusinessTemplateHelper = null;
    protected $businessEntityHelper = null;

    /**
     * Constructor
     * @param EntityManager            $entityManager
     * @param BusinessPageHelper $BusinessTemplateHelper
     * @param BusinessEntityHelper     $businessEntityHelper
     */
    public function __construct(EntityManager $entityManager, BusinessPageHelper $BusinessTemplateHelper, BusinessEntityHelper $businessEntityHelper)
    {
        $this->entityManager = $entityManager;
        $this->BusinessTemplateHelper = $BusinessTemplateHelper;
        $this->businessEntityHelper = $businessEntityHelper;
    }

    /**
     * Get the business entity page pattern instance (an array of a business entity page pattern and an entity)
     * @param string $url
     *
     * @return array of BusinessTemplate and entity
     */
    public function getBusinessPageByUrl($url)
    {
        $BusinessTemplateInstance = null;

        //services
        $manager = $this->entityManager;
        $urlHelper = $this->urlHelper;
        $BusinessTemplateRepository = $manager->getRepository('VictoireBusinessPageBundle:BusinessTemplate');
        $BusinessTemplateHelper = $this->BusinessTemplateHelper;
        $businessEntityHelper = $this->businessEntityHelper;

        //
        $shorterUrl = $url;
        $shorterCount = 0;
        $BusinessTemplate = null;

        $watchDog = 1;

        //until we try to remove all parts
        while ($shorterUrl !== null && $BusinessTemplate === null) {
            //we remove the last part to look for a business entity page pattern
            $shorterUrl = $this->removeLastPart($shorterUrl);
            //the number of time the short has been done
            $shorterCount += 1;

            $searchUrl = $shorterUrl;

            //we add the % for the like query
            for ($i = 0; $i < $shorterCount; $i += 1) {
                $searchUrl .= '/%';
            }

            //we look for a business entity page pattern that looks like this url
            $BusinessTemplate = $BusinessTemplateRepository->findOneByLikeUrl($searchUrl);

            //does a business entity page pattern fit the url
            if ($BusinessTemplate !== null) {
                //we want the identifier
                $positionProperty = $BusinessTemplateHelper->getIdentifierPositionInUrl($BusinessTemplate);

                if ($positionProperty !== null) {

                    $position = $positionProperty['position'];
                    $businessProperty = $positionProperty['businessProperty'];

                    $entityIdentifier = $this->extractPartByPosition($url, $position);
                    //test the entity identifier
                    if ($entityIdentifier === null) {
                        throw new \Exception('The entity identifier could not be retrieved from the url.');
                    }

                    //name of the attribute used to get the entity
                    $attributeName = $businessProperty->getEntityProperty();

                    //get the entity
                    $entity = $businessEntityHelper->getEntityByPageAndBusinessIdentifier($BusinessTemplate, $entityIdentifier, $attributeName);

                    if ($entity === null) {
                        throw new \Exception('The entity with the identifier ['.$entityIdentifier.'] was not found');
                    }

                    //information found
                    $BusinessTemplateInstance = array(
                        'BusinessTemplate' => $BusinessTemplate,
                        'entity'                      => $entity
                    );
                } else {
                    throw new \Exception('The business entity page pattern ['.$BusinessTemplate->getId().'] has no identifier.');
                }
            }

            //the watchdog
            $watchDog += 1;

            if ($watchDog > 200) {
                throw new \Exception('The watchdog has been raised, there might be an infinite loop');
            }
        }

        return $BusinessTemplateInstance;
    }

    /**
     * Remove the last part of the url
     * @param string $url
     *
     * @return string The shorten url
     */
    protected function removeLastPart($url)
    {
        $shortenUrl = null;

        if ($url !== null && $url !== '') {
            // split on the / character
            $keywords = preg_split("/\//", $url);

            //if there are some words, we pop the last
            if (count($keywords) > 0) {
                array_pop($keywords);

                //rebuild the url
                $shortenUrl = implode('/', $keywords);
            }
        }

        return $shortenUrl;
    }

    /**
     * Extract a part of the url
     * @param string  $url
     * @param integer $position
     *
     * @return string The extracted part
     */
    protected function extractPartByPosition($url, $position)
    {
        $part = null;

        if ($url !== null && $url !== '') {
            // split on the / character
            $keywords = preg_split("/\//", $url);
            // preg_match_all('/\{\%\s*([^\%\}]*)\s*\%\}|\{\{\s*([^\}\}]*)\s*\}\}/i', $url, $matches);

            //if there are some words, we pop the last
            if (count($keywords) > 0) {
                //get the part
                $part = $keywords[$position - 1];
            }
        }

        return $part;
    }
}
