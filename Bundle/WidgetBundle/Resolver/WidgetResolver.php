<?php
/**
 * Created by PhpStorm.
 * User: paulandrieux
 * Date: 17/03/2016
 * Time: 17:28.
 */
namespace Victoire\Bundle\WidgetBundle\Resolver;

use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;
use Victoire\Bundle\CriteriaBundle\Chain\DataSourceChain;
use Victoire\Bundle\CriteriaBundle\Entity\Criteria;
use Victoire\Bundle\WidgetBundle\Entity\Widget;
use Victoire\Bundle\WidgetMapBundle\Entity\WidgetMap;

class WidgetResolver
{
    const OPERAND_EQUAL = 'equal';
    const OPERAND_TRUE = 'true';
    const OPERAND_FALSE = 'false';
    const OPERAND_IN = 'in';
    const IS_GRANTED = 'is_granted';
    const IS_NOT_GRANTED = 'is_not_granted';

    /**
     * @var DataSourceChain
     */
    private $dataSourceChain;

    private $authorizationChecker;

    /**
     * WidgetResolver constructor.
     *
     * @param DataSourceChain      $dataSourceChain
     * @param AuthorizationChecker $authorizationChecker
     */
    public function __construct(DataSourceChain $dataSourceChain, AuthorizationChecker $authorizationChecker)
    {
        $this->dataSourceChain = $dataSourceChain;
        $this->authorizationChecker = $authorizationChecker;
    }

    public function resolve(WidgetMap $widgetMap)
    {
        //TODO: orderiaze it
        /* @var Widget $widget */
        foreach ($widgetMap->getWidgets() as $_widget) {
            /** @var Criteria $criteria */
            foreach ($_widget->getCriterias() as $criteria) {
                $value = $this->dataSourceChain->getData($criteria->getName());
                if (!$this->assert($value(), $criteria->getOperator(), $criteria->getValue())) {
                    continue 2; //try with break
                }
            }

            return $_widget;
        }
    }

    protected function assert($value, $operator, $expected)
    {
        $result = false;
        switch ($operator) {
            case self::OPERAND_EQUAL:
                $result = $value === $expected;
                break;
            case self::OPERAND_TRUE:
                $result = $value == true;
                break;
            case self::OPERAND_FALSE:
                $result = $value == false;
                break;
            case self::OPERAND_IN:
                $result = in_array($value, unserialize($expected));
                break;
            case self::IS_GRANTED:
                $result = $this->authorizationChecker->isGranted($expected);
                break;
            case self::IS_NOT_GRANTED:
                $result = false == $this->authorizationChecker->isGranted($expected);
                break;
        }

        return $result;
    }
}
