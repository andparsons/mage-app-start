<?php

namespace Magento\CompanyCredit\Block\Adminhtml\Order;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\CompanyCredit\Model\CompanyCreditPaymentConfigProvider;
use Magento\Company\Api\CompanyRepositoryInterface;

/**
 * Block for credit limit message.
 *
 * @api
 * @since 100.0.0
 */
class Message extends \Magento\Sales\Block\Adminhtml\Order\AbstractOrder
{
    /**
     * @var \Magento\CompanyCredit\Api\Data\CreditLimitInterface
     */
    private $creditLimit;

    /**
     * @var \Magento\CompanyCredit\Api\CreditDataProviderInterface
     */
    private $creditDataProvider;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    private $priceFormatter;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var \Magento\Company\Api\CompanyRepositoryInterface
     */
    private $companyRepository;

    /**
     * @var \Magento\CompanyCredit\Api\Data\CreditDataInterface
     */
    private $credit;

    /**
     * Message constructor.
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Sales\Helper\Admin $adminHelper
     * @param \Magento\CompanyCredit\Api\Data\CreditLimitInterface $creditLimit
     * @param \Magento\CompanyCredit\Api\CreditDataProviderInterface $creditDataProvider
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceFormatter
     * @param CustomerRepositoryInterface $customerRepository
     * @param CompanyRepositoryInterface $companyRepository
     * @param array $data [optional]
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Sales\Helper\Admin $adminHelper,
        \Magento\CompanyCredit\Api\Data\CreditLimitInterface $creditLimit,
        \Magento\CompanyCredit\Api\CreditDataProviderInterface $creditDataProvider,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceFormatter,
        CustomerRepositoryInterface $customerRepository,
        CompanyRepositoryInterface $companyRepository,
        array $data = []
    ) {
        parent::__construct($context, $registry, $adminHelper, $data);
        $this->creditLimit = $creditLimit;
        $this->creditDataProvider = $creditDataProvider;
        $this->priceFormatter = $priceFormatter;
        $this->customerRepository = $customerRepository;
        $this->companyRepository = $companyRepository;
    }

    /**
     * Check is order placed with Pay On Account.
     *
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function isPayOnAccountMethod()
    {
        return $this->getOrder()->getPayment()->getMethod() == CompanyCreditPaymentConfigProvider::METHOD_NAME;
    }

    /**
     * Get credit object.
     *
     * @return \Magento\CompanyCredit\Api\Data\CreditDataInterface|null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCredit()
    {
        if (empty($this->credit)) {
            try {
                $customerId = $this->getOrder()->getCustomerId();
                $customer = $this->customerRepository->getById($customerId);
                $companyId = $customer->getExtensionAttributes()->getCompanyAttributes()->getCompanyId();
                $this->credit = $this->creditDataProvider->get($companyId);
                if (!$this->credit->getId()) {
                    $this->credit = null;
                }
            } catch (NoSuchEntityException $e) {
                $this->credit = null;
            }
        }
        return $this->credit;
    }

    /**
     * Format price.
     *
     * @param float $price
     * @return string
     */
    public function formatPrice($price)
    {
        return $this->priceFormatter->format(
            $price,
            false,
            \Magento\Framework\Pricing\PriceCurrencyInterface::DEFAULT_PRECISION,
            null,
            $this->getCredit() ? $this->getCredit()->getCurrencyCode() : null
        );
    }

    /**
     * Get company name.
     *
     * @return string
     */
    public function getCompanyName()
    {
        try {
            $companyId = $this->getCredit()->getCompanyId();
            $company = $this->companyRepository->get($companyId);
            $companyName = $company->getCompanyName();
        } catch (NoSuchEntityException $e) {
            $companyName = '';
        }
        return $companyName;
    }
}
