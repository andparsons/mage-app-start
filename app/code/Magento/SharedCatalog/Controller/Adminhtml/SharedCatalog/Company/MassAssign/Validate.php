<?php
namespace Magento\SharedCatalog\Controller\Adminhtml\SharedCatalog\Company\MassAssign;

use Magento\Ui\Component\MassAction\Filter;
use Magento\Backend\App\Action\Context;
use Magento\SharedCatalog\Controller\Adminhtml\SharedCatalog\Company\MassAssign;
use Magento\SharedCatalog\Ui\DataProvider\Collection\Grid\CompanyFactory as CompanyCollectionFactory;
use Magento\SharedCatalog\Model\Form\Storage\CompanyFactory as CompanyStorageFactory;
use Magento\SharedCatalog\Api\SharedCatalogManagementInterface as CatalogManagementInterface;

/**
 * Validate companies before assign to shared catalog.
 */
class Validate extends MassAssign
{
    /**
     * @var CatalogManagementInterface
     */
    protected $catalogManagement;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @param Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param Filter $filter
     * @param CompanyCollectionFactory $collectionFactory
     * @param CompanyStorageFactory $companyStorageFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param CatalogManagementInterface $catalogManagement
     */
    public function __construct(
        Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        Filter $filter,
        CompanyCollectionFactory $collectionFactory,
        CompanyStorageFactory $companyStorageFactory,
        \Psr\Log\LoggerInterface $logger,
        CatalogManagementInterface $catalogManagement
    ) {
        parent::__construct($context, $resultJsonFactory, $filter, $collectionFactory, $companyStorageFactory, $logger);
        $this->catalogManagement = $catalogManagement;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $companiesCollection = $this->filter->getCollection($this->collectionFactory->create());
        $publicCatalog = $this->catalogManagement->getPublicCatalog();

        $isCustomCatalogExist = false;
        /** @var CompanyInterface $company */
        foreach ($companiesCollection as $company) {
            if ($publicCatalog->getId() != $company->getSharedCatalogId()) {
                $isCustomCatalogExist = true;
                break;
            }
        }

        return $this->createJsonResponse(['is_custom_assigned' => $isCustomCatalogExist]);
    }
}
