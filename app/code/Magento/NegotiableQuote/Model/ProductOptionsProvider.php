<?php

namespace Magento\NegotiableQuote\Model;

/**
 * Responsible for retrieving simple product customizable options and transforming them into array.
 */
class ProductOptionsProvider implements ProductOptionsProviderInterface
{
    /**
     * @inheritdoc
     */
    public function getProductType()
    {
        return \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE;
    }

    /**
     * @inheritdoc
     */
    public function getOptions(\Magento\Catalog\Model\Product $product)
    {
        $optionsArray = [];
        $options = $product->getOptionInstance()->getProductOptions($product);
        foreach ($options as $option) {
            /** @var \Magento\Catalog\Api\Data\ProductCustomOptionInterface $option */
            $optionsArray[$option->getOptionId()] = [
                'label' => $option->getTitle(),
                'values' => []
            ];

            if ($option->getValues()) {
                foreach ($option->getValues() as $value) {
                    $optionsArray[$option->getOptionId()]['values'][] = [
                        'value_index' => $value->getOptionTypeId(),
                        'label' => $value->getTitle()
                    ];
                }
            }
        }

        return $optionsArray;
    }
}
