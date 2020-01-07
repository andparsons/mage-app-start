<?php
namespace Magento\Ups\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class Type
 */
class Type implements OptionSourceInterface
{
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'UPS', 'label' => __('United Parcel Service')],
            ['value' => 'UPS_XML', 'label' => __('United Parcel Service XML')]
        ];
    }
}
