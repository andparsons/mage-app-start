<?php
namespace Magento\CompanyCredit\Gateway\Command;

use Magento\Payment\Gateway\CommandInterface;

/**
 * Credit limit payment method command for order refund action, increase the company credit by amount of the refund.
 */
class RefundCommand implements CommandInterface
{
    /**
     * @var \Magento\Payment\Gateway\Helper\SubjectReader
     */
    private $subjectReader;

    /**
     * @var \Magento\CompanyCredit\Model\CreditBalance
     */
    private $creditBalance;

    /**
     * @param \Magento\CompanyCredit\Model\CreditBalance $creditBalance
     * @param \Magento\Payment\Gateway\Helper\SubjectReader $subjectReader
     */
    public function __construct(
        \Magento\CompanyCredit\Model\CreditBalance $creditBalance,
        \Magento\Payment\Gateway\Helper\SubjectReader $subjectReader
    ) {
        $this->creditBalance = $creditBalance;
        $this->subjectReader = $subjectReader;
    }

    /**
     * Executes command basing on business object.
     *
     * @param array $commandSubject
     * @return void
     * @throws \LogicException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(array $commandSubject)
    {
        $paymentDataObject = $this->subjectReader->readPayment($commandSubject);
        $payment = $paymentDataObject->getPayment();
        if (!$payment instanceof \Magento\Sales\Api\Data\OrderPaymentInterface) {
            throw new \LogicException(__('Order Payment should be provided'));
        }
        $this->creditBalance->refund($payment->getOrder(), $payment->getCreditmemo());
    }
}
