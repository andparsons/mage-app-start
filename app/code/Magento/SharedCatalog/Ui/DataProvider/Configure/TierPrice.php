<?php
namespace Magento\SharedCatalog\Ui\DataProvider\Configure;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;
use Magento\Ui\DataProvider\Modifier\PoolInterface as Modifiers;
use Magento\Framework\App\RequestInterface;
use Magento\SharedCatalog\Model\Form\Storage\UrlBuilder as StorageUrlBuilder;
use Magento\SharedCatalog\Model\Form\Storage\WizardFactory;
use Magento\Store\Model\Store;

/**
 * Data provider for TierPrice form.
 */
class TierPrice extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var Modifiers
     */
    private $modifiers;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var WizardFactory
     */
    private $wizardStorageFactory;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param ProductRepositoryInterface $productRepository
     * @param WizardFactory $wizardStorageFactory
     * @param Modifiers $modifiers
     * @param RequestInterface $request
     * @param array $meta [optional]
     * @param array $data [optional]
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        ProductRepositoryInterface $productRepository,
        WizardFactory $wizardStorageFactory,
        Modifiers $modifiers,
        RequestInterface $request,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->productRepository = $productRepository;
        $this->request = $request;
        $this->wizardStorageFactory = $wizardStorageFactory;
        $this->modifiers = $modifiers;
    }

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @inheritdoc
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getMeta()
    {
        $meta = parent::getMeta();

        /** @var ModifierInterface $modifier */
        foreach ($this->modifiers->getModifiersInstances() as $modifier) {
            $meta = $modifier->modifyMeta($meta);
        }

        return $meta;
    }

    /**
     * @inheritdoc
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getData()
    {
        $productId = $this->request->getParam('product_id');
        $product = $this->productRepository->getById($productId, false, Store::DEFAULT_STORE_ID, true);

        $urlKey = $this->request->getParam(StorageUrlBuilder::REQUEST_PARAM_CONFIGURE_KEY);

        /** @var \Magento\SharedCatalog\Model\Form\Storage\Wizard $storage */
        $storage = $this->wizardStorageFactory->create(['key' => $urlKey]);

        $prices = $storage->getTierPrices($product->getSku());
        $data[$productId]['product_id'] = $productId;
        $data[$productId]['base_price'] = $product->getPrice();
        $data[$productId]['tier_price'] = array_values(array_reverse($prices));
        $data[$productId][StorageUrlBuilder::REQUEST_PARAM_CONFIGURE_KEY] = $urlKey;

        /** @var ModifierInterface $modifier */
        foreach ($this->modifiers->getModifiersInstances() as $modifier) {
            $data = $modifier->modifyData($data);
        }

        return $data;
    }

    /**
     * @inheritdoc
     */
    public function addFilter(\Magento\Framework\Api\Filter $filter)
    {
        return $this;
    }
}
