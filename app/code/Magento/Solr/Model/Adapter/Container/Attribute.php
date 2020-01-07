<?php

namespace Magento\Solr\Model\Adapter\Container;

use Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection;

class Attribute
{
    /**
     * @var string[]
     */
    private $idToCodeMap = [];

    /**
     * @var Collection
     */
    private $attributeCollection;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Eav\Attribute[]
     */
    private $searchableAttributes = [];

    /**
     * @param Collection $attributeCollection
     */
    public function __construct(Collection $attributeCollection)
    {
        $this->attributeCollection = $attributeCollection;
    }

    /**
     * @param int $attributeId
     * @return string
     */
    public function getAttributeCodeById($attributeId)
    {
        if (!array_key_exists($attributeId, $this->idToCodeMap)) {
            $code = $attributeId === 'options'
                ? 'options'
                : $this->attributeCollection->getItemById($attributeId)->getAttributeCode();
            $this->idToCodeMap[$attributeId] = $code;
        }
        return $this->idToCodeMap[$attributeId];
    }

    /**
     * @param string $attributeCode
     * @return int
     */
    public function getAttributeIdByCode($attributeCode)
    {
        if (!array_key_exists($attributeCode, array_flip($this->idToCodeMap))) {
            $attributeId = $attributeCode === 'options'
                ? 'options'
                : $this->attributeCollection->getItemByColumnValue('attribute_code', $attributeCode)->getId();
            $this->idToCodeMap[$attributeId] = $attributeCode;
        }
        $codeToIdMap = array_flip($this->idToCodeMap);
        return $codeToIdMap[$attributeCode];
    }

    /**
     * @param string $attributeCode
     * @return \Magento\Catalog\Model\ResourceModel\Eav\Attribute|null
     */
    public function getSearchableAttribute($attributeCode)
    {
        $searchableAttributes = $this->getSearchableAttributes();
        return array_key_exists($attributeCode, $searchableAttributes)
            ? $searchableAttributes[$attributeCode]
            : null;
    }

    /**
     * @return \Magento\Catalog\Model\ResourceModel\Attribute[]
     */
    public function getSearchableAttributes()
    {
        if (0 === count($this->searchableAttributes)) {
            /** @var \Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection $attributesCollection */
            $attributesCollection = $this->attributeCollection->addToIndexFilter(true);

            foreach ($attributesCollection as $attribute) {
                /** @var \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attribute */
                $this->searchableAttributes[$attribute->getAttributeCode()] = $attribute;
            }
        }

        return $this->searchableAttributes;
    }
}
