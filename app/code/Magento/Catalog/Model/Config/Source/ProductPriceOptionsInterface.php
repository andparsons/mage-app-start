<?php
namespace Magento\Catalog\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Interface ProductPriceOptionsInterface
 */
interface ProductPriceOptionsInterface extends OptionSourceInterface
{
    /**#@+
     * Values
     */
    const VALUE_FIXED = 'fixed';
    const VALUE_PERCENT = 'percent';
    /**#@-*/
}
