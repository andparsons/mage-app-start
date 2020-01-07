<?php
namespace Magento\SharedCatalog\Ui\DataProvider;

/**
 * Prepare websites data for pricing data provider.
 */
class Website implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var array
     */
    private $websites;

    /**
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(\Magento\Store\Model\StoreManagerInterface $storeManager)
    {
        $this->storeManager = $storeManager;
    }

    /**
     * Get websites list for website select.
     *
     * @return array
     */
    public function getWebsites()
    {
        if ($this->websites !== null) {
            return $this->websites;
        }

        $this->websites = [
            [
                'value' => 0,
                'label' => __('All Websites'),
                'store_ids' => [],
            ]
        ];
        $websites = $this->storeManager->getWebsites();

        foreach ($websites as $website) {
            $this->websites[] = [
                'value' => $website->getId(),
                'label' => $website->getName(),
                'store_ids' => $website->getGroupIds(),
            ];
        }

        return $this->websites;
    }

    /**
     * Get first store of the website by website id.
     *
     * @param int $websiteId
     * @return \Magento\Store\Api\Data\StoreInterface|null
     */
    public function getStoreByWebsiteId($websiteId)
    {
        $website = $this->storeManager->getWebsite($websiteId);

        if ($website) {
            $stores = $website->getStores();

            if ($stores) {
                $stores = array_values($stores);
                return $stores[0];
            }
        }

        return null;
    }

    /**
     * @inheritdoc
     */
    public function toOptionArray()
    {
        return $this->getWebsites();
    }
}
