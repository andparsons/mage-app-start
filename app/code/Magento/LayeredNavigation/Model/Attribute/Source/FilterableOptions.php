<?php
namespace Magento\LayeredNavigation\Model\Attribute\Source;

/**
 * @api
 * @since 100.0.2
 */
class FilterableOptions implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => 0,
                'label' => __('No'),
            ],
            [
                'value' => 1,
                'label' => __('Filterable (with results)'),
            ],
            [
                'value' => 2,
                'label' => __('Filterable (no results)'),
            ],
        ];
    }
}
