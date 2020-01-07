<?php
namespace Magento\CompanyCredit\Gateway\Config;

use Magento\Payment\Gateway\Config\ValueHandlerInterface;
use Magento\Payment\Gateway\ConfigInterface;

/**
 * Config payment action, depending on order status.
 */
class PaymentActionValueHandler implements ValueHandlerInterface
{
    /**
     * @var ConfigInterface
     */
    private $configInterface;

    /**
     * @var \Magento\Payment\Gateway\Helper\SubjectReader
     */
    private $subjectReader;

    /**
     * ActiveHandler constructor.
     *
     * @param ConfigInterface $configInterface
     * @param \Magento\Payment\Gateway\Helper\SubjectReader $subjectReader
     */
    public function __construct(
        ConfigInterface $configInterface,
        \Magento\Payment\Gateway\Helper\SubjectReader $subjectReader
    ) {
        $this->configInterface = $configInterface;
        $this->subjectReader = $subjectReader;
    }

    /**
     * Retrieve method configured value.
     *
     * @param array $subject
     * @param int|null $storeId [optional]
     *
     * @return string
     * @throws \InvalidArgumentException
     */
    public function handle(array $subject, $storeId = null)
    {
        if ($this->configInterface->getValue('order_status', $storeId)
            != \Magento\Sales\Model\Order::STATE_PROCESSING
        ) {
            $result = \Magento\CompanyCredit\Model\CompanyCreditPaymentConfigProvider::PAYMENT_ACTION_ORDER;
        } else {
            $result = $this->configInterface->getValue($this->subjectReader->readField($subject), $storeId);
        }
        return $result;
    }
}
