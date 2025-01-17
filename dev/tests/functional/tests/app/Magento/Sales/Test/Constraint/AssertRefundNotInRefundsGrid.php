<?php

namespace Magento\Sales\Test\Constraint;

use Magento\Sales\Test\Fixture\OrderInjectable;
use Magento\Sales\Test\Page\Adminhtml\CreditMemoIndex;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert that refund is absent in the refunds grid.
 */
class AssertRefundNotInRefundsGrid extends AbstractConstraint
{
    /**
     * Assert that refund is absent in the refunds grid.
     *
     * @param CreditMemoIndex $creditMemoIndex
     * @param OrderInjectable $order
     * @param array $ids
     * @return void
     */
    public function processAssert(CreditMemoIndex $creditMemoIndex, OrderInjectable $order, array $ids)
    {
        $creditMemoIndex->open();
        $amount = $order->getPrice()['refund'];
        $orderId = $order->getId();
        foreach ($ids['creditMemoIds'] as $key => $creditMemoId) {
            $filter = [
                'id' => $creditMemoId,
                'order_id' => $orderId,
                'grand_total_from' => $amount[$key]['grand_creditmemo_total'],
                'grand_total_to' => $amount[$key]['grand_creditmemo_total'],
            ];
            $creditMemoIndex->getCreditMemoGrid()->search($filter);
            $filter['grand_total_from'] = number_format($amount[$key]['grand_creditmemo_total'], 2);
            $filter['grand_total_to'] = number_format($amount[$key]['grand_creditmemo_total'], 2);
            \PHPUnit\Framework\Assert::assertFalse(
                $creditMemoIndex->getCreditMemoGrid()->isRowVisible($filter, false, false),
                "Credit memo '#$creditMemoId' is present in credit memos grid on credit memo index page."
            );
        }
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Credit memo is absent in credit memos grid on credit memo index page.';
    }
}
