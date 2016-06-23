<?php
/**
 * Created by PhpStorm.
 * User: paulandrieux
 * Date: 17/03/2016
 * Time: 17:28.
 */
namespace Victoire\Bundle\WidgetBundle\Resolver;

use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;
use Victoire\Bundle\CriteriaBundle\Chain\DataSourceChain;
use Victoire\Bundle\CriteriaBundle\Entity\Criteria;
use Victoire\Bundle\WidgetBundle\Entity\Widget;
use Victoire\Bundle\WidgetMapBundle\Entity\WidgetMap;

class WidgetResolver
{
    const OPERAND_EQUAL = 'equal';
    const OPERAND_IN = 'in';
    const IS_GRANTED = 'is_granted';

    /**
     * @var DataSourceChain
     */
    private $dataSourceChain;

    private $authorizationChecker;

    /**
     * WidgetResolver constructor.
     * @param DataSourceChain $dataSourceChain
     * @param AuthorizationChecker $authorizationChecker
     */
    public function __construct(DataSourceChain $dataSourceChain, AuthorizationChecker $authorizationChecker)
    {
        $this->dataSourceChain = $dataSourceChain;
        $this->authorizationChecker = $authorizationChecker;
    }

    public function resolve(WidgetMap $widgetMap)
    {
        $accessor = new PropertyAccessor();
        //TODO: orderiaze it
        /** @var Widget $widget */
        foreach ($widgetMap->getWidgets() as $widget) {
            /** @var Criteria $criteria */
            foreach ($widget->getCriterias() as $criteria) {
                $value = $this->dataSourceChain->getData($criteria->getName());
                if ($this->assert($value(), $criteria->getOperator(), $criteria->getValue())) {
                    continue;
                } else {
                    continue 2; //try with break
                }
            }

            return $widget;
        }

        return null;
    }

    protected function assert($value, $operator, $expected)
    {
        $result = false;
        switch ($operator) {
            case self::OPERAND_EQUAL:
                $result = $value === $expected;
                break;
            case self::OPERAND_IN:
                $result = in_array($value, unserialize($expected));
                break;
            case self::IS_GRANTED:
                $result = $this->authorizationChecker->isGranted($expected);
                break;
        }

        return $result;
    }
}
