<?php
namespace Magento\SharedCatalog\Controller\Adminhtml\SharedCatalog\Configure\Product\Price;

use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\SharedCatalog\Model\Form\Storage\WizardFactory as WizardStorageFactory;
use Magento\SharedCatalog\Model\Form\Storage\UrlBuilder;

/**
 * Save product custom price.
 */
class Save extends \Magento\SharedCatalog\Controller\Adminhtml\AbstractJsonAction implements HttpPostActionInterface
{
    /**
     * @var \Magento\SharedCatalog\Model\Form\Storage\WizardFactory
     */
    private $wizardStorageFactory;

    /**
     * @var \Magento\Framework\Locale\FormatInterface
     */
    private $valueFormatter;

    /**
     * @var \Magento\SharedCatalog\Model\ProductItemTierPriceValidator
     */
    private $productItemTierPriceValidator;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var \Magento\SharedCatalog\Model\Form\Storage\Wizard
     */
    private $storage;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\SharedCatalog\Model\Form\Storage\WizardFactory $wizardStorageFactory
     * @param \Magento\Framework\Locale\FormatInterface $valueFormatter
     * @param \Magento\SharedCatalog\Model\ProductItemTierPriceValidator $productItemTierPriceValidator
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\SharedCatalog\Model\Form\Storage\WizardFactory $wizardStorageFactory,
        \Magento\Framework\Locale\FormatInterface $valueFormatter,
        \Magento\SharedCatalog\Model\ProductItemTierPriceValidator $productItemTierPriceValidator,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
    ) {
        parent::__construct($context, $resultJsonFactory);
        $this->wizardStorageFactory = $wizardStorageFactory;
        $this->valueFormatter = $valueFormatter;
        $this->productItemTierPriceValidator = $productItemTierPriceValidator;
        $this->productRepository = $productRepository;
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        $prices = $this->getRequest()->getParam('prices', []);
        foreach ($prices as $price) {
            if (!is_array($price) || !$this->validatePrice($price)) {
                continue;
            }
            $productId = (int)$price['product_id'];
            $product = $this->productRepository->getById($productId);
            $customPrice = $this->valueFormatter->getNumber($price['custom_price']);

            if ($customPrice >= 0) {
                $valueField =
                    $price['price_type'] == \Magento\Catalog\Api\Data\TierPriceInterface::PRICE_TYPE_FIXED
                        ? \Magento\Catalog\Api\Data\ProductAttributeInterface::CODE_PRICE
                        : \Magento\Catalog\Api\Data\ProductAttributeInterface::CODE_TIER_PRICE_FIELD_PERCENTAGE_VALUE;
                $this->getStorage()->setTierPrices(
                    [
                        $product->getSku() => [
                            [
                                'qty' => 1,
                                $valueField => $customPrice,
                                'value_type' => $price['price_type'],
                                'website_id' => $price['website_id'],
                                'is_changed' => true,
                            ],
                        ]
                    ]
                );
            } else {
                $this->getStorage()->deleteTierPrice($product->getSku(), 1, $price['website_id']);
            }
        }

        return $this->createJsonResponse([
            'data' => ['status' => 1]
        ]);
    }

    /**
     * Validate price array on existing params and possibility of change price.
     *
     * @param array $price
     * @return bool
     */
    private function validatePrice(array $price)
    {
        if (empty($price) || empty($price['product_id']) || !isset($price['custom_price'])
            || !isset($price['price_type']) || !isset($price['website_id'])
        ) {
            return false;
        }

        $customPrices = $this->getStorage()->getProductPrices($price['product_id']);
        if (!$this->productItemTierPriceValidator->canChangePrice($customPrices, $price['website_id'])) {
            return false;
        }

        return true;
    }

    /**
     * Retrieve storage for current shared catalog.
     *
     * @return \Magento\SharedCatalog\Model\Form\Storage\Wizard
     */
    private function getStorage()
    {
        if (empty($this->storage)) {
            $this->storage = $this->wizardStorageFactory->create([
                'key' => $this->getRequest()->getParam(UrlBuilder::REQUEST_PARAM_CONFIGURE_KEY)
            ]);
        }
        return $this->storage;
    }
}
