<?php
namespace Victoire\Bundle\PageBundle\Matcher;

use Victoire\Bundle\PageBundle\Helper\UrlHelper;
use Doctrine\ORM\EntityManager;
use Victoire\Bundle\BusinessEntityPageBundle\Helper\BusinessEntityPageHelper;
use Victoire\Bundle\BusinessEntityBundle\Helper\BusinessEntityHelper;

/**
 * ref: victoire_page.matcher.url_matcher
 */
class UrlMatcher
{
    protected $urlHelper = null;
    protected $entityManager = null;
    protected $businessEntityPagePatternHelper = null;
    protected $businessEntityHelper = null;

    /**
     * Constructor
     * @param EntityManager            $entityManager
     * @param UrlHelper                $urlHelper
     * @param BusinessEntityPageHelper $businessEntityPagePatternHelper
     * @param BusinessEntityHelper     $businessEntityHelper
     */
    public function __construct(EntityManager $entityManager, UrlHelper $urlHelper, BusinessEntityPageHelper $businessEntityPagePatternHelper, BusinessEntityHelper $businessEntityHelper)
    {
        $this->entityManager = $entityManager;
        $this->urlHelper = $urlHelper;
        $this->businessEntityPagePatternHelper = $businessEntityPagePatternHelper;
        $this->businessEntityHelper = $businessEntityHelper;
    }

    /**
     * Get the business entity page pattern instance (an array of a business entity page pattern and an entity)
     * @param string $url
     *
     * @return array of businessEntityPagePattern and entity
     */
    public function getBusinessEntityPageByUrl($url)
    {
        $businessEntityPagePatternInstance = null;

        //services
        $manager = $this->entityManager;
        $urlHelper = $this->urlHelper;
        $businessEntityPagePatternRepository = $manager->getRepository('VictoireBusinessEntityPageBundle:BusinessEntityPagePattern');
        $businessEntityPagePatternHelper = $this->businessEntityPagePatternHelper;
        $businessEntityHelper = $this->businessEntityHelper;

        //
        $shorterUrl = $url;
        $shorterCount = 0;
        $businessEntityPagePattern = null;

        $watchDog = 1;

        //until we try to remove all parts
        while ($shorterUrl !== null && $businessEntityPagePattern === null) {
            //we remove the last part to look for a business entity page pattern
            $shorterUrl = $urlHelper->removeLastPart($shorterUrl);
            //the number of time the short has been done
            $shorterCount += 1;

            $searchUrl = $shorterUrl;

            //we add the % for the like query
            for ($i = 0; $i < $shorterCount; $i += 1) {
                $searchUrl .= '/%';
            }

            //we look for a business entity page pattern that looks like this url
            $businessEntityPagePattern = $businessEntityPagePatternRepository->findOneByLikeUrl($searchUrl);

            //does a business entity page pattern fit the url
            if ($businessEntityPagePattern !== null) {
                //we want the identifier
                $positionProperty = $businessEntityPagePatternHelper->getIdentifierPositionInUrl($businessEntityPagePattern);

                if ($positionProperty !== null) {

                    $position = $positionProperty['position'];
                    $businessProperty = $positionProperty['businessProperty'];

                    $entityIdentifier = $urlHelper->extractPartByPosition($url, $position);
                    //test the entity identifier
                    if ($entityIdentifier === null) {
                        throw new \Exception('The entity identifier could not be retrieved from the url.');
                    }

                    //name of the attribute used to get the entity
                    $attributeName = $businessProperty->getEntityProperty();

                    //get the entity
                    $entity = $businessEntityHelper->getEntityByPageAndBusinessIdentifier($businessEntityPagePattern, $entityIdentifier, $attributeName);

                    if ($entity === null) {
                        throw new \Exception('The entity with the identifier ['.$entityIdentifier.'] was not found');
                    }

                    //information found
                    $businessEntityPagePatternInstance = array(
                        'businessEntityPagePattern' => $businessEntityPagePattern,
                        'entity'                      => $entity
                    );
                } else {
                    throw new \Exception('The business entity page pattern ['.$businessEntityPagePattern->getId().'] has no identifier.');
                }
            }

            //the watchdog
            $watchDog += 1;

            if ($watchDog > 200) {
                throw new \Exception('The watchdog has been raised, there might be an infinite loop');
            }
        }

        return $businessEntityPagePatternInstance;
    }
}
