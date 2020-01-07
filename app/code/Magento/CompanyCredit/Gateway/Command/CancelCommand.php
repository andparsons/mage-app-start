<?php
namespace Magento\CompanyCredit\Gateway\Command;

use Magento\Payment\Gateway\CommandInterface;

/**
 * Credit limit payment method command for order cancellation action, revert credit to company.
 */
class CancelCommand implements CommandInterface
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
     * @var \Magento\Company\Api\CompanyManagementInterface
     */
    private $companyManagement;

    /**
     * @param \Magento\CompanyCredit\Model\CreditBalance $creditBalance
     * @param \Magento\Company\Api\CompanyManagementInterface $companyManagement
     * @param \Magento\Payment\Gateway\Helper\SubjectReader $subjectReader
     */
    public function __construct(
        \Magento\CompanyCredit\Model\CreditBalance $creditBalance,
        \Magento\Company\Api\CompanyManagementInterface $companyManagement,
        \Magento\Payment\Gateway\Helper\SubjectReader $subjectReader
    ) {
        $this->creditBalance = $creditBalance;
        $this->companyManagement = $companyManagement;
        $this->subjectReader = $subjectReader;
    }

    /**
     * Executes command basing on business object.
     *
     * @param array $commandSubject
     * @return void
     * @throws \LogicException
     * @throws \InvalidArgumentException
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(array $commandSubject)
    {
        $paymentDataObject = $this->subjectReader->readPayment($commandSubject);
        $payment = $paymentDataObject->getPayment();
        if (!$payment instanceof \Magento\Sales\Api\Data\OrderPaymentInterface) {
            throw new \LogicException(__('Order Payment should be provided'));
        }
        $order = $payment->getOrder();
        $company = $this->companyManagement->getByCustomerId($order->getCustomerId());
        if ($company && $company->getId()) {
            $isCreditIncreased = $this->creditBalance->cancel($order);

            $amount = $order->getBaseCurrency()->formatTxt($order->getBaseGrandTotal());
            $message = $isCreditIncreased
                ? __('Order is canceled. We reverted %1 to the company credit.', $amount)
                : __('Order is canceled. The order amount is not reverted to the company credit.');
        } else {
            $message = __(
                'Order is cancelled. The order amount is not reverted to the company credit '
                . 'because the company to which this customer belongs does not exist.'
            );
        }
        $order->addStatusHistoryComment(
            $message,
            \Magento\Sales\Model\Order::STATE_CANCELED
        );
    }
}
