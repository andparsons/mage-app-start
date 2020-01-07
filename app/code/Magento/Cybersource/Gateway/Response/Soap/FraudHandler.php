<?php
namespace Magento\Cybersource\Gateway\Response\Soap;

use Magento\Payment\Model\InfoInterface;
use Magento\Quote\Model\Quote\Payment;

/**
 * Class FraudHandler
 *
 * @deprecated 100.3.3 Starting from Magento 2.3.3 Cybersource payment method core integration is deprecated
 *      in favor of official payment integration available on the marketplace
 */
class FraudHandler extends \Magento\Cybersource\Gateway\Response\FraudHandler
{
    /**
     * Returns risk factors form response
     *
     * @param array $response
     * @return null | string
     */
    protected function getRiskFactors(array $response)
    {
        return isset($response['afsReply']['afsFactorCode'])
            ? $response['afsReply']['afsFactorCode']
            : null;
    }

    /**
     * Returns risk factors form response
     *
     * @param array $response
     * @return null | string
     */
    protected function getRiskScore(array $response)
    {
        return isset($response['afsReply']['afsResult'])
            ? $response['afsReply']['afsResult']
            : null;
    }

    /**
     * Sets payment state
     *
     * @param InfoInterface $payment
     * @return void
     */
    protected function setPaymentState(InfoInterface $payment)
    {
        $payment->setIsTransactionPending(true);
        $payment->setIsFraudDetected(true);
    }
}
