<?php
namespace Magento\GiftCardSharedCatalog\Ui\DataProvider\Modifier;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Type;

/**
 * UI grid price modifier for gift card.
 */
class GiftCard implements \Magento\Ui\DataProvider\Modifier\ModifierInterface
{
    /**
     * @var \Magento\Framework\EntityManager\MetadataPool
     */
    private $metadataPool;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var \Magento\Catalog\Model\Product\Type
     */
    private $productType;

    /**
     * @param ProductRepositoryInterface $productRepository
     * @param Type $productType
     * @param \Magento\Framework\EntityManager\MetadataPool $metadataPool
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        Type $productType,
        \Magento\Framework\EntityManager\MetadataPool $metadataPool
    ) {
        $this->productRepository = $productRepository;
        $this->productType = $productType;
        $this->metadataPool = $metadataPool;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyData(array $data)
    {
        $entityLinkField = $this->metadataPool->getMetadata(\Magento\Catalog\Api\Data\ProductInterface::class)
            ->getIdentifierField();
        $product = $this->productRepository->getById($data[$entityLinkField]);
        $price = $this->productType->priceFactory($data['type_id'])->getMinAmount($product);
        $data['price'] = $price;
        $data['new_price'] = $price;

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyMeta(array $meta)
    {
        return $meta;
    }
}
