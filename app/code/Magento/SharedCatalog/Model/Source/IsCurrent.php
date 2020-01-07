<?php
namespace Magento\SharedCatalog\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Provide options for isCurrent column on the company assign grid of shared catalog.
 */
class IsCurrent implements OptionSourceInterface
{
    /**
     * @inheritdoc
     */
    public function toOptionArray()
    {
        return [
            [
               'label' => __('Yes'),
               'value' => 1
            ],
            [
               'label' => __('No'),
               'value' => 0
            ]
        ];
    }
}
