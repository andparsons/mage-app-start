<?php
declare(strict_types=1);

namespace Magento\CatalogDataExporter\Model\Provider\Product;

/**
 * Interface OptionProviderInterface
 */
interface OptionProviderInterface
{
    /**
     * Get option values
     *
     * @param array $values
     * @return array
     */
    public function get(array $values) : array;
}
