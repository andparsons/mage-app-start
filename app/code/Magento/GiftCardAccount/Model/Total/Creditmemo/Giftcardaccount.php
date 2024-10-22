<?php
namespace Magento\GiftCardAccount\Model\Total\Creditmemo;

/**
 * Class for collecting gift card totals on credit memo level
 */
class Giftcardaccount extends \Magento\Sales\Model\Order\Creditmemo\Total\AbstractTotal
{
    /**
     * Collect gift card account totals for credit memo
     *
     * @param \Magento\Sales\Model\Order\Creditmemo $creditmemo
     * @return $this
     */
    public function collect(\Magento\Sales\Model\Order\Creditmemo $creditmemo)
    {
        $order = $creditmemo->getOrder();
        if ($order->getBaseGiftCardsAmount() && $order->getBaseGiftCardsInvoiced() != 0) {
            $gcaLeft = $order->getBaseGiftCardsInvoiced() - $order->getBaseGiftCardsRefunded();
            $creditmemoBaseAmount = $creditmemo->getBaseGrandTotal();
            $orderAmountLeft = $order->getSubtotalInvoiced() - $order->getTotalRefunded() - $creditmemoBaseAmount;

            $used = 0;
            $baseUsed = 0;

            if ($orderAmountLeft < $gcaLeft) {
                if ($gcaLeft >= $creditmemoBaseAmount) {
                    $baseUsed = $creditmemoBaseAmount;
                    $used = $creditmemo->getGrandTotal();

                    $creditmemo->setBaseGrandTotal(0);
                    $creditmemo->setGrandTotal(0);

                    $creditmemo->setAllowZeroGrandTotal(true);
                } else {
                    $baseUsed = $order->getBaseGiftCardsInvoiced() - $order->getBaseGiftCardsRefunded();
                    $used = $order->getGiftCardsInvoiced() - $order->getGiftCardsRefunded();

                    $creditmemo->setBaseGrandTotal($creditmemo->getBaseGrandTotal() - $baseUsed);
                    $creditmemo->setGrandTotal($creditmemo->getGrandTotal() - $used);
                }
            }
            $creditmemo->setBaseGiftCardsAmount($baseUsed);
            $creditmemo->setGiftCardsAmount($used);
        }

        return $this;
    }
}
