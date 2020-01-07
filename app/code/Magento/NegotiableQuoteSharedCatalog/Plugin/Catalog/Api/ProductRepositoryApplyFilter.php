<?php

namespace Magento\NegotiableQuoteSharedCatalog\Plugin\Catalog\Api;

use Magento\Store\Model\ScopeInterface;
use Magento\NegotiableQuoteSharedCatalog\Model\SharedCatalog\ProductItem\Retrieve;

/**
 * Adds Shared Catalog filter to product repository.
 */
class ProductRepositoryApplyFilter
{
    /**
     * @var \Magento\SharedCatalog\Model\Config
     */
    private $config;

    /**
     * @var \Magento\SharedCatalog\Model\CustomerGroupManagement
     */
    private $customerGroupManagement;

    /**
     * @var \Magento\Company\Model\CompanyContext
     */
    protected $companyContext;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface;
     */
    private $quoteRepository;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\NegotiableQuoteSharedCatalog\Model\SharedCatalog\ProductItem\Retrieve
     */
    private $sharedCatalogProductItemRetriever;

    /**
     * @param \Magento\SharedCatalog\Model\Config $config
     * @param \Magento\SharedCatalog\Model\CustomerGroupManagement $customerGroupManagement
     * @param \Magento\Company\Model\CompanyContext $companyContext
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param Retrieve $sharedCatalogProductItemRetriever
     */
    public function __construct(
        \Magento\SharedCatalog\Model\Config $config,
        \Magento\SharedCatalog\Model\CustomerGroupManagement $customerGroupManagement,
        \Magento\Company\Model\CompanyContext $companyContext,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        Retrieve $sharedCatalogProductItemRetriever
    ) {
        $this->config = $config;
        $this->customerGroupManagement = $customerGroupManagement;
        $this->companyContext = $companyContext;
        $this->request = $request;
        $this->quoteRepository = $quoteRepository;
        $this->storeManager = $storeManager;
        $this->sharedCatalogProductItemRetriever = $sharedCatalogProductItemRetriever;
    }

    /**
     * Check if product is available for this customer group.
     *
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $subject
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @return \Magento\Catalog\Api\Data\ProductInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetById(
        \Magento\Catalog\Api\ProductRepositoryInterface $subject,
        \Magento\Catalog\Api\Data\ProductInterface $product
    ) {
        $this->applyFilter($product);
        return $product;
    }

    /**
     * Check if product is available for current customer group.
     *
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $subject
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @return \Magento\Catalog\Api\Data\ProductInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGet(
        \Magento\Catalog\Api\ProductRepositoryInterface $subject,
        \Magento\Catalog\Api\Data\ProductInterface $product
    ) {
        $this->applyFilter($product);
        return $product;
    }

    /**
     * Apply filter to search results.
     *
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @return void
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function applyFilter(\Magento\Catalog\Api\Data\ProductInterface $product)
    {
        $website = $this->storeManager->getWebsite()->getId();
        if (!$this->config->isActive(ScopeInterface::SCOPE_WEBSITE, $website)) {
            return;
        }
        $customerGroupId = $this->getCustomerGroupId();
        if ($customerGroupId === null || $this->customerGroupManagement->isMasterCatalogAvailable($customerGroupId)) {
            return;
        }
        $sku = $product->getData(\Magento\Catalog\Api\Data\ProductInterface::SKU);
        $items = $this->sharedCatalogProductItemRetriever->retrieve($customerGroupId, $sku);
        if (empty($items)) {
            throw new \Magento\Framework\Exception\NoSuchEntityException(
                __("The product that was requested doesn't exist. Verify the product and try again.")
            );
        }
    }

    /**
     * Get customer group id from Negotiable Quote page.
     *
     * @return mixed|null
     */
    private function getCustomerGroupId()
    {
        $customerGroupId = $this->companyContext->getCustomerGroupId();
        if ($this->request->getParam('negotiable_quote_update_flag')
            && $this->request->getParam('quote_id')) {
            /** @var \Magento\Quote\Api\Data\CartInterface $quote */
            $quote = $this->quoteRepository->get($this->request->getParam('quote_id'), ['*']);
            $customerGroupId = $quote->getCustomerGroupId();
        }

        return $customerGroupId;
    }
}
