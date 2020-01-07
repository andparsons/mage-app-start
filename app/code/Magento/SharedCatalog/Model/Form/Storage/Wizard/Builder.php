<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SharedCatalog\Model\Form\Storage\Wizard;

use Magento\SharedCatalog\Api\Data\SharedCatalogInterface;
use Magento\SharedCatalog\Model\Form\Storage\Wizard as WizardStorage;
use Magento\SharedCatalog\Model\SharedCatalogProductsLoader;

/**
 * Initialize storage with products and categories assigned to shared catalog.
 */
class Builder
{
    /**
     * @var SharedCatalogProductsLoader
     */
    private $sharedCatalogProductsLoader;

    /**
     * @var \Magento\SharedCatalog\Model\Price\ProductTierPriceLoader
     */
    private $productTierPriceLoader;

    /**
     * @var \Magento\SharedCatalog\Model\ProductItemTierPriceValidator
     */
    private $productItemTierPriceValidator;

    /**
     * @var \Magento\SharedCatalog\Api\CategoryManagementInterface
     */
    private $sharedCatalogCategoryManagement;

    /**
     * @param SharedCatalogProductsLoader $sharedCatalogProductsLoader
     * @param \Magento\SharedCatalog\Model\Price\ProductTierPriceLoader $productTierPriceLoader
     * @param \Magento\SharedCatalog\Model\ProductItemTierPriceValidator $productItemTierPriceValidator
     * @param \Magento\SharedCatalog\Api\CategoryManagementInterface $sharedCatalogCategoryManagement
     */
    public function __construct(
        SharedCatalogProductsLoader $sharedCatalogProductsLoader,
        \Magento\SharedCatalog\Model\Price\ProductTierPriceLoader $productTierPriceLoader,
        \Magento\SharedCatalog\Model\ProductItemTierPriceValidator $productItemTierPriceValidator,
        \Magento\SharedCatalog\Api\CategoryManagementInterface $sharedCatalogCategoryManagement
    ) {
        $this->sharedCatalogProductsLoader = $sharedCatalogProductsLoader;
        $this->productTierPriceLoader = $productTierPriceLoader;
        $this->productItemTierPriceValidator = $productItemTierPriceValidator;
        $this->sharedCatalogCategoryManagement = $sharedCatalogCategoryManagement;
    }

    /**
     * Build storage wizard.
     *
     * @param WizardStorage $wizardStorage
     * @param SharedCatalogInterface $sharedCatalog
     * @return WizardStorage
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function build(WizardStorage $wizardStorage, SharedCatalogInterface $sharedCatalog)
    {
        $productSkus = $this->sharedCatalogProductsLoader
            ->getAssignedProductsSkus($sharedCatalog->getCustomerGroupId());
        if (!empty($productSkus)) {
            $this->productTierPriceLoader->populateTierPrices($productSkus, $sharedCatalog->getId(), $wizardStorage);
        }
        $wizardStorage->assignProducts($productSkus);
        $categoryIds = $this->sharedCatalogCategoryManagement->getCategories($sharedCatalog->getId());
        $wizardStorage->assignCategories($categoryIds);
        return $wizardStorage;
    }
}
