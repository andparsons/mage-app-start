<?php

/**
 * Config source reports event store filter
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Config\Model\Config\Source\Reports;

/**
 * @api
 * @since 100.0.2
 */
class Scope implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Scope filter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'website', 'label' => __('Website')],
            ['value' => 'group', 'label' => __('Store')],
            ['value' => 'store', 'label' => __('Store View')]
        ];
    }
}
