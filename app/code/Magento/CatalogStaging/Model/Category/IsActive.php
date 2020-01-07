<?php
namespace Magento\CatalogStaging\Model\Category;

use Magento\Framework\Data\OptionSourceInterface;

class IsActive implements OptionSourceInterface
{
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            [
                'label' => __('Enabled'),
                'value' => 1
            ],
            [
                'label' => __('Disabled'),
                'value' => 0
            ]
        ];
    }
}
