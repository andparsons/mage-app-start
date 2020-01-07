<?php

namespace Magento\RequisitionList\Model;

/**
 * Actions with product for the requisition list.
 */
class RequisitionListProduct
{
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var \Magento\Framework\Serialize\SerializerInterface
     */
    private $serializer;

    /**
     * @var array
     */
    private $products = [];

    /**
     * @var array
     */
    private $productTypesToConfigure;

    /**
     * @var \Magento\Catalog\Model\Product\Type
     */
    private $productType;

    /**
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Framework\Serialize\SerializerInterface $serializer
     * @param \Magento\Catalog\Model\Product\Type $productType
     * @param array $productTypesToConfigure [optional]
     */
    public function __construct(
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Framework\Serialize\SerializerInterface $serializer,
        \Magento\Catalog\Model\Product\Type $productType,
        array $productTypesToConfigure = []
    ) {
        $this->productRepository = $productRepository;
        $this->serializer = $serializer;
        $this->productType = $productType;
        $this->productTypesToConfigure = $productTypesToConfigure;
    }

    /**
     * Get product by sku.
     *
     * Returns product object if product with provided sku is existed and visible in catalog and false if product
     * with this sku is not existed or not visible
     *
     * @param string $sku
     * @return \Magento\Catalog\Api\Data\ProductInterface|bool
     */
    public function getProduct($sku)
    {
        if (!isset($this->products[$sku])) {
            try {
                $product = $this->productRepository->get($sku);

                if ($product->isVisibleInCatalog()) {
                    $this->products[$sku] = $product;
                } else {
                    $this->products[$sku] = false;
                }
            } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                $this->products[$sku] = false;
            }
        }

        return $this->products[$sku];
    }

    /**
     * Check is it necessary to configure product.
     *
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @return bool
     */
    public function isProductShouldBeConfigured(\Magento\Catalog\Api\Data\ProductInterface $product)
    {
        if (in_array($product->getTypeId(), $this->productTypesToConfigure)) {
            return true;
        }

        $typeInstance = $this->productType->factory($product);
        return $typeInstance->hasRequiredOptions($product);
    }

    /**
     * Prepare product information.
     *
     * @param string $productData
     * @return \Magento\Framework\DataObject
     */
    public function prepareProductData($productData)
    {
        $productData = $this->serializer->unserialize($productData);

        if (isset($productData['options'])) {
            $options = [];
            parse_str($productData['options'], $options);
            $productData['options'] = $options;
        }

        return new \Magento\Framework\DataObject($productData);
    }
}
