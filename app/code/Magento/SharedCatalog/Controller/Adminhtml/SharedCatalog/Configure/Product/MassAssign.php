<?php
namespace Magento\SharedCatalog\Controller\Adminhtml\SharedCatalog\Configure\Product;

use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Backend\App\Action\Context;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Eav\Model\Entity\Collection\AbstractCollection;
use Magento\Ui\Component\MassAction\Filter;
use Magento\SharedCatalog\Model\Form\Storage\UrlBuilder;
use Psr\Log\LoggerInterface;
use Magento\SharedCatalog\Api\Data\SharedCatalogInterface;

/**
 * Mass assignment of products to shared catalog.
 */
class MassAssign extends AbstractMassAction implements HttpPostActionInterface
{
    /**
     * @var \Magento\SharedCatalog\Model\Form\Storage\WizardFactory
     */
    private $wizardStorageFactory;

    /**
     * @var \Magento\SharedCatalog\Model\Form\Storage\SharedCatalogMassAssignment
     */
    private $sharedCatalogMassAssignment;

    /**
     * @param Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param LoggerInterface $logger
     * @param \Magento\SharedCatalog\Model\Form\Storage\WizardFactory $wizardFactory
     * @param \Magento\SharedCatalog\Model\Form\Storage\SharedCatalogMassAssignment $sharedCatalogMassAssignment
     */
    public function __construct(
        Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        Filter $filter,
        CollectionFactory $collectionFactory,
        LoggerInterface $logger,
        \Magento\SharedCatalog\Model\Form\Storage\WizardFactory $wizardFactory,
        \Magento\SharedCatalog\Model\Form\Storage\SharedCatalogMassAssignment $sharedCatalogMassAssignment
    ) {
        parent::__construct(
            $context,
            $resultJsonFactory,
            $filter,
            $collectionFactory,
            $logger
        );
        $this->wizardStorageFactory = $wizardFactory;
        $this->sharedCatalogMassAssignment = $sharedCatalogMassAssignment;
    }

    /**
     * Assign all selected products to the shared catalog.
     *
     * @param AbstractCollection $collection
     * @return \Magento\Framework\Controller\Result\Json
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function massAction(AbstractCollection $collection)
    {
        $storage = $this->wizardStorageFactory->create([
            'key' => $this->getRequest()->getParam(UrlBuilder::REQUEST_PARAM_CONFIGURE_KEY)
        ]);
        $isAssign = (int)$this->getRequest()->getParam('is_assign');
        $sharedCatalogId = (int)$this->getRequest()->getParam(SharedCatalogInterface::SHARED_CATALOG_ID_URL_PARAM);
        $this->sharedCatalogMassAssignment->assign($collection, $storage, $sharedCatalogId, $isAssign);

        return $this->createJsonResponse(['data' => ['status' => true]]);
    }
}
