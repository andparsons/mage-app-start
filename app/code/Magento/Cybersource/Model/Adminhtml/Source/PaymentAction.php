<?php
namespace Magento\Cybersource\Model\Adminhtml\Source;

use Magento\Payment\Model\Method\AbstractMethod;

/**
 * Cybersource payment action source.
 *
 * @deprecated 100.3.3 Starting from Magento 2.3.3 Cybersource payment method core integration is deprecated
 *      in favor of official payment integration available on the marketplace
 */
class PaymentAction implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @inheritdoc
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => AbstractMethod::ACTION_AUTHORIZE,
                'label' => __('Authorize'),
            ],
            [
                'value' => AbstractMethod::ACTION_AUTHORIZE_CAPTURE,
                'label' => __('Authorize and Capture'),
            ],
        ];
    }
}
