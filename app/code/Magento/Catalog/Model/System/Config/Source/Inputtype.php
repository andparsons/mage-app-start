<?php
namespace Magento\Catalog\Model\System\Config\Source;

class Inputtype
{
    /**
     * Get input types which use predefined source
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'multiselect', 'label' => __('Multiple Select')],
            ['value' => 'select', 'label' => __('Dropdown')]
        ];
    }
}
