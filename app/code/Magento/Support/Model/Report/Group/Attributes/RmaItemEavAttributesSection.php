<?php
namespace Magento\Support\Model\Report\Group\Attributes;

use Magento\Rma\Api\RmaAttributesManagementInterface;

/**
 * Rma Item Eav Attributes section of Attributes report group
 */
class RmaItemEavAttributesSection extends AbstractAttributesSection
{
    /**
     * {@inheritdoc}
     */
    public function generate()
    {
        $attributeCollection = $this->getAttributesCollection(
            ['entity_type_id' => $this->getEntityTypeId(RmaAttributesManagementInterface::ENTITY_TYPE)]
        );
        return [
            (string)__('Rma Item Eav Attributes') => $this->generateSectionData(
                $attributeCollection,
                ['entity_type_code']
            )
        ];
    }
}
