<?php
namespace Magento\Eway\Observer;

use Magento\Framework\Event\Observer;
use Magento\Payment\Observer\AbstractDataAssignObserver;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;

/**
 * Class DataAssignObserver
 *
 * @deprecated 100.3.3 Starting from Magento 2.3.3 eWay payment method core integration is deprecated
 *      in favor of official payment integration available on the marketplace
 */
class DataAssignObserver extends AbstractDataAssignObserver
{
    const CC_NUMBER = 'cc_number';
    const CC_CID = 'cc_cid';

    /**
     * @var array
     */
    protected $additionalInformationList = [
        self::CC_NUMBER,
        self::CC_CID,
        OrderPaymentInterface::CC_TYPE,
        OrderPaymentInterface::CC_EXP_MONTH,
        OrderPaymentInterface::CC_EXP_YEAR,
    ];

    /**
     * Updates additional payment information.
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $data = $this->readDataArgument($observer);

        $additionalData = $data->getData(PaymentInterface::KEY_ADDITIONAL_DATA);
        if (!is_array($additionalData)) {
            return;
        }

        $paymentInfo = $this->readPaymentModelArgument($observer);

        foreach ($this->additionalInformationList as $additionalInformationKey) {
            $value = isset($additionalData[$additionalInformationKey])
                ? $additionalData[$additionalInformationKey]
                : null;

            if ($value === null) {
                continue;
            }

            $paymentInfo->setAdditionalInformation(
                $additionalInformationKey,
                $value
            );

            // CC data should be stored explicitly
            $paymentInfo->setData(
                $additionalInformationKey,
                $value
            );
        }
    }
}
