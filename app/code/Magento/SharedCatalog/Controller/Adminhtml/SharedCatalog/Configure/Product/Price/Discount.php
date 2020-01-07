<?php
namespace Magento\SharedCatalog\Controller\Adminhtml\SharedCatalog\Configure\Product\Price;

use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Backend\App\Action\Context;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Eav\Model\Entity\Collection\AbstractCollection;
use Magento\Ui\Component\MassAction\Filter;
use Magento\SharedCatalog\Model\Form\Storage\WizardFactory as WizardStorageFactory;
use Magento\SharedCatalog\Model\Form\Storage\UrlBuilder;
use Magento\SharedCatalog\Controller\Adminhtml\SharedCatalog\Configure\Product\AbstractMassAction as AbstractMassAction;
use Psr\Log\LoggerInterface;

/**
 * Mass discount action.
 */
class Discount extends AbstractMassAction implements HttpPostActionInterface
{
    /**
     * @var WizardStorageFactory
     */
    private $wizardStorageFactory;

    /**
     * @var \Magento\SharedCatalog\Model\ProductItemTierPriceValidator
     */
    private $productItemTierPriceValidator;

    /**
     * @var string
     */
    private $productPriceValueType = 'percent';

    /**
     * @var int
     */
    private $batchSize;

    /**
     * @param Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param LoggerInterface $logger
     * @param WizardStorageFactory $wizardStorageFactory
     * @param \Magento\SharedCatalog\Model\ProductItemTierPriceValidator $productItemTierPriceValidator
     * @param int $batchSize [optional]
     */
    public function __construct(
        Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        Filter $filter,
        CollectionFactory $collectionFactory,
        LoggerInterface $logger,
        WizardStorageFactory $wizardStorageFactory,
        \Magento\SharedCatalog\Model\ProductItemTierPriceValidator $productItemTierPriceValidator,
        $batchSize = 100
    ) {
        parent::__construct(
            $context,
            $resultJsonFactory,
            $filter,
            $collectionFactory,
            $logger
        );
        $this->wizardStorageFactory = $wizardStorageFactory;
        $this->productItemTierPriceValidator = $productItemTierPriceValidator;
        $this->batchSize = $batchSize;
    }

    /**
     * Apply tier prices with discount type for all selected products.
     *
     * @param AbstractCollection $collection
     * @return \Magento\Framework\Controller\Result\Json
     */
    protected function massAction(AbstractCollection $collection)
    {
        $customPrice = (float)$this->getRequest()->getParam('value');
        if ($customPrice < 0 || $customPrice > 100) {
            return $this->createJsonResponse(
                ['data' => ['status' => false, 'error' => __("Discount value cannot be outside the range 0-100")]]
            );
        }

        /** @var \Magento\SharedCatalog\Model\Form\Storage\Wizard $storage */
        $storage = $this->wizardStorageFactory->create([
            'key' => $this->getRequest()->getParam(UrlBuilder::REQUEST_PARAM_CONFIGURE_KEY)
        ]);

        /* @var \Magento\Catalog\Model\ResourceModel\Product\Collection $collection */
        $collection->addFieldToSelect('price');
        $websiteId = $this->getRequest()->getParam('website_id');

        /* @var \Magento\Catalog\Api\Data\ProductInterface $product*/
        $productPrices = [];
        foreach ($collection as $product) {
            $customPrices = $storage->getProductPrices($product->getSku());
            if (!$this->productItemTierPriceValidator->isTierPriceApplicable($product->getTypeId())
                || !$this->productItemTierPriceValidator->canChangePrice($customPrices, $websiteId)) {
                continue;
            }

            $productPrices[$product->getSku()][] = [
                'qty' => 1,
                'percentage_value' => $customPrice,
                'value_type' => $this->productPriceValueType,
                'website_id' => $websiteId ?: 0,
                'is_changed' => true,
            ];
            if (count($productPrices) >= $this->batchSize) {
                $storage->setTierPrices($productPrices);
                $productPrices = [];
            }
        }
        if (!empty($productPrices)) {
            $storage->setTierPrices($productPrices);
        }

        return $this->createJsonResponse(['data' => ['status' => true]]);
    }
}
