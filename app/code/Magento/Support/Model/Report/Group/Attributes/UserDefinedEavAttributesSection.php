<?php
namespace Magento\Support\Model\Report\Group\Attributes;

/**
 * User Defined Eav Attributes section of Attributes report group
 */
class UserDefinedEavAttributesSection extends AbstractAttributesSection
{
    /**
     * {@inheritdoc}
     */
    public function generate()
    {
        $attributeCollection = $this->getAttributesCollection(['is_user_defined' => 1]);
        return [
            (string)__('User Defined Eav Attributes') => $this->generateSectionData(
                $attributeCollection,
                ['is_user_defined']
            )
        ];
    }
}
