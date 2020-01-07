<?php
namespace Magento\SharedCatalog\Controller\Adminhtml\SharedCatalog\Configure\Product;

use Magento\SharedCatalog\Model\Form\Storage\WizardFactory as WizardStorageFactory;
use Magento\SharedCatalog\Model\Form\Storage\UrlBuilder;
use Magento\SharedCatalog\Api\Data\SharedCatalogInterface;

/**
 * Assign product to shared catalog.
 */
class Assign extends \Magento\SharedCatalog\Controller\Adminhtml\AbstractJsonAction
{
    /**
     * @var WizardStorageFactory
     */
    private $wizardStorageFactory;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var \Magento\SharedCatalog\Model\Price\ProductTierPriceLoader
     */
    private $productTierPriceLoader;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param WizardStorageFactory $wizardStorageFactory,
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\SharedCatalog\Model\Price\ProductTierPriceLoader $productTierPriceLoader
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        WizardStorageFactory $wizardStorageFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\SharedCatalog\Model\Price\ProductTierPriceLoader $productTierPriceLoader,
        \Psr\Log\LoggerInterface $logger
    ) {
        parent::__construct($context, $resultJsonFactory);
        $this->wizardStorageFactory = $wizardStorageFactory;
        $this->productRepository = $productRepository;
        $this->productTierPriceLoader = $productTierPriceLoader;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        /** @var \Magento\SharedCatalog\Model\Form\Storage\Wizard $storage */
        $storage = $this->wizardStorageFactory->create([
            'key' => $this->getRequest()->getParam(UrlBuilder::REQUEST_PARAM_CONFIGURE_KEY)
        ]);

        $productId = (int)$this->getRequest()->getParam('product_id');
        $isAssign = (int)$this->getRequest()->getParam('is_assign');
        $status = 0;

        try {
            $product = $this->productRepository->getById($productId);
            if ($isAssign) {
                $storage->assignProducts([$product->getSku()]);
                $storage->assignCategories($product->getCategoryIds());
            } else {
                $storage->unassignProducts([$product->getSku()]);
            }

            $sharedCatalogId = (int)$this->getRequest()->getParam(SharedCatalogInterface::SHARED_CATALOG_ID_URL_PARAM);
            $this->productTierPriceLoader->populateProductTierPrices(
                [$product],
                $sharedCatalogId,
                $storage
            );
            $isAssign = $storage->isProductAssigned($product->getSku());
            $status = 1;
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            $isAssign = !$isAssign;
        } catch (\Exception $e) {
            $isAssign = !$isAssign;
            $this->logger->critical($e);
        }

        return $this->createJsonResponse(
            [
                'data'  => [
                    'status' => $status,
                    'product' => $productId,
                    'is_assign' => $isAssign
                ]
            ]
        );
    }
}
