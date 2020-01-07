<?php

namespace Magento\CompanyCredit\Controller\Adminhtml\Index;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Company\Api\Data\CompanyInterface;
use Magento\Company\Model\ResourceModel\Company\CollectionFactory as CompanyCollectionFactory;
use Magento\CompanyCredit\Api\CreditLimitManagementInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Ui\Component\MassAction\Filter;
use Psr\Log\LoggerInterface;

/**
 * Controller returns base website / company credit currency pairs with conversion rates.
 */
class GetConversionRates extends Action implements HttpGetActionInterface
{
    /**
     * @var Filter
     */
    private $filter;

    /**
     * @var CompanyCollectionFactory
     */
    private $companyCollectionFactory;

    /**
     * @var CreditLimitManagementInterface
     */
    private $creditLimitManagement;

    /**
     * @var PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param Context $context
     * @param Filter $filter
     * @param CompanyCollectionFactory $companyCollectionFactory
     * @param CreditLimitManagementInterface $creditLimitManagement
     * @param PriceCurrencyInterface $priceCurrency
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CompanyCollectionFactory $companyCollectionFactory,
        CreditLimitManagementInterface $creditLimitManagement,
        PriceCurrencyInterface $priceCurrency,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->filter = $filter;
        $this->companyCollectionFactory = $companyCollectionFactory;
        $this->creditLimitManagement = $creditLimitManagement;
        $this->priceCurrency = $priceCurrency;
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $result */
        $result = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $currencyTo = $this->getRequest()->getParam('currency_to');

        try {
            /** @var \Magento\Directory\Model\Currency $currency */
            $currency = $this->priceCurrency->getCurrency();
            $rates = [];
            foreach ($this->getRequestedCompanies() as $company) {
                $creditLimit = $this->creditLimitManagement->getCreditByCompanyId($company->getId());
                $rates[$creditLimit->getCurrencyCode()] = $currency->getCurrencyRates(
                    $creditLimit->getCurrencyCode(),
                    [$currencyTo]
                );
            }
            $result->setData(
                [
                    'status' => 'success',
                    'currency_rates' => $rates
                ]
            );
        } catch (\Exception $e) {
            $result->setData(
                [
                    'status' => 'error',
                    'error' => __(
                        'Unable to retrieve currency rates at this moment. ' .
                        'Please try again later or contact store administrator.'
                    )
                ]
            );
            $this->logger->critical($e);
        }

        return $result;
    }

    /**
     * Get companies requested by massaction.
     *
     * @return CompanyInterface[]
     * @throws LocalizedException
     */
    private function getRequestedCompanies()
    {
        $collection = $this->companyCollectionFactory->create();
        $collection = $this->filter->getCollection($collection);

        return $collection->getItems();
    }
}
