<?php
namespace Magento\SharedCatalog\Ui\DataProvider;

use Magento\Ui\DataProvider\Modifier\PoolInterface as Modifiers;
use Magento\SharedCatalog\Api\Data\SharedCatalogInterface;

/**
 * Shared catalog edit form data provider.
 */
class SharedCatalog extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var array
     */
    private $loadedData;

    /**
     * @var \Magento\Ui\DataProvider\Modifier\PoolInterface
     */
    private $modifiers;

    /**
     * SharedCatalog constructor.
     *
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param \Magento\SharedCatalog\Ui\DataProvider\Collection\SharedCatalogFactory $collectionFactory
     * @param Modifiers $modifiers
     * @param array $meta [optional]
     * @param array $data [optional]
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        \Magento\SharedCatalog\Ui\DataProvider\Collection\SharedCatalogFactory $collectionFactory,
        Modifiers $modifiers,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collectionFactory->create();
        $this->modifiers = $modifiers;
    }

    /**
     * Prepare shared catalog information.
     *
     * @param \Magento\Framework\Api\Search\DocumentInterface $catalog
     * @return array
     */
    public function getCatalogDetailsData(\Magento\Framework\Api\Search\DocumentInterface $catalog)
    {
        return [
            'name' => $catalog->getName(),
            'description' => $catalog->getDescription(),
            'customer_group_id' => $catalog->getCustomerGroupId(),
            'type' => $catalog->getType(),
            'tax_class_id' => $catalog->getTaxClassId(),
            'created_at' => $catalog->getCreatedAt(),
            'created_by' => $catalog->getCreatedBy(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }

        $items = $this->collection->getItems();

        /** @var \Magento\Framework\Api\Search\DocumentInterface $sharedCatalog */
        foreach ($items as $sharedCatalog) {
            $result['catalog_details'] = $this->getCatalogDetailsData($sharedCatalog);
            $result[SharedCatalogInterface::SHARED_CATALOG_ID_URL_PARAM] = $sharedCatalog->getId();
            $this->loadedData[$sharedCatalog->getId()] = $result;
        }

        $data = ['items' => $this->loadedData, 'config' => $this->getConfigData()];
        foreach ($this->modifiers->getModifiersInstances() as $modifier) {
            $data = $modifier->modifyData($data);
        }
        $this->loadedData = $data['items'];
        $this->setConfigData($data['config']);

        return $this->loadedData;
    }
}
