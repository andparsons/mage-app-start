<?php
namespace Magento\Solr\Model\Adapter;

/**
 * Class FieldMapper
 */
class FieldType
{
    /**
     * @param \Magento\Eav\Model\Entity\Attribute\AbstractAttribute $attribute
     * @return string
     */
    public function getFieldType($attribute)
    {
        $backendType = $attribute->getBackendType();
        $frontendInput = $attribute->getFrontendInput();

        if ($backendType === 'decimal' || $backendType === 'datetime') {
            $fieldType = $backendType;
        } elseif (in_array($frontendInput, ['multiselect', 'select', 'boolean'], true)) {
            $fieldType = 'int';
        } else {
            $fieldType = 'string';
        }
        return $fieldType;
    }
}
