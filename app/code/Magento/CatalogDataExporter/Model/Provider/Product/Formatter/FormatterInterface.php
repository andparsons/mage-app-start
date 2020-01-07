<?php
declare(strict_types=1);

namespace Magento\CatalogDataExporter\Model\Provider\Product\Formatter;

/**
 * Interface FormatterInterface
 */
interface FormatterInterface
{
    /**
     * Format provider data row
     *
     * @param array $row
     * @return array
     */
    public function format(array $row) : array;
}
