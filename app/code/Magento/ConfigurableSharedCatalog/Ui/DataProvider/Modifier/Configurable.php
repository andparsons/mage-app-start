<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableSharedCatalog\Ui\DataProvider\Modifier;

use Magento\SharedCatalog\Model\Form\Storage\WizardFactory as WizardStorageFactory;
use Magento\SharedCatalog\Model\Form\Storage\UrlBuilder;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\SharedCatalog\Model\Form\Storage\PriceCalculator;

/**
 * UI grid price modifier for configurable products.
 */
class Configurable implements \Magento\Ui\DataProvider\Modifier\ModifierInterface
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
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    /**
     * @var \Magento\SharedCatalog\Model\Form\Storage\Wizard
     */
    private $storage;

    /**
     * @var \Magento\SharedCatalog\Model\Form\Storage\PriceCalculator
     */
    private $priceCalculator;

    /**
     * @param \Magento\Framework\App\RequestInterface $request
     * @param WizardStorageFactory $wizardStorageFactory
     * @param ProductRepositoryInterface $productRepository
     * @param PriceCalculator $priceCalculator
     * @param \Magento\Framework\EntityManager\MetadataPool $metadataPool
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        WizardStorageFactory $wizardStorageFactory,
        ProductRepositoryInterface $productRepository,
        PriceCalculator $priceCalculator,
        \Magento\Framework\EntityManager\MetadataPool $metadataPool
    ) {
        $this->request = $request;
        $this->storageFactory = $wizardStorageFactory;
        $this->productRepository = $productRepository;
        $this->priceCalculator = $priceCalculator;
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
        $childs = $product->getExtensionAttributes()->getConfigurableProductLinks();
        $pricesOriginal = [];
        $prices = [];
        foreach ($childs as $child) {
            $childProduct = $this->productRepository->getById($child);
            if ($this->getStorage()->isProductAssigned($childProduct->getSku())) {
                $prices[] = $this->priceCalculator->calculateNewPriceForProduct(
                    $this->request->getParam(UrlBuilder::REQUEST_PARAM_CONFIGURE_KEY),
                    $childProduct->getSku(),
                    $childProduct->getPrice(),
                    $data['website_id']
                );
                $pricesOriginal[] = $childProduct->getPrice();
            }
        }

        $prices = array_filter($prices);
        $pricesOriginal = array_filter($pricesOriginal);
        $data['price'] = $pricesOriginal ? min($pricesOriginal) : 0;
        $data['new_price'] = $prices ? min($prices) : 0;

        return $data;
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
