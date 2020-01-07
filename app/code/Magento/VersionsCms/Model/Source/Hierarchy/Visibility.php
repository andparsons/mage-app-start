<?php
namespace Magento\VersionsCms\Model\Source\Hierarchy;

class Visibility implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            \Magento\VersionsCms\Helper\Hierarchy::METADATA_VISIBILITY_PARENT => __('Use Parent'),
            \Magento\VersionsCms\Helper\Hierarchy::METADATA_VISIBILITY_YES => __('Yes'),
            \Magento\VersionsCms\Helper\Hierarchy::METADATA_VISIBILITY_NO => __('No')
        ];
    }
}
