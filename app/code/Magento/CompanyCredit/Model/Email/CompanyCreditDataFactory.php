<?php
namespace Magento\CompanyCredit\Model\Email;

/**
 * Class that creates DataObject containing company credit information to use in Sender class.
 */
class CompanyCreditDataFactory
{
    /**
     * @var \Magento\Framework\Reflection\DataObjectProcessor
     */
    private $dataProcessor;

    /**
     * @var \Magento\Company\Api\CompanyRepositoryInterface
     */
    private $companyRepository;

    /**
     * @var \Magento\CompanyCredit\Api\CreditLimitRepositoryInterface
     */
    private $creditLimitRepository;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    private $priceFormatter;

    /**
     * @var \Magento\Customer\Api\CustomerNameGenerationInterface
     */
    private $customerViewHelper;

    /**
     * @var \Magento\CompanyCredit\Model\Sales\OrderLocator
     */
    private $orderLocator;

    /**
     * @var \Magento\Framework\Serialize\SerializerInterface
     */
    private $serializer;

    /**
     * @param \Magento\Framework\Reflection\DataObjectProcessor $dataProcessor
     * @param \Magento\Company\Api\CompanyRepositoryInterface $companyRepository
     * @param \Magento\CompanyCredit\Api\CreditLimitRepositoryInterface $creditLimitRepository
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceFormatter
     * @param \Magento\Customer\Api\CustomerNameGenerationInterface $customerViewHelper
     * @param \Magento\CompanyCredit\Model\Sales\OrderLocator $orderLocator
     * @param \Magento\Framework\Serialize\SerializerInterface $serializer
     */
    public function __construct(
        \Magento\Framework\Reflection\DataObjectProcessor $dataProcessor,
        \Magento\Company\Api\CompanyRepositoryInterface $companyRepository,
        \Magento\CompanyCredit\Api\CreditLimitRepositoryInterface $creditLimitRepository,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceFormatter,
        \Magento\Customer\Api\CustomerNameGenerationInterface $customerViewHelper,
        \Magento\CompanyCredit\Model\Sales\OrderLocator $orderLocator,
        \Magento\Framework\Serialize\SerializerInterface $serializer
    ) {
        $this->dataProcessor = $dataProcessor;
        $this->companyRepository = $companyRepository;
        $this->creditLimitRepository = $creditLimitRepository;
        $this->priceFormatter = $priceFormatter;
        $this->customerViewHelper = $customerViewHelper;
        $this->orderLocator = $orderLocator;
        $this->serializer = $serializer;
    }

    /**
     * Create an object with data merged from CreditHistory and Credit.
     *
     * @param \Magento\CompanyCredit\Model\HistoryInterface $history
     * @param \Magento\Customer\Api\Data\CustomerInterface $customer
     * @return \Magento\Framework\DataObject|null
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCompanyCreditDataObject(
        \Magento\CompanyCredit\Model\HistoryInterface $history,
        \Magento\Customer\Api\Data\CustomerInterface $customer
    ) {
        $mergedCompanyCreditData = null;
        $orderId = null;
        $storeId = null;
        $creditLimit = $this->creditLimitRepository->get($history->getCompanyCreditId());
        $company = $this->companyRepository->get((int)$creditLimit->getCompanyId());
        $companyCreditData = $this->dataProcessor
            ->buildOutputDataArray($history, \Magento\CompanyCredit\Model\HistoryInterface::class);
        $mergedCompanyCreditData = new \Magento\Framework\DataObject($companyCreditData);
        $comment = $history->getComment() ? $this->serializer->unserialize($history->getComment()) : false;
        if (is_array($comment) && isset($comment['system']['order'])) {
            $orderId = $comment['system']['order'];
            $order = $this->orderLocator->getOrderByIncrementId($orderId);
            $storeId = $order->getStoreId();
        }
        $mergedCompanyCreditData->setData(
            'availableCredit',
            $this->priceFormatter->format(
                $history->getCreditLimit(),
                false,
                \Magento\Framework\Pricing\PriceCurrencyInterface::DEFAULT_PRECISION,
                $storeId,
                $history->getCurrencyCredit()
            )
        );
        $mergedCompanyCreditData->setData(
            'outStandingBalance',
            $this->priceFormatter->format(
                $history->getBalance(),
                false,
                \Magento\Framework\Pricing\PriceCurrencyInterface::DEFAULT_PRECISION,
                $storeId,
                $history->getCurrencyCredit()
            )
        );
        $mergedCompanyCreditData->setData(
            'exceedLimit',
            ($creditLimit->getExceedLimit()) ? 'allowed' : 'not allowed'
        );
        $operationAmount = $history->getAmount() * $this->getOperationAmountConversionRate($history);
        $mergedCompanyCreditData->setData(
            'operationAmount',
            $this->priceFormatter->format(
                $operationAmount,
                false,
                \Magento\Framework\Pricing\PriceCurrencyInterface::DEFAULT_PRECISION,
                $storeId,
                $history->getCurrencyCredit()
            )
        );
        $mergedCompanyCreditData->setData('orderId', $orderId);
        $mergedCompanyCreditData->setData('companyName', $company->getCompanyName());
        $mergedCompanyCreditData->setData('customerName', $this->customerViewHelper->getCustomerName($customer));

        return $mergedCompanyCreditData;
    }

    /**
     * Get rate for conversion operation amount to credit currency.
     *
     * If history item does not contain currency rate,
     * return rate between base currency and operation currency.
     * Otherwise return 1.
     *
     * @param \Magento\CompanyCredit\Model\HistoryInterface $history
     * @return float
     */
    private function getOperationAmountConversionRate(\Magento\CompanyCredit\Model\HistoryInterface $history)
    {
        $conversionRate = 1;
        $rate = (float)$history->getRate() ?: 1;
        $rateCredit = (float)$history->getRateCredit();

        if ($rateCredit) {
            $conversionRate = $rateCredit;
        } elseif ($history->getCurrencyOperation() != $history->getCurrencyCredit()) {
            $conversionRate = 1 / $rate;
        }

        return (float)$conversionRate;
    }
}
