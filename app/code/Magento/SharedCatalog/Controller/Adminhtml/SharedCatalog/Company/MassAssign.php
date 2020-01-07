<?php
namespace Magento\SharedCatalog\Controller\Adminhtml\SharedCatalog\Company;

use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Magento\SharedCatalog\Ui\DataProvider\Collection\Grid\CompanyFactory as CompanyCollectionFactory;
use Magento\SharedCatalog\Model\Form\Storage\CompanyFactory as CompanyStorageFactory;
use Magento\SharedCatalog\Model\Form\Storage\UrlBuilder;

/**
 * Change assignment state for companies in shared catalog.
 */
class MassAssign extends \Magento\SharedCatalog\Controller\Adminhtml\AbstractJsonAction
{
    /**
     * @var CompanyStorageFactory
     */
    protected $companyStorageFactory;

    /**
     * Filters
     * @var Filter
     */
    protected $filter;

    /**
     * Company collection factory
     * @var CompanyCollectionFactory
     */
    protected $collectionFactory;

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
     */
    public function __construct(
        Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        Filter $filter,
        CompanyCollectionFactory $collectionFactory,
        CompanyStorageFactory $companyStorageFactory,
        \Psr\Log\LoggerInterface $logger
    ) {
        parent::__construct($context, $resultJsonFactory);
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->companyStorageFactory = $companyStorageFactory;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        try {
            /** @var \Magento\SharedCatalog\Model\Form\Storage\Company $storage */
            $storage = $this->companyStorageFactory->create([
                'key' => $this->getRequest()->getParam(UrlBuilder::REQUEST_PARAM_CONFIGURE_KEY)
            ]);

            $isAssign = (int)$this->getRequest()->getParam('is_assign');

            if ($isAssign) {
                $storage->assignCompanies($this->getCompaniesIds());
            } else {
                $storage->unassignCompanies($this->getCompaniesIds());
            }

            $response = $this->createJsonResponse(['data' => ['status' => true]]);
        } catch (\Exception $e) {
            $this->logger->critical($e);
            $response = $this->createJsonResponse(['data' => ['status' => false]]);
        }

        return $response;
    }

    /**
     * Get companies ids.
     *
     * @return array
     */
    protected function getCompaniesIds()
    {
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        return array_keys($collection->getItems());
    }
}
