<?php

namespace Magento\CompanyCredit\Plugin\Sales\Model;

use Magento\Sales\Api\Data\OrderStatusHistoryInterface;
use Magento\CompanyCredit\Model\CompanyCreditPaymentConfigProvider;

/**
 * Add comment for order history.
 */
class OrderPlugin
{
    /**
     * Around addStatusHistoryComment.
     *
     * @param \Magento\Sales\Model\Order $subject
     * @param \Closure $proceed
     * @param string $comment
     * @param bool|string $status [optional]
     * @return OrderStatusHistoryInterface
     */
    public function aroundAddStatusHistoryComment(
        \Magento\Sales\Model\Order $subject,
        $proceed,
        $comment,
        $status = false
    ) {
        if (CompanyCreditPaymentConfigProvider::METHOD_NAME == $subject->getPayment()->getMethod() &&
            null !== $subject->getPayment()->getCreditmemo()) {
            $formattedPrice = $subject->getBaseCurrency()->formatTxt(
                $subject->getPayment()->getCreditmemo()->getBaseGrandTotal()
            );
            $patternOnline = (string)__('We refunded %1 online.', $formattedPrice);
            $patternOffline = (string)__('We refunded %1 offline.', $formattedPrice);
            if (strstr($patternOnline, (string)$comment) || strstr($patternOffline, (string)$comment)) {
                $comment = __('We refunded %1 to the company credit.', $formattedPrice);
            }
        }

        $result = $proceed($comment, $status);
        return $result;
    }
}
