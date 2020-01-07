<?php
namespace Magento\CompanyCredit\Gateway\Command;

use Magento\Payment\Gateway\CommandInterface;
use Magento\Payment\Gateway\ConfigInterface;

/**
 * Credit limit payment method command for order sale action, decrease company credit balance by order.
 */
class SaleCommand implements CommandInterface
{
    /**
     * @var ConfigInterface
     */
    private $configInterface;

    /**
     * @var \Magento\CompanyCredit\Model\CreditBalance
     */
    private $creditBalance;

    /**
     * @var \Magento\Company\Api\CompanyManagementInterface
     */
    private $companyManagement;

    /**
     * @var \Magento\Payment\Gateway\Helper\SubjectReader
     */
    private $subjectReader;

    /**
     * @param ConfigInterface $configInterface
     * @param \Magento\CompanyCredit\Model\CreditBalance $creditBalance
     * @param \Magento\Company\Api\CompanyManagementInterface $companyManagement
     * @param \Magento\Payment\Gateway\Helper\SubjectReader $subjectReader
     */
    public function __construct(
        ConfigInterface $configInterface,
        \Magento\CompanyCredit\Model\CreditBalance $creditBalance,
        \Magento\Company\Api\CompanyManagementInterface $companyManagement,
        \Magento\Payment\Gateway\Helper\SubjectReader $subjectReader
    ) {
        $this->configInterface = $configInterface;
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
     * @throws \Magento\Framework\Exception\LocalizedException
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
        if ($this->configInterface->getValue('order_status') != \Magento\Sales\Model\Order::STATE_PROCESSING) {
            $order->setState(\Magento\Sales\Model\Order::STATE_NEW);
            $payment->setSkipOrderProcessing(true);
        }
        $payment->setAdditionalInformation('company_id', $company->getId());
        $payment->setAdditionalInformation('company_name', $company->getCompanyName());
        $this->creditBalance->decreaseBalanceByOrder($order, $payment->getPoNumber());
    }
}
