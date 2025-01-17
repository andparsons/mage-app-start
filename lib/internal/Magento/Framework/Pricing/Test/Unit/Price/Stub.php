<?php

namespace Magento\Framework\Pricing\Test\Unit\Price;

use Magento\Framework\Pricing\Price\AbstractPrice;

/**
 * Class Stub for testing abstract class AbstractPrice
 *
 */
class Stub extends AbstractPrice
{
    /**
     * Get price value
     *
     * @return float
     */
    public function getValue()
    {
        $examplePrice = 77.0;
        return $examplePrice;
    }
}
