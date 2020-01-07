<?php
namespace Magento\Support\Model\Report\Group\Attributes;

/**
 * All Eav Attributes section of Attributes report group
 */
class AllEavAttributesSection extends AbstractAttributesSection
{
    /**
     * {@inheritdoc}
     */
    public function generate()
    {
        $attributeCollection = $this->getAttributesCollection();
        return [
            (string)__('All Eav Attributes') => $this->generateSectionData($attributeCollection)
        ];
    }
}
