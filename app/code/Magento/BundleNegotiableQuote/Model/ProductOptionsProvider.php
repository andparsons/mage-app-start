<?php

namespace Magento\BundleNegotiableQuote\Model;

use Magento\NegotiableQuote\Model\ProductOptionsProviderInterface;

/**
 * Responsible for retrieving bundle product options and transforming them into array.
 */
class ProductOptionsProvider implements ProductOptionsProviderInterface
{
    /**
     * @inheritdoc
     */
    public function getProductType()
    {
        return \Magento\Bundle\Model\Product\Type::TYPE_CODE;
    }

    /**
     * @inheritdoc
     */
    public function getOptions(\Magento\Catalog\Model\Product $product)
    {
        $optionsArray = [];
        $typeInstance = $product->getTypeInstance();
        $typeInstance->setStoreFilter($product->getStoreId(), $product);
        $optionCollection = $typeInstance->getOptionsCollection($product);
        $selectionCollection = $typeInstance->getSelectionsCollection(
            $typeInstance->getOptionsIds($product),
            $product
        );
        $attributesArray = $optionCollection->appendSelections(
            $selectionCollection
        );

        foreach ($attributesArray as $value) {
            /** @var \Magento\Bundle\Api\Data\OptionInterface $value */
            $optionsArray[$value->getOptionId()] = [
                'label' => $value->getTitle(),
                'values' => []
            ];

            foreach ($value->getSelections() as $select) {
                $optionsArray[$value->getOptionId()]['values'][] = [
                    'value_index' => $select->getSelectionId(),
                    'label' => $select->getName()
                ];
            }
        }

        return $optionsArray;
    }
}
