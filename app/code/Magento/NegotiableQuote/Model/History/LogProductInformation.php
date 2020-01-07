<?php

namespace Magento\NegotiableQuote\Model\History;

/**
 * Prepares product information for negotiable quote history log.
 */
class LogProductInformation
{
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var array
     */
    private $productOptionsProviders;

    /**
     * @var array
     */
    private $products = [];

    /**
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Psr\Log\LoggerInterface $logger
     * @param array $productOptionsProviders [optional]
     */
    public function __construct(
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Psr\Log\LoggerInterface $logger,
        array $productOptionsProviders = []
    ) {
        $this->productRepository = $productRepository;
        $this->logger = $logger;
        $this->productOptionsProviders = $productOptionsProviders;
    }

    /**
     * Get product name by sku.
     *
     * @param string $sku
     * @return string
     */
    public function getProductName($sku)
    {
        try {
            $product = $this->productRepository->get($sku);
            $productName = $product->getName();
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            $this->logger->critical($e);
            $productName = $sku . __(' - deleted');
        } catch (\Exception $e) {
            $this->logger->critical($e);
            $productName = $sku;
        }

        return $productName;
    }

    /**
     * Get product name by product id.
     *
     * @param int $id
     * @return string
     */
    public function getProductNameById($id)
    {
        try {
            $product = $this->productRepository->getById($id);
            $productName = $product->getName();
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            $this->logger->critical($e);
            $productName = __('Product with ID #%1 is deleted', $id);
        } catch (\Exception $e) {
            $this->logger->critical($e);
            $productName = $id;
        }

        return $productName;
    }

    /**
     * Retrieve product allowed attributes.
     *
     * @param int $productId
     * @return array
     */
    public function getProductAttributes($productId)
    {
        $attributesArray = [];

        if (!isset($this->products[$productId])) {
            try {
                $product = $this->productRepository->getById($productId);
                $this->products[$productId] = $product;
            } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                $this->logger->critical($e);
                return $attributesArray;
            } catch (\Exception $e) {
                $this->logger->critical($e);
                return $attributesArray;
            }
        }

        $product = $this->products[$productId];
        $productType = $product->getTypeId();

        /**
         * @var \Magento\NegotiableQuote\Model\ProductOptionsProviderInterface $optionsProvider
         */
        foreach ($this->productOptionsProviders as $optionsProvider) {
            if ($optionsProvider->getProductType() == $productType) {
                $attributesArray = $optionsProvider->getOptions($product);
            }
        }

        return $attributesArray;
    }
}
