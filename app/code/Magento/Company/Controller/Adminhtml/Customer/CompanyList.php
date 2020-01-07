<?php
namespace Magento\Company\Controller\Adminhtml\Customer;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Company\Api\CompanyRepositoryInterface;
use Magento\Company\Api\Data\CompanyInterface;
use Magento\Company\Model\CountryInformationProvider;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\DB\Helper as DbHelper;
use Magento\Framework\Exception\LocalizedException;

/**
 * Controller for obtaining companies suggestions by query.
 */
class CompanyList extends Action implements HttpPostActionInterface, HttpGetActionInterface
{
    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var CompanyRepositoryInterface
     */
    private $companyRepository;

    /**
     * @var CountryInformationProvider
     */
    private $countryInformationProvider;

    /**
     * @var DbHelper
     */
    private $dbHelper;

    /**
     * @param Context $context
     * @param CompanyRepositoryInterface $companyRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param CountryInformationProvider $countryInformationProvider
     * @param DbHelper $dbHelper
     */
    public function __construct(
        Context $context,
        CompanyRepositoryInterface $companyRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        CountryInformationProvider $countryInformationProvider,
        DbHelper $dbHelper
    ) {
        parent::__construct($context);
        $this->companyRepository = $companyRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->countryInformationProvider = $countryInformationProvider;
        $this->dbHelper = $dbHelper;
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $result */
        $result = $this->resultFactory->create(ResultFactory::TYPE_JSON);

        $name = $this->getRequest()->getParam('name');

        try {
            $companies = $this->getSuggestedCompanies($name);
            $result->setData(
                $this->getCompaniesData($companies)
            );
        } catch (LocalizedException $e) {
            $result->setData(['error' => $e->getMessage()]);
        }

        return $result;
    }

    /**
     * Get suggested companies by query.
     *
     * @param string $query
     * @return CompanyInterface[]
     */
    private function getSuggestedCompanies($query)
    {
        $escapedQuery = $this->dbHelper->escapeLikeValue(
            $query,
            ['position' => 'start']
        );

        $searchCriteria = $this->searchCriteriaBuilder->addFilter(
            CompanyInterface::NAME,
            $escapedQuery,
            'like'
        )->create();

        $searchResult = $this->companyRepository->getList($searchCriteria);

        return $searchResult->getItems();
    }

    /**
     * Get companies data as array.
     *
     * @param CompanyInterface[] $companies
     * @return array
     */
    private function getCompaniesData(array $companies)
    {
        return array_map(
            function (CompanyInterface $company) {
                return [
                    'id' => $company->getId(),
                    'name' => $company->getCompanyName(),
                    'group' => $company->getCustomerGroupId(),
                    'country' => $company->getCountryId(),
                    'region' => $this->countryInformationProvider->getActualRegionName(
                        $company->getCountryId(),
                        $company->getRegionId(),
                        $company->getRegion()
                    )
                ];
            },
            array_values($companies)
        );
    }
}
