<?php

namespace Victoire\Bundle\WidgetBundle\Resolver;

use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;
use Victoire\Bundle\BusinessPageBundle\Entity\BusinessPage;
use Victoire\Bundle\CoreBundle\Helper\CurrentViewHelper;
use Victoire\Bundle\CriteriaBundle\Chain\DataSourceChain;
use Victoire\Bundle\CriteriaBundle\Entity\Criteria;
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
     * @var CurrentViewHelper
     */
    private $currentViewHelper;

    /**
     * WidgetResolver constructor.
     *
     * @param DataSourceChain      $dataSourceChain
     * @param AuthorizationChecker $authorizationChecker
     * @param CurrentViewHelper    $currentViewHelper
     */
    public function __construct(DataSourceChain $dataSourceChain, AuthorizationChecker $authorizationChecker, CurrentViewHelper $currentViewHelper)
    {
        $this->dataSourceChain = $dataSourceChain;
        $this->authorizationChecker = $authorizationChecker;
        $this->currentViewHelper = $currentViewHelper;
    }

    public function resolve(WidgetMap $widgetMap)
    {
        //TODO: orderize it
        $widgets = $widgetMap->getWidgets();
        // if the widgetmap is linked to no widgets, it seems that it is an overwrite of the position so keep the replaced widgets for display

        if ($widgetMap->getReplaced() && count($widgets) === 0) {
            $widgets = $widgetMap->getReplaced()->getWidgets();
        }
        /* @var \Victoire\Bundle\WidgetBundle\Entity\Widget $widget */
        foreach ($widgets as $_widget) {

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
        $businessEntity = null;
        if ($this->currentViewHelper->getCurrentView() instanceof BusinessPage) {
            $businessEntity = $this->currentViewHelper->getCurrentView()->getEntity();
        }
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
            case self::IS_NOT_GRANTED:
                if (!$this->authorizationChecker->isGranted('ROLE_VICTOIRE')) {
                    $granted = $this->authorizationChecker->isGranted($expected, $businessEntity);
                    if ($operator === self::IS_GRANTED) {
                        $result = $granted;
                    } else {
                        $result = (false === $granted);
                    }
                } else {
                    $result = true;
                }
                break;
        }

        return $result;
    }
}
