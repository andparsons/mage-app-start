<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\NegotiableQuote\Model\QuoteUpdatesInfo;

/**
 * Class for retrieving product options.
 */
class ProductOptions
{
    /**
     * @var \Magento\Catalog\Helper\Product\Configuration
     */
    private $productConfig;

    /**
     * @var \Magento\Catalog\Helper\Product\ConfigurationPool
     */
    private $productConfigurationPool;

    /**
     * ProductOptions constructor.
     * @param \Magento\Catalog\Helper\Product\Configuration $productConfig
     * @param \Magento\Catalog\Helper\Product\ConfigurationPool $productConfigurationPool
     */
    public function __construct(
        \Magento\Catalog\Helper\Product\Configuration $productConfig,
        \Magento\Catalog\Helper\Product\ConfigurationPool $productConfigurationPool
    ) {
        $this->productConfig = $productConfig;
        $this->productConfigurationPool = $productConfigurationPool;
    }

    /**
     * Get product configuration by type.
     *
     * @param string $productType
     * @return \Magento\Catalog\Helper\Product\Configuration\ConfigurationInterface
     */
    public function getConfigurationForProductType($productType)
    {
        return $this->productConfigurationPool->getByProductType($productType);
    }

    /**
     * Accept option value and return its formatted view.
     *
     * @param string|array $optionValue
     * @param array $params
     * @return array
     */
    public function getFormattedOptionValue($optionValue, $params = null)
    {
        return $this->productConfig->getFormattedOptionValue($optionValue, $params);
    }
}
