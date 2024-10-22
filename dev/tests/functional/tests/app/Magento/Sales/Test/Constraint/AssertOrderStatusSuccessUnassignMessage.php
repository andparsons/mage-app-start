<?php

namespace Magento\Sales\Test\Constraint;

use Magento\Sales\Test\Page\Adminhtml\OrderStatusIndex;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertOrderStatusSuccessUnassignMessage
 * Assert that success message is displayed after order status unassigning
 */
class AssertOrderStatusSuccessUnassignMessage extends AbstractConstraint
{
    /**
     * OrderStatus unassign success message
     */
    const SUCCESS_MESSAGE = 'You have unassigned the order status.';

    /**
     * Assert that success message is displayed after order status unassign
     *
     * @param OrderStatusIndex $orderStatusIndexPage
     * @return void
     */
    public function processAssert(OrderStatusIndex $orderStatusIndexPage)
    {
        \PHPUnit\Framework\Assert::assertEquals(
            self::SUCCESS_MESSAGE,
            $orderStatusIndexPage->getMessagesBlock()->getSuccessMessage()
        );
    }

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function toString()
    {
        return 'Order status success unassign message is present.';
    }
}
