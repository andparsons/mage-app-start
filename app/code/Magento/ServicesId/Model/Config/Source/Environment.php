<?php
declare(strict_types=1);

namespace Magento\ServicesId\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * System configuration source class for Environment dropdown selector
 *
 * @api
 */
class Environment implements OptionSourceInterface
{
    /**
     * Value Constants
     */
    const NON_PRODUCTION_VALUE = 'Testing';
    const PRODUCTION_VALUE = 'Production';

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray() : array
    {
        return [
            ['value' => self::NON_PRODUCTION_VALUE, 'label' => __(self::NON_PRODUCTION_VALUE)],
            ['value' => self::PRODUCTION_VALUE, 'label' => __(self::PRODUCTION_VALUE)]
        ];
    }
}
