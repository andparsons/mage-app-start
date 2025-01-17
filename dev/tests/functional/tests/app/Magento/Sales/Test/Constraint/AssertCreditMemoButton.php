<?php

namespace Magento\Sales\Test\Constraint;

use Magento\Sales\Test\Fixture\OrderInjectable;
use Magento\Sales\Test\Page\Adminhtml\OrderIndex;
use Magento\Sales\Test\Page\Adminhtml\SalesOrderView;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert that 'Credit Memo' button is present on order's page
 */
class AssertCreditMemoButton extends AbstractConstraint
{
    /**
     * Assert that 'Credit Memo' button is present on order's page
     *
     * @param SalesOrderView $salesOrderView
     * @param OrderIndex $orderIndex
     * @param OrderInjectable $order
     * @return void
     */
    public function processAssert(SalesOrderView $salesOrderView, OrderIndex $orderIndex, OrderInjectable $order)
    {
        $orderIndex->open();
        $orderIndex->getSalesOrderGrid()->searchAndOpen(['id' => $order->getId()]);
        \PHPUnit\Framework\Assert::assertTrue(
            $salesOrderView->getPageActions()->isActionButtonVisible('Credit Memo'),
            'Credit memo button is absent on order view page.'
        );
    }

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function toString()
    {
        return 'Credit memo button is present on order view page.';
    }
}
