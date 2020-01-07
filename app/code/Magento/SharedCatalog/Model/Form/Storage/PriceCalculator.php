<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SharedCatalog\Model\Form\Storage;

use Magento\SharedCatalog\Model\Form\Storage\WizardFactory as WizardStorageFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;

/**
 * Class for calculate price for product with storage price.
 */
class PriceCalculator
{
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var \Magento\SharedCatalog\Model\Form\Storage\Wizard
     */
    private $storageFactory;

    /**
     * @var \Magento\SharedCatalog\Model\Form\Storage\Wizard[]
     */
    private $storage = [];

    /**
     * @var \Magento\Framework\Locale\FormatInterface
     */
    private $localeFormat;

    /**
     * @var \Magento\SharedCatalog\Model\ProductItemTierPriceValidator
     */
    private $productItemTierPriceValidator;

    /**
     * @param WizardStorageFactory $wizardStorageFactory
     * @param ProductRepositoryInterface $productRepository
     * @param \Magento\SharedCatalog\Model\ProductItemTierPriceValidator $productItemTierPriceValidator
     * @param \Magento\Framework\Locale\FormatInterface $localeFormat
     */
    public function __construct(
        WizardStorageFactory $wizardStorageFactory,
        ProductRepositoryInterface $productRepository,
        \Magento\SharedCatalog\Model\ProductItemTierPriceValidator $productItemTierPriceValidator,
        \Magento\Framework\Locale\FormatInterface $localeFormat
    ) {
        $this->storageFactory = $wizardStorageFactory;
        $this->productRepository = $productRepository;
        $this->productItemTierPriceValidator = $productItemTierPriceValidator;
        $this->localeFormat = $localeFormat;
    }

    /**
     * Retrieve storage wizard object from factory by $storageKey.
     *
     * @param string $storageKey [optional]
     * @return \Magento\SharedCatalog\Model\Form\Storage\Wizard
     */
    private function getStorage($storageKey = '')
    {
        if (empty($this->storage[$storageKey])) {
            $this->storage[$storageKey] = $this->storageFactory->create([
                'key' => $storageKey
            ]);
        }
        return $this->storage[$storageKey];
    }

    /**
     * Get new price for product.
     *
     * @param string $storageKey
     * @param string $productSku
     * @param float $oldPrice [optional]
     * @param int $websiteId [optional]
     * @return float|null
     */
    public function calculateNewPriceForProduct($storageKey, $productSku, $oldPrice = 0.00, $websiteId = 0)
    {
        $customPrices = $this->getStorage($storageKey)->getProductPrices($productSku);
        if (!$websiteId && $this->productItemTierPriceValidator->existsPricePerWebsite($customPrices)) {
            return null;
        }

        $customPrice = $this->getStorage($storageKey)->getProductPrice($productSku, $websiteId);

        if (is_array($customPrice) && !empty($customPrice)) {
            $priceType = $customPrice['value_type'];
            if ($priceType == 'fixed') {
                return $this->localeFormat->getNumber($customPrice['price']);
            } else {
                return $oldPrice * (1 - $customPrice['percentage_value'] / 100);
            }
        }
        return $oldPrice;
    }
}
