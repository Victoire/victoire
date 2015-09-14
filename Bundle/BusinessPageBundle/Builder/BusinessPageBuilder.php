<?php
namespace Victoire\Bundle\BusinessPageBundle\Builder;


use Symfony\Component\PropertyAccess\PropertyAccess;
use Victoire\Bundle\BusinessEntityBundle\Converter\ParameterConverter;
use Victoire\Bundle\BusinessEntityBundle\Helper\BusinessEntityHelper;
use Victoire\Bundle\BusinessPageBundle\Entity\BusinessTemplate;
use Victoire\Bundle\BusinessPageBundle\Entity\VirtualBusinessPage;
use Victoire\Bundle\CoreBundle\Helper\UrlBuilder;
use Victoire\Bundle\CoreBundle\Entity\EntityProxy;

class BusinessPageBuilder
{

    protected $businessEntityHelper;
    protected $urlBuilder;
    protected $parameterConverter;

    public function __construct(BusinessEntityHelper $businessEntityHelper, UrlBuilder $urlBuilder, ParameterConverter $parameterConverter)
    {
        $this->businessEntityHelper = $businessEntityHelper;
        $this->urlBuilder = $urlBuilder;
        $this->parameterConverter = $parameterConverter;

    }

    /**
     * Generate update the page parameters with the entity
     * @param BusinessTemplate $bepPattern
     * @param BusinessEntity                    $entity
     *
     */
    public function generateEntityPageFromPattern(BusinessTemplate $bepPattern, $entity)
    {
        $page = new VirtualBusinessPage();

        $reflect = new \ReflectionClass($bepPattern);
        $patternProperties = $reflect->getProperties();
        $accessor = PropertyAccess::createPropertyAccessor();

        foreach ($patternProperties as $property) {
            if (!in_array($property->getName(), array('id', 'widgetMap', 'slots', 'seo', 'i18n')) && !$property->isStatic()) {
                $value = $accessor->getValue($bepPattern, $property->getName());
                $setMethod = 'set'.ucfirst($property->getName());
                if (method_exists($page, $setMethod)) {
                    $accessor->setValue($page, $property->getName(), $value);
                }
            }
        }

        //find Victoire\Bundle\BusinessEntityBundle\Entity\BusinessEntity object according to the given $entity
        $businessEntity = $this->businessEntityHelper->findByEntityInstance($entity);

        if ($businessEntity !== null) {
            //the business properties usable in a url
            $businessProperties = $this->getBusinessProperties($businessEntity);

            //the url of the page
            $pageUrl = $this->urlBuilder->buildUrl($page);

            $pageName = $page->getName();
            $pageSlug = $page->getSlug();

            //parse the business properties
            foreach ($businessProperties as $businessProperty) {
                $pageUrl = $this->parameterConverter->setBusinessPropertyInstance($pageUrl, $businessProperty, $entity);
                $pageSlug = $this->parameterConverter->setBusinessPropertyInstance($pageSlug, $businessProperty, $entity);
                $pageName = $this->parameterConverter->setBusinessPropertyInstance($pageName, $businessProperty, $entity);
            }

            //Check that all twig variables in pattern url was removed for it's generated BusinessPage
            preg_match_all('/\{\%\s*([^\%\}]*)\s*\%\}|\{\{\s*([^\}\}]*)\s*\}\}/i', $pageUrl, $matches);

            if (count($matches[2])) {
                throw new \Exception(sprintf(
                        'The following identifiers are not defined as well, (%s)
                    you need to add the following lines on your businessEntity properties:
                    <br> <pre>@VIC\BusinessProperty("businessParameter")</pre>',
                        implode($matches[2], ', ')
                    ));
            }

            $entityProxy = new EntityProxy();
            $entityProxy->setEntity($entity, $businessEntity->getName());
            //we update the url of the page
            $page->setUrl($pageUrl);
            $page->setSlug($pageSlug);
            $page->setName($pageName);
            $page->setEntityProxy($entityProxy);
            $page->setTemplate($bepPattern);
            if ($seo = $bepPattern->getSeo()) {
                $pageSeo = clone $seo;
                $page->setSeo($pageSeo);
            }
        }

        return $page;
    }
}
