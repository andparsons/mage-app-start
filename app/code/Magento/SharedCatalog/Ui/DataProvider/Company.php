<?php
namespace Magento\SharedCatalog\Ui\DataProvider;

use Magento\Company\Api\CompanyRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\SharedCatalog\Api\Data\SharedCatalogInterface;
use Magento\SharedCatalog\Api\SharedCatalogManagementInterface;
use Magento\SharedCatalog\Api\SharedCatalogRepositoryInterface;
use Magento\SharedCatalog\Ui\DataProvider\Collection\Grid\CompanyFactory as CollectionFactory;
use Magento\SharedCatalog\Model\Form\Storage\CompanyFactory as CompanyStorageFactory;
use Magento\SharedCatalog\Model\Form\Storage\UrlBuilder;

/**
 * Companies grid data provider for shared catalog.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Company extends AbstractDataProvider
{
    /**
     * @var \Magento\SharedCatalog\Model\Form\Storage\Company
     */
    protected $storage;

    /**
     * @var SharedCatalogManagementInterface
     */
    protected $catalogManagement;

    /**
     * @var SharedCatalogRepositoryInterface
     */
    protected $catalogRepository;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var CompanyRepositoryInterface
     */
    protected $companyRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param \Magento\Framework\App\RequestInterface $request
     * @param CollectionFactory $collectionFactory
     * @param CompanyStorageFactory $companyStorageFactory
     * @param SharedCatalogManagementInterface $catalogManagement
     * @param SharedCatalogRepositoryInterface $catalogRepository
     * @param CompanyRepositoryInterface $companyRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param array $meta [optional]
     * @param array $data [optional]
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        \Magento\Framework\App\RequestInterface $request,
        CollectionFactory $collectionFactory,
        CompanyStorageFactory $companyStorageFactory,
        SharedCatalogManagementInterface $catalogManagement,
        SharedCatalogRepositoryInterface $catalogRepository,
        CompanyRepositoryInterface $companyRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $request, $meta, $data);
        $this->collectionFactory = $collectionFactory;
        $this->catalogManagement = $catalogManagement;
        $this->catalogRepository = $catalogRepository;
        $this->companyRepository = $companyRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->storage = $companyStorageFactory->create([
            'key' => $request->getParam(UrlBuilder::REQUEST_PARAM_CONFIGURE_KEY)
        ]);
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareConfig(array $configData)
    {
        $configData = parent::prepareConfig($configData);
        return $this->prepareUrl($configData, 'update_url');
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareCollection()
    {
        $collection = $this->collectionFactory->create();
        $collection->addIsCurrentColumn($this->storage->getSharedCatalogId());
        return $collection;
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareDataItem(\Magento\Framework\DataObject $item)
    {
        $item->setSharedCatalogId($this->getItemSharedCatalogId($item));
        $item->setIsCurrent((int)$this->isCompanyCurrent($item));
        $item->setIsPublicCatalog($this->isPublicCatalog($item));
        return parent::prepareDataItem($item);
    }

    /**
     * Is data item shared catalog public or not.
     *
     * @param \Magento\Framework\DataObject $item
     * @return bool
     */
    private function isPublicCatalog(\Magento\Framework\DataObject $item)
    {
        return $item->getSharedCatalogId() == $this->catalogManagement->getPublicCatalog()->getId();
    }

    /**
     * Get shared catalog ID for data item.
     *
     * @param \Magento\Framework\DataObject $item
     * @return int
     */
    private function getItemSharedCatalogId(\Magento\Framework\DataObject $item)
    {
        $isAssigned = $this->storage->isCompanyAssigned($item->getEntityId());
        $isUnassigned = $this->storage->isCompanyUnassigned($item->getEntityId());

        $sharedCatalogId = $item->getSharedCatalogId();

        if ($isAssigned) {
            $sharedCatalogId = $this->storage->getSharedCatalogId();
        }

        if ($isUnassigned) {
            $sharedCatalogId = $this->catalogManagement->getPublicCatalog()->getId();
        }

        return $sharedCatalogId;
    }

    /**
     * Is data item company assigned to current shared catalog or not.
     *
     * @param \Magento\Framework\DataObject $item
     * @return bool
     */
    private function isCompanyCurrent(\Magento\Framework\DataObject $item)
    {
        return $item->getSharedCatalogId() == $this->storage->getSharedCatalogId();
    }

    /**
     * {@inheritdoc}
     */
    public function addFilter(\Magento\Framework\Api\Filter $filter)
    {
        switch ($filter->getField()) {
            case 'is_current':
                $this->addIsCurrentFilter($filter->getValue());
                break;
            case 'shared_catalog_id':
                $this->addSharedCatalogFilter($filter->getValue());
                break;
            default:
                $this->getCollection()->addFieldToFilter(
                    $filter->getField(),
                    [$filter->getConditionType() => $filter->getValue()]
                );
        }
    }

    /**
     * Add currently assigned companies filter to collection.
     *
     * @param string $value
     * @return $this
     */
    private function addIsCurrentFilter($value)
    {
        /** @var \Magento\SharedCatalog\Ui\DataProvider\Collection\Company $collection */
        $collection = $this->getCollection();
        $collection->addIdFilter($this->storage->getAssignedCompaniesIds(), !$value);
        return $this;
    }

    /**
     * Add shared catalog filter.
     *
     * @param string $catalogId
     * @return $this
     */
    private function addSharedCatalogFilter($catalogId)
    {
        $currentCatalog = $this->getCurrentSharedCatalog();
        if ($catalogId == $currentCatalog->getId()) {
            return $this->addIsCurrentFilter(true);
        }

        $catalogCompanies = array_diff(
            $this->getCatalogCompaniesIds($catalogId),
            $this->storage->getAssignedCompaniesIds()
        );

        if ($catalogId == $this->catalogManagement->getPublicCatalog()->getId()) {
            $catalogCompanies = array_merge($catalogCompanies, $this->storage->getUnassignedCompaniesIds());
        }

        /** @var \Magento\SharedCatalog\Ui\DataProvider\Collection\Company $collection */
        $collection = $this->getCollection();
        $collection->addIdFilter($catalogCompanies);

        return $this;
    }

    /**
     * Get shared catalog companies IDs.
     *
     * @param int $catalogId
     * @return array
     */
    private function getCatalogCompaniesIds($catalogId)
    {
        $catalog = $this->catalogRepository->get($catalogId);
        $builder = $this->searchCriteriaBuilder->addFilter('customer_group_id', $catalog->getCustomerGroupId());
        $companies = $this->companyRepository->getList($builder->create())->getItems();
        return array_keys($companies);
    }

    /**
     * Get current shared catalog.
     *
     * @return SharedCatalogInterface
     */
    private function getCurrentSharedCatalog()
    {
        return $this->catalogRepository->get($this->storage->getSharedCatalogId());
    }
}
