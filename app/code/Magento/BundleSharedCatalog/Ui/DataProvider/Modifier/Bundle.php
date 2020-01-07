<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\BundleSharedCatalog\Ui\DataProvider\Modifier;

use Magento\Bundle\Model\Product\Price;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\SharedCatalog\Model\Form\Storage\WizardFactory as WizardStorageFactory;
use Magento\SharedCatalog\Model\Form\Storage\UrlBuilder;
use Magento\SharedCatalog\Model\Form\Storage\PriceCalculator;
use Magento\Catalog\Model\Product\Type;

/**
 * UI grid price modifier for bundle products.
 */
class Bundle implements \Magento\Ui\DataProvider\Modifier\ModifierInterface
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
     * @var \Magento\SharedCatalog\Model\Form\Storage\Wizard
     */
    private $storageFactory;

    /**
     * @var \Magento\SharedCatalog\Model\Form\Storage\Wizard
     */
    private $storage;

    /**
     * @var \Magento\SharedCatalog\Model\Form\Storage\PriceCalculator
     */
    private $priceCalculator;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    /**
     * @var \Magento\Catalog\Model\Product\Type
     */
    private $productType;

    /**
     * @param \Magento\Framework\App\RequestInterface $request
     * @param WizardStorageFactory $wizardStorageFactory
     * @param ProductRepositoryInterface $productRepository
     * @param PriceCalculator $priceCalculator
     * @param \Magento\Framework\EntityManager\MetadataPool $metadataPool
     * @param Type $productType
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        WizardStorageFactory $wizardStorageFactory,
        ProductRepositoryInterface $productRepository,
        PriceCalculator $priceCalculator,
        \Magento\Framework\EntityManager\MetadataPool $metadataPool,
        Type $productType
    ) {
        $this->storageFactory = $wizardStorageFactory;
        $this->productRepository = $productRepository;
        $this->priceCalculator = $priceCalculator;
        $this->metadataPool = $metadataPool;
        $this->request = $request;
        $this->productType = $productType;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyData(array $data)
    {
        $entityLinkField = $this->metadataPool->getMetadata(\Magento\Catalog\Api\Data\ProductInterface::class)
            ->getIdentifierField();
        $product = $this->productRepository->getById($data[$entityLinkField]);
        $priceType = $product->getCustomAttribute('price_type')->getValue();
        $priceMin = $priceMax = $priceType == Price::PRICE_TYPE_FIXED ? $product->getPrice() : 0;
        foreach ($this->getBundleOptions($product) as $option) {
            $prices = [];
            foreach ($option->getProductLinks() as $link) {
                $childProduct = $this->productRepository->get($link->getSku());
                $type = $this->productType->factory($childProduct);
                if ($type->isSalable($childProduct)
                    && $this->getStorage()->isProductAssigned($childProduct->getSku())) {
                    $prices[] = $this->getPriceForOption($product, $childProduct, $link, $data['website_id']);
                }
            }
            $prices = array_filter($prices);
            if ($prices) {
                if ($option->getRequired()) {
                    $priceMin += min($prices);
                }
                $priceMax += max($prices);
            }
        }

        $data['max_new_price'] = $this->priceCalculator->calculateNewPriceForProduct(
            $this->request->getParam(UrlBuilder::REQUEST_PARAM_CONFIGURE_KEY),
            $product->getSku(),
            $priceMax,
            $data['website_id']
        );
        $data['new_price'] = $this->priceCalculator->calculateNewPriceForProduct(
            $this->request->getParam(UrlBuilder::REQUEST_PARAM_CONFIGURE_KEY),
            $product->getSku(),
            $priceMin,
            $data['website_id']
        );
        $data['max_price'] = $priceMax;
        $data['price'] = $priceMin;
        $data['currency_type'] = 'percent';
        $data['price_view'] = $product->getCustomAttribute('price_view')->getValue();
        $data['price_type'] = $priceType;

        return $data;
    }

    /**
     * Get bundle options for product.
     *
     * @param ProductInterface $product
     * @return \Magento\Bundle\Api\Data\OptionInterface[]
     */
    private function getBundleOptions(ProductInterface $product)
    {
        return $product->getExtensionAttributes() && $product->getExtensionAttributes()->getBundleProductOptions()
            ? $product->getExtensionAttributes()->getBundleProductOptions()
            : [];
    }

    /**
     * Get price for bundle option.
     *
     * @param ProductInterface $bundleProduct
     * @param ProductInterface $childProduct
     * @param \Magento\Bundle\Api\Data\LinkInterface $link
     * @param int $websiteId [optional]
     * @return mixed
     */
    private function getPriceForOption(
        ProductInterface $bundleProduct,
        ProductInterface $childProduct,
        \Magento\Bundle\Api\Data\LinkInterface $link,
        $websiteId
    ) {
        $priceType = $bundleProduct->getCustomAttribute('price_type')->getValue();
        if ($priceType == Price::PRICE_TYPE_FIXED) {
            if ($link->getPriceType()) {
                $price = $bundleProduct->getPrice() * ($link->getPrice() / 100);
            } else {
                $price = $link->getPrice();
            }
        } else {
            $price = $this->priceCalculator->calculateNewPriceForProduct(
                $this->request->getParam(UrlBuilder::REQUEST_PARAM_CONFIGURE_KEY),
                $childProduct->getSku(),
                $childProduct->getPrice(),
                $websiteId
            );
        }
        return $link->getQty() * $price;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyMeta(array $meta)
    {
        return $meta;
    }

    /**
     * Retrieve storage wizard object from factory.
     *
     * @return \Magento\SharedCatalog\Model\Form\Storage\Wizard
     */
    private function getStorage()
    {
        if (empty($this->storage)) {
            $this->storage = $this->storageFactory->create([
                'key' => $this->request->getParam(UrlBuilder::REQUEST_PARAM_CONFIGURE_KEY)
            ]);
        }
        return $this->storage;
    }
}
