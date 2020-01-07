<?php

namespace Magento\CustomerSegment\Model\Segment\Condition\Sales;

class Purchasedquantity extends \Magento\CustomerSegment\Model\Segment\Condition\Sales\Combine
{
    /**
     * Name of condition for displaying as html
     *
     * @var string
     */
    protected $frontConditionName = 'Purchased Quantity';

    /**
     * @param string $operator
     * @param string $value
     * @return \Zend_Db_Expr
     */
    protected function getConditionSql($operator, $value)
    {
        $aggrFunc = $this->getAttribute() == 'total' ? 'SUM' : 'AVG';
        $condition =  $this->getResource()
            ->getConnection()
            ->getCheckSql("{$aggrFunc}(sales_order.total_qty_ordered) {$operator} {$value}", 1, 0);
        return new \Zend_Db_Expr($condition);
    }
}
