<?php
namespace Victoire\Bundle\PageBundle\Matcher;

use Victoire\Bundle\PageBundle\Helper\UrlHelper;
use Doctrine\ORM\EntityManager;
use Victoire\Bundle\BusinessEntityTemplateBundle\Helper\BusinessEntityTemplateHelper;
use Victoire\Bundle\BusinessEntityBundle\Helper\BusinessEntityHelper;

/**
 *
 * @author Thomas Beaujean
 * ref: victoire_page.matcher.url_matcher
 */
class UrlMatcher
{
    protected $urlHelper = null;
    protected $entityManager = null;
    protected $businessEntityTemplateHelper = null;
    protected $businessEntityHelper = null;

    /**
     * Constructor
     *
     * @param EntityManager                $entityManager
     * @param UrlHelper                    $urlHelper
     * @param BusinessEntityTemplateHelper $businessEntityTemplateHelper
     * @param BusinessEntityHelper         $businessEntityHelper
     */
    public function __construct(EntityManager $entityManager, UrlHelper $urlHelper, BusinessEntityTemplateHelper $businessEntityTemplateHelper, BusinessEntityHelper $businessEntityHelper)
    {
        $this->entityManager = $entityManager;
        $this->urlHelper = $urlHelper;
        $this->businessEntityTemplateHelper = $businessEntityTemplateHelper;
        $this->businessEntityHelper = $businessEntityHelper;
    }

    /**
     * Get the business entity template instance (an array of a business entity template and an entity)
     *
     * @param string $url
     *
     * @return array of businessEntityTemplate and entity
     */
    public function getBusinessEntityTemplateInstanceByUrl($url)
    {
        $businessEntityTemplateInstance = null;

        //services
        $manager = $this->entityManager;
        $urlHelper = $this->urlHelper;
        $businessEntityTemplateRepository = $manager->getRepository('VictoireBusinessEntityTemplateBundle:BusinessEntityTemplate');
        $businessEntityTemplateHelper = $this->businessEntityTemplateHelper;
        $businessEntityHelper = $this->businessEntityHelper;

        //
        $shorterUrl = $url;
        $shorterCount = 0;
        $businessEntityTemplate = null;

        $watchDog = 1;

        //until we try to remove all parts
        while ($shorterUrl !== null && $businessEntityTemplate === null) {
            //we remove the last part to look for a business entity template
            $shorterUrl = $urlHelper->removeLastPart($shorterUrl);
            //the number of time the short has been done
            $shorterCount += 1;

            $searchUrl = $shorterUrl;

            //we add the % for the like query
            for ($i = 0; $i < $shorterCount; $i += 1) {
                $searchUrl .= '/%';
            }

            //we look for a business entity template that looks like this url
            $businessEntityTemplate = $businessEntityTemplateRepository->findOneByLikeUrl($searchUrl);

            //does a template fit the url
            if ($businessEntityTemplate !== null) {
                //we want the identifier
                $positionProperty = $businessEntityTemplateHelper->getIdentifierPositionInUrl($businessEntityTemplate);

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
                    $entity = $businessEntityHelper->getEntityByPageAndBusinessIdentifier($businessEntityTemplate, $entityIdentifier, $attributeName);

                    if ($entity === null) {
                        throw new \Exception('The entity with the identifier ['.$entityIdentifier.'] was not found');
                    }

                    //information found
                    $businessEntityTemplateInstance = array(
                        'businessEntityTemplate' => $businessEntityTemplate,
                        'entity'                 => $entity
                    );
                } else {
                    throw new \Exception('The template ['.$businessEntityTemplate->getId().'] has no identifier.');
                }
            }

            //the watchdog
            $watchDog += 1;

            if ($watchDog > 200) {
                throw new \Exception('The watchdog has been raised, there might be an infinite loop');
            }
        }

        return $businessEntityTemplateInstance;
    }
}
