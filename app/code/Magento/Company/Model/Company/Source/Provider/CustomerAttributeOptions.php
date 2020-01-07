<?php
declare(strict_types=1);

namespace Magento\Company\Model\Company\Source\Provider;

use Magento\Eav\Model\Entity\AttributeFactory;
use Magento\Eav\Model\Entity\Attribute;

/**
 * Provides options for a customer attribute.
 */
class CustomerAttributeOptions
{
    /**
     * @var AttributeFactory
     */
    private $attributeFactory;

    /**
     * @param AttributeFactory $attributeFactory
     */
    public function __construct(AttributeFactory $attributeFactory)
    {
        $this->attributeFactory = $attributeFactory;
    }

    /**
     * Load options for a certain customer attribute.
     *
     * @param string $attributeCode
     *
     * @return array[] With "label" and "value" keys.
     */
    public function loadOptions(string $attributeCode): array
    {
        $result = [];
        /** @var Attribute $attribute */
        $attribute = $this->attributeFactory->create();
        $attribute->loadByCode('customer', $attributeCode);
        $options = $attribute->getOptions();
        foreach ($options as $item) {
            $result[] = ['label' => $item->getLabel(), 'value' => $item->getValue()];
        }

        return $result;
    }
}
