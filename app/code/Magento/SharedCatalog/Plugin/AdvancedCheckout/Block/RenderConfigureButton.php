<?php
namespace Magento\SharedCatalog\Plugin\AdvancedCheckout\Block;

use Magento\Store\Model\ScopeInterface;

/**
 * Prepares HTML code for the "Configure" button.
 */
class RenderConfigureButton
{
    /**
     * @var \Magento\Framework\Serialize\SerializerInterface
     */
    private $serializer;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\SharedCatalog\Model\Config
     */
    private $config;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var \Magento\SharedCatalog\Model\SharedCatalogProductsLoader
     */
    private $productLoader;

    /**
     * @var string
     */
    private $quoteIdKey = 'quote_id';

    /**
     * @param \Magento\Framework\Serialize\SerializerInterface $serializer
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\SharedCatalog\Model\Config $config
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param \Magento\SharedCatalog\Model\SharedCatalogProductsLoader $productsLoader
     */
    public function __construct(
        \Magento\Framework\Serialize\SerializerInterface $serializer,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\SharedCatalog\Model\Config $config,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\SharedCatalog\Model\SharedCatalogProductsLoader $productsLoader
    ) {
        $this->serializer = $serializer;
        $this->storeManager = $storeManager;
        $this->config = $config;
        $this->quoteRepository = $quoteRepository;
        $this->productLoader = $productsLoader;
    }

    /**
     * Retrieves HTML code of "Configure" button.
     *
     * This function also checks whether the product was added to the shared catalog
     *
     * @param \Magento\AdvancedCheckout\Block\Adminhtml\Sku\Errors\Grid\Description $subject
     * @param \Closure $method
     * @return string
     * @throws \InvalidArgumentException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundGetConfigureButtonHtml(
        \Magento\AdvancedCheckout\Block\Adminhtml\Sku\Errors\Grid\Description $subject,
        \Closure $method
    ) {
        $quoteId = $subject->getRequest()->getParam($this->quoteIdKey);
        $canConfigure = $subject->getProduct()->canConfigure()
            && !$subject->getItem()->getIsConfigureDisabled()
            && $this->isProductAssignedToSharedCatalog($subject->getItem()->getSku(), $quoteId);
        $productId = $subject->escapeHtml($this->serializer->serialize($subject->getProduct()->getId()));
        $itemSku = $subject->escapeHtml($this->serializer->serialize($subject->getItem()->getSku()));

        /* @var $button \Magento\Backend\Block\Widget\Button */
        $button = $subject->getLayout()->createBlock(
            \Magento\Backend\Block\Widget\Button::class,
            '',
            [
                'data' => [
                    'class' => $canConfigure ? 'action-configure' : 'action-configure action-disabled',
                    'onclick' => $canConfigure ? "addBySku.configure({$productId}, {$itemSku})" : '',
                    'disabled' => !$canConfigure,
                    'label' => __('Configure'),
                    'type' => 'button',
                ]
            ]
        );

        return $button->toHtml();
    }

    /**
     * Checks whether the product is in the shared catalog, if the SharedCatalog feature is enabled.
     *
     * @param string $sku
     * @param int $quoteId
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function isProductAssignedToSharedCatalog($sku, $quoteId)
    {
        $website = $this->storeManager->getWebsite()->getId();

        if ($this->config->isActive(ScopeInterface::SCOPE_WEBSITE, $website)) {
            $quote = $this->quoteRepository->get($quoteId);
            $customerGroupId = $quote->getCustomerGroupId();
            $productSkus = $this->productLoader->getAssignedProductsSkus($customerGroupId);
            return in_array($sku, $productSkus);
        }

        return true;
    }
}
