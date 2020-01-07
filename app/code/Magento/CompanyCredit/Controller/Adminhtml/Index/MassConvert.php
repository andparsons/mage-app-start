<?php
namespace Magento\CompanyCredit\Controller\Adminhtml\Index;

use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Company\Api\Data\CompanyInterface;
use Magento\Company\Model\ResourceModel\Company\CollectionFactory as CompanyCollectionFactory;
use Magento\CompanyCredit\Api\CreditLimitManagementInterface;
use Magento\CompanyCredit\Api\CreditLimitRepositoryInterface;
use Magento\CompanyCredit\Api\Data\CreditLimitInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Ui\Component\MassAction\Filter;

/**
 * Controller for bulk convert companies credit currency, balance and limit.
 */
class MassConvert extends Action implements HttpPostActionInterface
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
     * @var CreditLimitRepositoryInterface
     */
    private $creditLimitRepository;

    /**
     * @param Context $context
     * @param Filter $filter
     * @param CompanyCollectionFactory $companyCollectionFactory
     * @param CreditLimitManagementInterface $creditLimitManagement
     * @param CreditLimitRepositoryInterface $creditLimitRepository
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CompanyCollectionFactory $companyCollectionFactory,
        CreditLimitManagementInterface $creditLimitManagement,
        CreditLimitRepositoryInterface $creditLimitRepository
    ) {
        parent::__construct($context);
        $this->filter = $filter;
        $this->companyCollectionFactory = $companyCollectionFactory;
        $this->creditLimitManagement = $creditLimitManagement;
        $this->creditLimitRepository = $creditLimitRepository;
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Redirect $result */
        $result = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $result->setRefererUrl();

        $rates = $this->getRequest()->getParam('currency_rates');
        $currencyTo = $this->getRequest()->getParam('currency_to');

        try {
            $recordsConverted = 0;
            foreach ($this->getRequestedCompanies() as $company) {
                $creditLimit = $this->creditLimitManagement->getCreditByCompanyId($company->getId());

                $currencyRate = $this->prepareCurrencyRate($rates, $creditLimit->getCurrencyCode());
                if ($currencyRate === null) {
                    continue;
                }

                $creditLimit->setCurrencyCode($currencyTo)
                    ->setCurrencyRate($currencyRate)
                    ->setCreditLimit($creditLimit->getCreditLimit() * $currencyRate);

                $this->creditLimitRepository->save($creditLimit);

                $recordsConverted++;
            }
            $this->messageManager->addSuccessMessage(__('A total of %1 record(s) were updated.', $recordsConverted));
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage(
                $e,
                __('Unable to convert company credit. Please try again later or contact store administrator.')
            );
        }

        return $result;
    }

    /**
     * Prepare currency rate for conversion.
     *
     * @param array $rates
     * @param string $creditCurrencyCode
     * @return float|null
     */
    private function prepareCurrencyRate(array $rates, $creditCurrencyCode)
    {
        return (isset($rates[$creditCurrencyCode]) && (float)$rates[$creditCurrencyCode] > 0) ?
            (float)$rates[$creditCurrencyCode] : null;
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
