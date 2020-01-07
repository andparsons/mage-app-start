<?php
namespace Magento\SharedCatalog\Controller\Adminhtml\SharedCatalog\Configure;

use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Authorization\Model\UserContextInterface;

/**
 * Save shared catalog structure and pricing.
 */
class Save extends \Magento\Backend\App\Action implements HttpPostActionInterface
{
    /**
     * @var \Magento\SharedCatalog\Model\Configure\Category
     */
    private $configureCategory;

    /**
     * @var \Magento\SharedCatalog\Model\Form\Storage\WizardFactory
     */
    private $wizardStorageFactory;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \Magento\SharedCatalog\Model\ResourceModel\ProductItem\Price\ScheduleBulk
     */
    private $scheduleBulk;

    /**
     * @var \Magento\SharedCatalog\Api\PriceManagementInterface
     */
    private $priceSharedCatalogManagement;

    /**
     * @var \Magento\Authorization\Model\UserContextInterface
     */
    private $userContext;

    /**
     * @var \Magento\SharedCatalog\Model\Form\Storage\DiffProcessor
     */
    private $diffProcessor;

    /**
     * Save constructor.
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\SharedCatalog\Model\Configure\Category $configureCategory
     * @param \Magento\SharedCatalog\Model\Form\Storage\WizardFactory $wizardStorageFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\SharedCatalog\Model\ResourceModel\ProductItem\Price\ScheduleBulk $scheduleBulk
     * @param \Magento\SharedCatalog\Api\PriceManagementInterface $priceSharedCatalogManagement
     * @param UserContextInterface $userContextInterface
     * @param \Magento\SharedCatalog\Model\Form\Storage\DiffProcessor $diffProcessor
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\SharedCatalog\Model\Configure\Category $configureCategory,
        \Magento\SharedCatalog\Model\Form\Storage\WizardFactory $wizardStorageFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\SharedCatalog\Model\ResourceModel\ProductItem\Price\ScheduleBulk $scheduleBulk,
        \Magento\SharedCatalog\Api\PriceManagementInterface $priceSharedCatalogManagement,
        UserContextInterface $userContextInterface,
        \Magento\SharedCatalog\Model\Form\Storage\DiffProcessor $diffProcessor
    ) {
        parent::__construct($context);
        $this->configureCategory = $configureCategory;
        $this->wizardStorageFactory = $wizardStorageFactory;
        $this->logger = $logger;
        $this->scheduleBulk = $scheduleBulk;
        $this->priceSharedCatalogManagement = $priceSharedCatalogManagement;
        $this->userContext = $userContextInterface;
        $this->diffProcessor = $diffProcessor;
    }

    /**
     * Save shared catalog products, categories and tier prices.
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $sharedCatalogId = $this->getRequest()->getParam('catalog_id');
        /** @var \Magento\SharedCatalog\Model\Form\Storage\Wizard $currentStorage */
        $currentStorage = $this->wizardStorageFactory->create([
            'key' => $this->getRequest()->getParam('configure_key')
        ]);

        try {
            $resultDiff = $this->diffProcessor->getDiff($currentStorage, $sharedCatalogId);
            $sharedCatalog = $this->configureCategory->saveConfiguredCategories(
                $currentStorage,
                $sharedCatalogId,
                $this->getRequest()->getParam('store_id')
            );
            $unassignProductSkus = $currentStorage->getUnassignedProductSkus();
            $this->priceSharedCatalogManagement->deleteProductTierPrices(
                $sharedCatalog,
                $unassignProductSkus
            );
            $prices = $currentStorage->getTierPrices(null, true);
            $prices = array_diff_key($prices, array_flip($unassignProductSkus));
            $this->scheduleBulk->execute($sharedCatalog, $prices, $this->userContext->getUserId());
            if ($resultDiff['pricesChanged'] || $resultDiff['categoriesChanged']) {
                $this->messageManager->addSuccessMessage(
                    __(
                        'The selected items are being processed. You can continue to work in the meantime.'
                    )
                );
            } elseif ($resultDiff['productsChanged']) {
                $this->messageManager->addSuccessMessage(
                    __(
                        'The selected changes have been applied to the shared catalog.'
                    )
                );
            }
        } catch (\Exception $e) {
            $this->logger->critical($e);
            $this->messageManager->addErrorMessage($e->getMessage());
        }

        return $this->resultRedirectFactory->create()->setPath('shared_catalog/sharedCatalog/index');
    }
}
