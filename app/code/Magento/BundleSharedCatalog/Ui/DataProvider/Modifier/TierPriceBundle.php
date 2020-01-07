<?php
namespace Magento\BundleSharedCatalog\Ui\DataProvider\Modifier;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Model\Config\Source\ProductPriceOptionsInterface;

/**
 * Modifier for bundle product on shared catalog tier price form.
 */
class TierPriceBundle implements \Magento\Ui\DataProvider\Modifier\ModifierInterface
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var ArrayManager
     */
    private $arrayManager;

    /**
     * @param ProductRepositoryInterface $productRepository
     * @param RequestInterface $request
     * @param ArrayManager $arrayManager
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        RequestInterface $request,
        ArrayManager $arrayManager
    ) {
        $this->productRepository = $productRepository;
        $this->request = $request;
        $this->arrayManager = $arrayManager;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyData(array $data)
    {
        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyMeta(array $meta)
    {
        $productId = $this->request->getParam('product_id');
        $product = $this->productRepository->getById($productId);
        if ($product->getTypeId() == \Magento\Bundle\Model\Product\Type::TYPE_CODE) {
            $tierPricePath = $this->arrayManager->findPath(
                ProductAttributeInterface::CODE_TIER_PRICE,
                $meta,
                null,
                'children'
            );
            $pricePath = $this->arrayManager->findPath(
                ProductAttributeInterface::CODE_TIER_PRICE_FIELD_PRICE,
                $meta,
                $tierPricePath
            );
            $pricePath = $this->arrayManager->slicePath($pricePath, 0, -1) . '/value_type/arguments/data/options';

            $price = $this->arrayManager->get($pricePath, $meta);
            if ($price) {
                $meta = $this->arrayManager->remove($pricePath, $meta);
                foreach ($price as $key => $item) {
                    if ($item['value'] == ProductPriceOptionsInterface::VALUE_FIXED) {
                        unset($price[$key]);
                    }
                }
                $meta = $this->arrayManager->merge(
                    $this->arrayManager->slicePath($pricePath, 0, -1),
                    $meta,
                    ['options' => $price]
                );
            }
        }

        return $meta;
    }
}
