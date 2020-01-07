<?php

namespace Magento\NegotiableQuote\Model\Plugin\Quote\Model;

use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\Quote\ShippingAssignment\ShippingAssignmentPersister;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;

/**
 * Class QuotePlugin plugin
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ShippingAssignmentPersisterPlugin
{
    /**
     * Before quote load plugin
     *
     * @param ShippingAssignmentPersister $subject
     * @param \Closure $proceed
     * @param CartInterface $quote
     * @param ShippingAssignmentInterface $shippingAssignment
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundSave(
        ShippingAssignmentPersister $subject,
        \Closure $proceed,
        CartInterface $quote,
        ShippingAssignmentInterface $shippingAssignment
    ) {
        $isActive = $quote->getIsActive();
        $extensionAttributes = $quote->getExtensionAttributes();
        if ($extensionAttributes !== null
            && $extensionAttributes->getNegotiableQuote() !== null
            && $extensionAttributes->getNegotiableQuote()->getIsRegularQuote() !== null) {
            $quote->setIsActive(true);
        }
        $proceed($quote, $shippingAssignment);
        $quote->setIsActive($isActive);
    }
}
