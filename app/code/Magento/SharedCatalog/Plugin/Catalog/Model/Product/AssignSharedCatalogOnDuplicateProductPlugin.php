<?php

namespace Magento\SharedCatalog\Plugin\Catalog\Model\Product;

/**
 * Assign products to Shared Catalog on product duplicate action.
 */
class AssignSharedCatalogOnDuplicateProductPlugin
{
    /**
     * @var \Magento\SharedCatalog\Model\ProductSharedCatalogsLoader
     */
    private $productSharedCatalogsLoader;

    /**
     * @var \Magento\SharedCatalog\Api\ProductItemManagementInterface
     */
    private $sharedCatalogProductItemManagement;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @param \Magento\SharedCatalog\Model\ProductSharedCatalogsLoader $productSharedCatalogsLoader
     * @param \Magento\SharedCatalog\Api\ProductItemManagementInterface $productItemManagement
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Magento\SharedCatalog\Model\ProductSharedCatalogsLoader $productSharedCatalogsLoader,
        \Magento\SharedCatalog\Api\ProductItemManagementInterface $productItemManagement,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->productSharedCatalogsLoader = $productSharedCatalogsLoader;
        $this->sharedCatalogProductItemManagement = $productItemManagement;
        $this->logger = $logger;
    }

    /**
     * Product after copy plugin.
     *
     * @param \Magento\Catalog\Model\Product\Copier $subject
     * @param \Magento\Catalog\Model\Product $result
     * @param \Magento\Catalog\Model\Product $product
     * @return \Magento\Catalog\Model\Product
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterCopy(
        \Magento\Catalog\Model\Product\Copier $subject,
        \Magento\Catalog\Model\Product $result,
        \Magento\Catalog\Model\Product $product
    ) {
        $origProductSharedCatalogs = $this->productSharedCatalogsLoader->getAssignedSharedCatalogs($product->getSku());
        if (count($origProductSharedCatalogs)) {
            foreach ($origProductSharedCatalogs as $origProductSharedCatalog) {
                try {
                    $this->sharedCatalogProductItemManagement->addItems(
                        $origProductSharedCatalog->getCustomerGroupId(),
                        [$result->getSku()]
                    );
                } catch (\Magento\Framework\Exception\LocalizedException $e) {
                    $this->logger->critical($e);
                }
            }
        }

        return $result;
    }
}
