<?php
namespace Magento\CompanyCredit\Model;

use Magento\CompanyCredit\Model\CompanyCreditPaymentConfigProvider;

/**
 * This class is used to get company id by order.
 */
class CompanyOrder
{
    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var \Magento\Company\Api\CompanyRepositoryInterface
     */
    private $companyRepository;

    /**
     * @var \Magento\CompanyCredit\Model\CompanyStatus
     */
    private $companyStatus;

    /**
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Magento\Company\Api\CompanyRepositoryInterface $companyRepository
     * @param \Magento\CompanyCredit\Model\CompanyStatus $companyStatus
     */
    public function __construct(
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Company\Api\CompanyRepositoryInterface $companyRepository,
        \Magento\CompanyCredit\Model\CompanyStatus $companyStatus
    ) {
        $this->customerRepository = $customerRepository;
        $this->companyRepository = $companyRepository;
        $this->companyStatus = $companyStatus;
    }

    /**
     * Get company id for refund/revert operation by order.
     *
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @return int|null
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCompanyIdByOrder(\Magento\Sales\Api\Data\OrderInterface $order)
    {
        $companyId = null;
        if ($order->getPayment()->getMethod() == CompanyCreditPaymentConfigProvider::METHOD_NAME) {
            if ($order->getCustomerId()) {
                $customer = $this->customerRepository->getById($order->getCustomerId());
                if ($customer->getExtensionAttributes()
                    && $customer->getExtensionAttributes()->getCompanyAttributes()
                    && $customer->getExtensionAttributes()->getCompanyAttributes()->getCompanyId()
                ) {
                    $companyId = $customer->getExtensionAttributes()->getCompanyAttributes()->getCompanyId();
                }
            } else {
                $companyId = $order->getPayment()->getAdditionalInformation('company_id');
            }
        }
        return $companyId;
    }

    /**
     * Retrieve company id for refund.
     *
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @return int
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCompanyIdForRefund(\Magento\Sales\Api\Data\OrderInterface $order)
    {
        $companyId = $this->getCompanyIdByOrder($order);
        try {
            $company = $this->companyRepository->get($companyId);
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('We can\'t save the credit memo because the company associated with this customer does not exist.')
            );
        }
        if (!$this->companyStatus->isRefundAvailable($company->getId())) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('We can\'t save the credit memo because the company associated with customer is not active.')
            );
        }
        return $companyId;
    }
}
