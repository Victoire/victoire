<?php
namespace Victoire\Bundle\PageBundle\Helper;

use Victoire\Bundle\PageBundle\Entity\Page;
use Victoire\Bundle\BusinessEntityTemplateBundle\Entity\BusinessEntityTemplate;
use Victoire\Bundle\CoreBundle\Cached\Entity\EntityProxy;
use Victoire\Bundle\BusinessEntityBundle\Converter\ParameterConverter;
use Victoire\Bundle\BusinessEntityBundle\Helper\BusinessEntityHelper;

/**
 *
 * @author Thomas Beaujean
 *
 * ref: victoire_page.page_helper
 */
class PageHelper
{
    protected $parameterConverter = null;
    protected $businessEntityHelper = null;

    protected $pageParameters = array(
        'title',
        'bodyId',
        'bodyClass',
        'slug');

    /**
     * Constructor
     *
     * @param ParameterConverter   $parameterConverter
     * @param BusinessEntityHelper $businessEntityHelper
     */
    public function __construct(ParameterConverter $parameterConverter, BusinessEntityHelper $businessEntityHelper)
    {
        $this->parameterConverter = $parameterConverter;
        $this->businessEntityHelper = $businessEntityHelper;
    }

    /**
     * Create an instance of the business entity template page
     *
     * @param BusinessEntityTemplate $page   The business entity template page
     * @param string                     $url    The new url
     * @param entity                     $entity The entity
     *
     * @return \Victoire\Bundle\PageBundle\Entity\Page
     */
    public function createPageInstanceFromBusinessEntityTemplate(BusinessEntityTemplate $template, $entity)
    {
        //create a new page
        $newPage = new Page();

        $parentPage = $template->getParentPage();

        //set the page parameter by the business entity template page
        $newPage->setParent($parentPage);
        $newPage->setTemplate($template);

        $newPage->setLayout($template->getLayout());

        $newPage->setTitle('');

        $entityProxy = new EntityProxy();
        $entityProxy->setEntity($entity);

        $newPage->setEntityProxy($entityProxy);

        return $newPage;
    }

    /**
     * Generate update the page parameters with the entity
     *
     * @param Page $page
     * @param Entity   $entity
     */
    public function updatePageParametersByEntity(Page $page, $entity)
    {
        //if no entity is provided
        if ($entity === null) {
            //we look for the entity of the page
            if ($page->getEntity() !== null) {
                $entity = $page->getEntity();
            }
        }

        //only if we have an entity instance
        if ($entity !== null) {
            $className = get_class($entity);

            $businessEntity = $this->businessEntityHelper->findByClassname($className);

            if ($businessEntity !== null) {

                $businessProperties = $businessEntity->getBusinessPropertiesByType('seoable');

                //parse the business properties
                foreach ($businessProperties as $businessProperty) {
                    //parse of seo attributes
                    foreach ($this->pageParameters as $pageAttribute) {
                        $string = $this->getEntityAttributeValue($page, $pageAttribute);
                        $updatedString = $this->parameterConverter->setBusinessPropertyInstance($string, $businessProperty, $entity);
                        $this->setEntityAttributeValue($page, $pageAttribute, $updatedString);
                    }
                }
            }
        }
    }

    /**
     * Get the content of an attribute of an entity given
     *
     * @param entity $entity
     * @param strin $functionName
     *
     * @return mixed
     */
    protected function getEntityAttributeValue($entity, $field)
    {
        $functionName = 'get'.ucfirst($field);

        $fieldValue = call_user_func(array($entity, $functionName));

        return $fieldValue;
    }

    /**
     * Update the value of the entity
     *
     * @param Object        $entity
     * @param The attribute $field
     * @param string        $value
     *
     * @return mixed
     */
    protected function setEntityAttributeValue($entity, $field, $value)
    {
        $functionName = 'set'.ucfirst($field);

        call_user_func(array($entity, $functionName), $value);
    }
}
