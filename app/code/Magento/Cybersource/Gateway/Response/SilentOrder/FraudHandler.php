<?php
namespace Magento\Cybersource\Gateway\Response\SilentOrder;

use \Magento\Payment\Model\InfoInterface;

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
        return isset($response['score_factors'])
            ? $response['score_factors']
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
        return isset($response['score_score_result'])
            ? $response['score_score_result']
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
        $payment->setIsTransactionPending(false);
        $payment->setIsFraudDetected(true);
    }
}
