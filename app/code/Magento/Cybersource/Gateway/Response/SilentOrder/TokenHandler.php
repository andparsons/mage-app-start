<?php
namespace Magento\Cybersource\Gateway\Response\SilentOrder;

use Magento\Cybersource\Gateway\Request\SilentOrder\PaymentTokenBuilder;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;

/**
 * Stores token from response.
 *
 * @deprecated 100.3.3 Starting from Magento 2.3.3 Cybersource payment method core integration is deprecated
 *      in favor of official payment integration available on the marketplace
 */
class TokenHandler implements HandlerInterface
{
    /**
     * Request token constant
     */
    private const REQUEST_TOKEN = 'request_token';

    /**
     * Handles response
     *
     * @param array $handlingSubject
     * @param array $response
     * @return void
     * @throws \InvalidArgumentException
     */
    public function handle(array $handlingSubject, array $response)
    {
        if (!isset($response[PaymentTokenBuilder::PAYMENT_TOKEN])) {
            return;
        }

        /** @var PaymentDataObjectInterface $paymentDO */
        $paymentDO = SubjectReader::readPayment($handlingSubject);

        $paymentDO->getPayment()
            ->setAdditionalInformation(
                PaymentTokenBuilder::PAYMENT_TOKEN,
                $response[PaymentTokenBuilder::PAYMENT_TOKEN]
            );

        if (isset($response[TransactionIdHandler::TRANSACTION_ID])) {
            $paymentDO->getPayment()
                ->setAdditionalInformation(
                    TransactionIdHandler::TRANSACTION_ID,
                    $response[TransactionIdHandler::TRANSACTION_ID]
                );
        }

        if (isset($response[self::REQUEST_TOKEN])) {
            $paymentDO->getPayment()
                ->setAdditionalInformation(
                    self::REQUEST_TOKEN,
                    $response[self::REQUEST_TOKEN]
                );
        }
    }
}
