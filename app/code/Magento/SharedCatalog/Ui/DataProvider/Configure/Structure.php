<?php
namespace Magento\SharedCatalog\Ui\DataProvider\Configure;

use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\SharedCatalog\Model\Form\Storage\WizardFactory as WizardStorageFactory;
use Magento\SharedCatalog\Api\Data\SharedCatalogInterface;

/**
 * Data provider for structure grid.
 */
class Structure extends AbstractDataProvider
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

        return $data;
    }

    /**
     * Prepare shared catalog structure grid data item, check is the product item assigned to the shared catalog.
     *
     * @param \Magento\Framework\DataObject $item
     * @return \Magento\Framework\DataObject
     */
    protected function prepareDataItem(\Magento\Framework\DataObject $item)
    {
        $isAssigned = $this->getStorage()->isProductAssigned($item->getSku());
        $item->setIsAssign($isAssigned);
        return parent::prepareDataItem($item);
    }

    /**
     * Add website filter to a product collection.
     *
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    protected function prepareCollection()
    {
        $collection = parent::prepareCollection();
        $params = $this->request->getParams();

        if (isset($params['filters']['websites'])) {
            $collection->addWebsiteFilter((int)$params['filters']['websites']);
        } elseif (!empty($params[SharedCatalogInterface::SHARED_CATALOG_ID_URL_PARAM])) {
            $collection->addWebsiteFilter($this->stepDataProcessor->retrieveSharedCatalogWebsiteIds(
                $params[SharedCatalogInterface::SHARED_CATALOG_ID_URL_PARAM]
            ));
        }

        return $collection;
    }
}
