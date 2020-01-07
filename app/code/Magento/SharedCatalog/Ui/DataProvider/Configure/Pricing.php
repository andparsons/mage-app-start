<?php
namespace Magento\SharedCatalog\Ui\DataProvider\Configure;

use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\SharedCatalog\Model\Form\Storage\WizardFactory as WizardStorageFactory;
use Magento\SharedCatalog\Api\Data\SharedCatalogInterface;

/**
 * Data provider for Configure Pricing grid.
 */
class Pricing extends AbstractDataProvider
{
    /**
     * @var \Magento\SharedCatalog\Ui\DataProvider\Configure\StepDataProcessor
     */
    private $stepDataProcessor;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param \Magento\Framework\App\RequestInterface $request
     * @param WizardStorageFactory $wizardStorageFactory
     * @param \Magento\SharedCatalog\Model\ResourceModel\CategoryTree $categoryTree
     * @param \Magento\SharedCatalog\Ui\DataProvider\Configure\StepDataProcessor $stepDataProcessor
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param array $meta [optional]
     * @param array $data [optional]
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        \Magento\Framework\App\RequestInterface $request,
        WizardStorageFactory $wizardStorageFactory,
        \Magento\SharedCatalog\Model\ResourceModel\CategoryTree $categoryTree,
        \Magento\SharedCatalog\Ui\DataProvider\Configure\StepDataProcessor $stepDataProcessor,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct(
            $name,
            $primaryFieldName,
            $requestFieldName,
            $request,
            $wizardStorageFactory,
            $categoryTree,
            $storeManager,
            $meta,
            $data
        );
        $this->stepDataProcessor = $stepDataProcessor;
    }

    /**
     * @inheritdoc
     */
    public function getData()
    {
        $data = parent::getData();
        $data = $this->stepDataProcessor->modifyData($data);
        $data['websites'] = $this->stepDataProcessor->getWebsites();
        $data['isChanged'] = $this->checkPriceIsChanged();

        return $data;
    }

    /**
     * @inheritdoc
     * @param \Magento\Framework\DataObject $item
     * @return \Magento\Framework\DataObject
     */
    protected function prepareDataItem(\Magento\Framework\DataObject $item)
    {
        $customPrices = $this->getStorage()->getProductPrices($item->getSku());
        $customPrice = $this->stepDataProcessor->prepareCustomPrice($customPrices);
        $priceType = \Magento\Catalog\Model\Config\Source\ProductPriceOptionsInterface::VALUE_FIXED;
        if (is_array($customPrice) && !empty($customPrice)) {
            $priceType = $customPrice['value_type'];
            if ($priceType == \Magento\Catalog\Api\Data\TierPriceInterface::PRICE_TYPE_FIXED) {
                $item->setCustomPrice($customPrice['price']);
                $priceType = \Magento\Catalog\Model\Config\Source\ProductPriceOptionsInterface::VALUE_FIXED;
            } else {
                $item->setCustomPrice($customPrice['percentage_value']);
                $priceType = \Magento\Catalog\Model\Config\Source\ProductPriceOptionsInterface::VALUE_PERCENT;
            }
        }
        $item->setPriceType($priceType);
        $item->setOriginPrice($item->getPrice());

        $tierPrices = $this->getStorage()->getTierPrices($item->getSku());
        $item->setData('tier_price_count', count($tierPrices));
        $item->setData('custom_price_enabled', $this->stepDataProcessor->isCustomPriceEnabled($customPrices));

        return parent::prepareDataItem($item);
    }

    /**
     * @inheritdoc
     */
    protected function prepareCollection()
    {
        $this->stepDataProcessor->switchCurrentStore();
        $collection = parent::prepareCollection();
        $collection->addAttributeToFilter('sku', ['in' => $this->getStorage()->getAssignedProductSkus()]);
        $params = $this->request->getParams();

        if (!empty($params['filters']['websites'])) {
            $collection->addWebsiteFilter($params['filters']['websites']);
        } else {
            $sharedCatalogId = (int)$this->request->getParam(SharedCatalogInterface::SHARED_CATALOG_ID_URL_PARAM);
            $collection->addWebsiteFilter(
                $this->stepDataProcessor->retrieveSharedCatalogWebsiteIds($sharedCatalogId)
            );
        }

        return $collection;
    }

    /**
     * Check if any tier price was changed.
     *
     * @return bool
     */
    public function checkPriceIsChanged()
    {
        $customPrices = $this->getStorage()->getTierPrices(null, true);
        foreach ($customPrices as $product) {
            foreach ($product as $data) {
                if (isset($data['is_changed']) && $data['is_changed']) {
                    return true;
                }
            }
        }
        return false;
    }
}
