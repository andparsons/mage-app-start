<?php
namespace Magento\SharedCatalog\Model;

use Magento\SharedCatalog\Api\CategoryManagementInterface;
use Magento\SharedCatalog\Api\ProductManagementInterface;
use Magento\SharedCatalog\Api\SharedCatalogDuplicationInterface;
use Magento\SharedCatalog\Api\SharedCatalogRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\SharedCatalog\Model\ResourceModel\ProductItem\Price\ScheduleBulk;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\App\ObjectManager;

/**
 * Duplicating categories and products in a shared catalog.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Duplicator
{
    /**
     * @var \Magento\SharedCatalog\Api\CategoryManagementInterface
     */
    private $categoryManagement;

    /**
     * @var \Magento\SharedCatalog\Api\ProductManagementInterface
     */
    private $productManagement;

    /**
     * @var \Magento\SharedCatalog\Api\SharedCatalogDuplicationInterface
     */
    private $sharedCatalogDuplication;

    /**
     * @var \Magento\SharedCatalog\Model\CatalogPermissionManagement
     */
    private $catalogPermissionManagement;

    /**
     * @var \Magento\SharedCatalog\Api\SharedCatalogRepositoryInterface
     */
    private $sharedCatalogRepository;

    /**
     * @var \Magento\SharedCatalog\Model\ResourceModel\ProductItem\Price\ScheduleBulk
     */
    private $scheduleBulk;

    /**
     * @var UserContextInterface
     */
    private $userContext;

    /**
     * @var \Magento\SharedCatalog\Model\Price\DuplicatorTierPriceLoader
     */
    private $tierPriceLoader;

    /**
     * @param CategoryManagementInterface $categoryManagement
     * @param ProductManagementInterface $productManagement
     * @param \Magento\SharedCatalog\Model\CatalogPermissionManagement $catalogPermissionManagement
     * @param ProductRepositoryInterface $productRepository
     * @param SharedCatalogRepositoryInterface $sharedCatalogRepository
     * @param ScheduleBulk $scheduleBulk
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param UserContextInterface $userContextInterface
     * @param \Magento\SharedCatalog\Model\Price\DuplicatorTierPriceLoader $tierPricesLoader
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param \Magento\SharedCatalog\Api\SharedCatalogDuplicationInterface $sharedCatalogDuplication
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        CategoryManagementInterface $categoryManagement,
        ProductManagementInterface $productManagement,
        \Magento\SharedCatalog\Model\CatalogPermissionManagement $catalogPermissionManagement,
        ProductRepositoryInterface $productRepository,
        SharedCatalogRepositoryInterface $sharedCatalogRepository,
        ScheduleBulk $scheduleBulk,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        UserContextInterface $userContextInterface,
        \Magento\SharedCatalog\Model\Price\DuplicatorTierPriceLoader $tierPricesLoader,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        SharedCatalogDuplicationInterface $sharedCatalogDuplication = null
    ) {
        $this->categoryManagement = $categoryManagement;
        $this->productManagement = $productManagement;
        $this->catalogPermissionManagement = $catalogPermissionManagement;
        $this->sharedCatalogRepository = $sharedCatalogRepository;
        $this->scheduleBulk = $scheduleBulk;
        $this->userContext = $userContextInterface;
        $this->tierPriceLoader = $tierPricesLoader;
        $this->sharedCatalogDuplication = $sharedCatalogDuplication
            ?: ObjectManager::getInstance()->get(SharedCatalogDuplicationInterface::class);
    }

    /**
     * Duplicate categories, products and store from shared catalog $idOriginal to shared catalog $idDuplicated.
     *
     * @param int $idOriginal
     * @param int $idDuplicated
     * @return void
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function duplicateCatalog($idOriginal, $idDuplicated)
    {
        $oldCatalog = $this->sharedCatalogRepository->get($idOriginal);
        $newCatalog = $this->sharedCatalogRepository->get($idDuplicated);
        $newCatalog->setStoreId($oldCatalog->getStoreId());
        $this->sharedCatalogRepository->save($newCatalog);

        $categoryIds = $this->categoryManagement->getCategories($idOriginal);
        $this->catalogPermissionManagement->setAllowPermissions(
            $categoryIds,
            [$newCatalog->getCustomerGroupId()]
        );
        $productSkus = $this->productManagement->getProducts($idOriginal);
        $tierPrices = $this->tierPriceLoader->load($productSkus, $oldCatalog->getCustomerGroupId());
        $this->sharedCatalogDuplication->assignProductsToDuplicate($idDuplicated, $productSkus);
        if ($tierPrices) {
            $this->scheduleBulk->execute($newCatalog, $tierPrices, $this->userContext->getUserId());
        }
    }
}
