<?php

namespace Magento\SharedCatalog\Model\ResourceModel\ProductItem\Price;

/**
 * Prepares prices data for updating and deleting, prepares error message if process of price update/delete is failed.
 */
class PriceProcessor
{
    /**
     * @var \Magento\Catalog\Api\Data\TierPriceInterfaceFactory
     */
    private $tierPriceFactory;

    /**
     * @param \Magento\Catalog\Api\Data\TierPriceInterfaceFactory $tierPriceFactory
     */
    public function __construct(
        \Magento\Catalog\Api\Data\TierPriceInterfaceFactory $tierPriceFactory
    ) {
        $this->tierPriceFactory = $tierPriceFactory;
    }

    /**
     * Create tier prices DTO and populate it with data from the operation for update.
     *
     * @param array $operationData
     * @return \Magento\Catalog\Api\Data\TierPriceInterface[]
     */
    public function createPricesUpdate(array $operationData)
    {
        $pricesDataForUpdate = [];
        foreach ($operationData['prices'] as $priceData) {
            if (!empty($priceData['is_deleted'])) {
                continue;
            }
            $pricesDataForUpdate[] = $this->createPrice(
                $operationData['product_sku'],
                $operationData['customer_group'],
                $priceData
            );
        }
        return $pricesDataForUpdate;
    }

    /**
     * Create tier prices DTO and populate it with data from the operation for delete.
     *
     * @param array $operationData
     * @return \Magento\Catalog\Api\Data\TierPriceInterface[]
     */
    public function createPricesDelete(array $operationData)
    {
        $pricesDataForDelete = [];
        foreach ($operationData['prices'] as $priceData) {
            if (empty($priceData['is_deleted'])) {
                continue;
            }
            $pricesDataForDelete[] = $this->createPrice(
                $operationData['product_sku'],
                $operationData['customer_group'],
                $priceData
            );
        }
        return $pricesDataForDelete;
    }

    /**
     * Get formatted price update error message by replacing placeholders in it with values.
     *
     * @param \Magento\Catalog\Api\Data\PriceUpdateResultInterface $result
     * @return string
     */
    public function prepareErrorMessage(\Magento\Catalog\Api\Data\PriceUpdateResultInterface $result)
    {
        $message = $result->getMessage();
        foreach ($result->getParameters() as $placeholder => $value) {
            $message = str_replace('%' . $placeholder, $value, $message);
        }
        return $message;
    }

    /**
     * Create tier prices DTO with $sku, $group and price type and value form $price.
     *
     * @param string $sku
     * @param string $group
     * @param array $priceData
     * @return \Magento\Catalog\Api\Data\TierPriceInterface
     */
    private function createPrice($sku, $group, array $priceData)
    {
        /** @var \Magento\Catalog\Api\Data\TierPriceInterface $price */
        $price = $this->tierPriceFactory->create();
        $price
            ->setWebsiteId($priceData['website_id'])
            ->setSku($sku)
            ->setCustomerGroup($group)
            ->setQuantity($priceData['qty']);
        if ($priceData['value_type'] == \Magento\Catalog\Api\Data\TierPriceInterface::PRICE_TYPE_FIXED) {
            $price
                ->setPrice($priceData['price'])
                ->setPriceType(\Magento\Catalog\Api\Data\TierPriceInterface::PRICE_TYPE_FIXED);
        } else {
            $price
                ->setPrice($priceData['percentage_value'])
                ->setPriceType(\Magento\Catalog\Api\Data\TierPriceInterface::PRICE_TYPE_DISCOUNT);
        }
        return $price;
    }
}
