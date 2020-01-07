<?php
namespace Magento\SharedCatalog\Controller\Adminhtml\SharedCatalog\Configure\Product\TierPrice;

use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Locale\FormatInterface;
use Magento\SharedCatalog\Controller\Adminhtml\AbstractJsonAction;
use Magento\SharedCatalog\Model\Form\Storage\WizardFactory;
use Magento\SharedCatalog\Model\Form\Storage\UrlBuilder;

/**
 * Saving tier prices action.
 */
class Save extends AbstractJsonAction implements HttpPostActionInterface
{
    /**
     * @var FormatInterface
     */
    private $format;

    /**
     * @var WizardFactory
     */
    private $wizardStorageFactory;

    /**
     * @var \Magento\SharedCatalog\Model\ProductItemTierPriceValidator
     */
    private $productItemTierPriceValidator;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * Constructor.
     *
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param FormatInterface $format
     * @param WizardFactory $wizardStorageFactory
     * @param \Magento\SharedCatalog\Model\ProductItemTierPriceValidator $productItemTierPriceValidator
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        FormatInterface $format,
        WizardFactory $wizardStorageFactory,
        \Magento\SharedCatalog\Model\ProductItemTierPriceValidator $productItemTierPriceValidator,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
    ) {
        parent::__construct($context, $resultJsonFactory);
        $this->format = $format;
        $this->wizardStorageFactory = $wizardStorageFactory;
        $this->productItemTierPriceValidator = $productItemTierPriceValidator;
        $this->productRepository = $productRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $request = $this->getRequest();
        $requestTierPrices = (array)$request->getParam('tier_price', []);
        $productId = (int)$request->getParam('product_id');
        $product = $this->productRepository->getById($productId);

        if (!$this->productItemTierPriceValidator->validateDuplicates($requestTierPrices)) {
            return $this->createJsonResponse(
                ['data' => ['status' => false, 'error' => __("We found a duplicate website, tier price or quantity.")]]
            );
        }

        $urlKey = (string)$request->getParam(UrlBuilder::REQUEST_PARAM_CONFIGURE_KEY);
        /** @var \Magento\SharedCatalog\Model\Form\Storage\Wizard $storage */
        $storage = $this->wizardStorageFactory->create(['key' => $urlKey]);

        $storage->deleteTierPrices($product->getSku());
        foreach ($requestTierPrices as $tierPrice) {
            if (isset($tierPrice['delete']) && $tierPrice['delete']) {
                continue;
            }
            $item = [];
            $item['qty'] = $this->format->getNumber($tierPrice['qty']);
            $item['website_id'] = $this->format->getNumber($tierPrice['website_id']);
            $item['value_type'] = $tierPrice['value_type'];
            $item['is_changed'] = true;
            if (isset($tierPrice['price'])) {
                $item['price'] = $this->format->getNumber($tierPrice['price']);
            }
            if (isset($tierPrice['percentage_value'])) {
                $item['percentage_value'] = $this->format->getNumber($tierPrice['percentage_value']);
            }
            $storage->setTierPrices([$product->getSku() => [$item]]);
        }

        return $this->createJsonResponse([
            'data' => ['status' => 1]
        ]);
    }
}
