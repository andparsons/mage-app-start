<?php
namespace Magento\ConfigurableProduct\Model\Quote\Item;

use Magento\Quote\Model\Quote\Item\CartItemProcessorInterface;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\App\ObjectManager;

class CartItemProcessor implements CartItemProcessorInterface
{
    /**
     * @var \Magento\Framework\DataObject\Factory
     */
    protected $objectFactory;

    /**
     * @var \Magento\Quote\Model\Quote\ProductOptionFactory
     */
    protected $productOptionFactory;

    /**
     * @var \Magento\Quote\Api\Data\ProductOptionExtensionFactory
     */
    protected $extensionFactory;

    /**
     * @var \Magento\ConfigurableProduct\Model\Quote\Item\ConfigurableItemOptionValueFactory
     */
    protected $itemOptionValueFactory;

    /**
     * @var Json
     */
    private $serializer;

    /**
     * @param \Magento\Framework\DataObject\Factory $objectFactory
     * @param \Magento\Quote\Model\Quote\ProductOptionFactory $productOptionFactory
     * @param \Magento\Quote\Api\Data\ProductOptionExtensionFactory $extensionFactory
     * @param \Magento\ConfigurableProduct\Model\Quote\Item\ConfigurableItemOptionValueFactory $itemOptionValueFactory
     * @param Json $serializer
     */
    public function __construct(
        \Magento\Framework\DataObject\Factory $objectFactory,
        \Magento\Quote\Model\Quote\ProductOptionFactory $productOptionFactory,
        \Magento\Quote\Api\Data\ProductOptionExtensionFactory $extensionFactory,
        \Magento\ConfigurableProduct\Model\Quote\Item\ConfigurableItemOptionValueFactory $itemOptionValueFactory,
        Json $serializer = null
    ) {
        $this->objectFactory = $objectFactory;
        $this->productOptionFactory = $productOptionFactory;
        $this->extensionFactory = $extensionFactory;
        $this->itemOptionValueFactory = $itemOptionValueFactory;
        $this->serializer = $serializer ?: ObjectManager::getInstance()->get(Json::class);
    }

    /**
     * @inheritdoc
     */
    public function convertToBuyRequest(CartItemInterface $cartItem)
    {
        if ($cartItem->getProductOption() && $cartItem->getProductOption()->getExtensionAttributes()) {
            /** @var \Magento\ConfigurableProduct\Api\Data\ConfigurableItemOptionValueInterface $options */
            $options = $cartItem->getProductOption()->getExtensionAttributes()->getConfigurableItemOptions();
            if (is_array($options)) {
                $requestData = [];
                foreach ($options as $option) {
                    $requestData['super_attribute'][$option->getOptionId()] = (string) $option->getOptionValue();
                }
                return $this->objectFactory->create($requestData);
            }
        }
        return null;
    }

    /**
     * @inheritdoc
     */
    public function processOptions(CartItemInterface $cartItem)
    {
        $attributesOption = $cartItem->getProduct()->getCustomOption('attributes');
        $selectedConfigurableOptions = $this->serializer->unserialize($attributesOption->getValue());

        if (is_array($selectedConfigurableOptions)) {
            $configurableOptions = [];
            foreach ($selectedConfigurableOptions as $optionId => $optionValue) {
                /** @var \Magento\ConfigurableProduct\Api\Data\ConfigurableItemOptionValueInterface $option */
                $option = $this->itemOptionValueFactory->create();
                $option->setOptionId($optionId);
                $option->setOptionValue($optionValue);
                $configurableOptions[] = $option;
            }

            $productOption = $cartItem->getProductOption()
                ? $cartItem->getProductOption()
                : $this->productOptionFactory->create();

            /** @var  \Magento\Quote\Api\Data\ProductOptionExtensionInterface $extensibleAttribute */
            $extensibleAttribute =  $productOption->getExtensionAttributes()
                ? $productOption->getExtensionAttributes()
                : $this->extensionFactory->create();

            $extensibleAttribute->setConfigurableItemOptions($configurableOptions);
            $productOption->setExtensionAttributes($extensibleAttribute);
            $cartItem->setProductOption($productOption);
        }
        return $cartItem;
    }
}
