<?php
namespace Magento\Downloadable\Model\System\Config\Source;

/**
 * Downloadable Content Disposition Source
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Contentdisposition implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'attachment', 'label' => __('attachment')],
            ['value' => 'inline', 'label' => __('inline')]
        ];
    }
}
