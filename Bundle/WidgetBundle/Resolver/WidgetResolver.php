<?php
/**
 * Created by PhpStorm.
 * User: paulandrieux
 * Date: 17/03/2016
 * Time: 17:28
 */

namespace Victoire\Bundle\WidgetBundle\Resolver;


use Symfony\Component\PropertyAccess\PropertyAccessor;
use Victoire\Bundle\CriteriaBundle\Chain\CriteriaChain;
use Victoire\Bundle\CriteriaBundle\Chain\DataSourceChain;
use Victoire\Bundle\CriteriaBundle\Entity\Criteria;
use Victoire\Bundle\WidgetMapBundle\Entity\WidgetMap;
use Victoire\Bundle\WidgetBundle\Entity\Widget;

class WidgetResolver
{
    const OPERAND_EQUAL = "equal";
    const OPERAND_IN = "in";

    /**
     * @var DataSourceChain
     */
    private $dataSourceChain;

    /**
     * WidgetResolver constructor.
     *
     * @param DataSourceChain $dataSourceChain
     */
    public function __construct(DataSourceChain $dataSourceChain)
    {

        $this->dataSourceChain = $dataSourceChain;
    }

    public function resolve(WidgetMap $widgetMap)
    {

        $widget = null;
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

        return $widget;
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
        }

        return $result;
    }
}
