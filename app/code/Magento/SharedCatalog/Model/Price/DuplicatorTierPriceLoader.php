<?php
namespace Magento\SharedCatalog\Model\Price;

use Magento\Catalog\Api\Data\TierPriceInterface;
use Magento\Catalog\Model\Config\Source\ProductPriceOptionsInterface;

/**
 * Loading tier prices for a products assigned to a shared catalog.
 */
class DuplicatorTierPriceLoader
{
    /**
     * @var \Magento\Catalog\Api\TierPriceStorageInterface
     */
    private $tierPriceStorage;

    /**
     * @var \Magento\Customer\Api\GroupRepositoryInterface
     */
    private $customerGroupRepository;

    /**
     * @param \Magento\Catalog\Api\TierPriceStorageInterface $tierPriceStorage
     * @param \Magento\Customer\Api\GroupRepositoryInterface $customerGroupRepository
     */
    public function __construct(
        \Magento\Catalog\Api\TierPriceStorageInterface $tierPriceStorage,
        \Magento\Customer\Api\GroupRepositoryInterface $customerGroupRepository
    ) {
        $this->tierPriceStorage = $tierPriceStorage;
        $this->customerGroupRepository = $customerGroupRepository;
    }

    /**
     * Loading tier prices for a products assigned to a shared catalog.
     *
     * Loading tier prices of products assigned to the original shared catalog
     * Preparing tier prices data for a shared catalog duplication
     *
     * @param array $skus
     * @param int $customerGroupId
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function load(array $skus, $customerGroupId)
    {
        $tierPrices = [];
        $prices = $this->tierPriceStorage->get($skus);

        if ($prices) {
            $customerGroup = $this->customerGroupRepository->getById($customerGroupId);

            foreach ($prices as $price) {
                if ($price->getCustomerGroup() == $customerGroup->getCode()) {
                    $tierPrice = [
                        'qty' => $price->getQuantity(),
                        'website_id' => $price->getWebsiteId(),
                        'is_changed' => true,
                    ];

                    if ($price->getPriceType() == TierPriceInterface::PRICE_TYPE_FIXED) {
                        $tierPrice['price'] = $price->getPrice();
                        $tierPrice['value_type'] = ProductPriceOptionsInterface::VALUE_FIXED;
                    } else {
                        $tierPrice['percentage_value'] = $price->getPrice();
                        $tierPrice['value_type'] = ProductPriceOptionsInterface::VALUE_PERCENT;
                    }

                    $tierPrices[$price->getSku()][] = $tierPrice;
                }
            }
        }

        return $tierPrices;
    }
}
