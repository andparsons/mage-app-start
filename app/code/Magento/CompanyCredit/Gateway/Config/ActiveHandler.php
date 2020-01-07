<?php
namespace Magento\CompanyCredit\Gateway\Config;

use Magento\Payment\Gateway\Config\ValueHandlerInterface;
use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;

/**
 * Check whether payment method can be used.
 */
class ActiveHandler implements ValueHandlerInterface
{
    /**
     * @var ConfigInterface
     */
    private $configInterface;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var \Magento\Company\Model\CompanyContext
     */
    private $companyContext;

    /**
     * @var \Magento\Payment\Gateway\Helper\SubjectReader
     */
    private $subjectReader;

    /**
     * @var \Magento\Authorization\Model\UserContextInterface
     */
    private $userContext;

    /**
     * @param ConfigInterface $configInterface
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Magento\Company\Model\CompanyContext $companyContext
     * @param \Magento\Payment\Gateway\Helper\SubjectReader $subjectReader
     * @param \Magento\Authorization\Model\UserContextInterface $userContext
     */
    public function __construct(
        ConfigInterface $configInterface,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Company\Model\CompanyContext $companyContext,
        \Magento\Payment\Gateway\Helper\SubjectReader $subjectReader,
        \Magento\Authorization\Model\UserContextInterface $userContext
    ) {
        $this->configInterface = $configInterface;
        $this->customerRepository = $customerRepository;
        $this->companyContext = $companyContext;
        $this->subjectReader = $subjectReader;
        $this->userContext = $userContext;
    }

    /**
     * Retrieve method configured value.
     *
     * @param array $subject
     * @param int|null $storeId [optional]
     *
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \InvalidArgumentException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function handle(array $subject, $storeId = null)
    {
        $configValue = $this->configInterface->getValue($this->subjectReader->readField($subject), $storeId);

        if (in_array($this->userContext->getUserType(), [
            \Magento\Authorization\Model\UserContextInterface::USER_TYPE_ADMIN,
            \Magento\Authorization\Model\UserContextInterface::USER_TYPE_INTEGRATION
            ])
        ) {
            return (bool)$configValue;
        }

        $customerId = $this->companyContext->getCustomerId();
        if ($configValue && $customerId) {
            $customer = $this->getCustomer($subject, $customerId);
            if (!$customer) {
                return false;
            }
            if ($customer->getExtensionAttributes() !== null
                && $customer->getExtensionAttributes()->getCompanyAttributes() !== null
            ) {
                return (bool)$customer->getExtensionAttributes()->getCompanyAttributes()->getStatus();
            }
        }
        return false;
    }

    /**
     * Retrieve customer by customerId, otherwise return false
     *
     * @param int $customerId
     * @return \Magento\Customer\Api\Data\CustomerInterface|bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException If customer with the specified ID does not exist.
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function retrieveCustomer($customerId)
    {
        try {
            return $this->customerRepository->getById($customerId);
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            return false;
        }
    }

    /**
     * Get customer either from Company context or from payment quote.
     *
     * @param array $subject
     * @param int $customerId
     * @return \Magento\Customer\Api\Data\CustomerInterface|bool
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getCustomer(array $subject, $customerId)
    {
        $customer = $this->retrieveCustomer($customerId);
        if (!$customer && !empty($subject['payment']) && $subject['payment'] instanceof PaymentDataObjectInterface) {
            /** @var \Magento\Payment\Gateway\Data\PaymentDataObjectInterface $payment */
            $payment = $subject['payment'];
            /** @var \Magento\Customer\Api\Data\CustomerInterface $customer */
            $customer = $this->retrieveCustomer($payment->getOrder()->getCustomerId());
            if (!$customer) {
                return false;
            }
        }
        return $customer;
    }
}
