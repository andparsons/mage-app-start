<?php

namespace Magento\CatalogRuleStaging\Pricing\Price;

use Magento\Catalog\Model\Product;
use Magento\CatalogRule\Model\ResourceModel\Rule as RuleResourceModel;
use Magento\CatalogRule\Model\ResourceModel\Rule\Collection as RuleCollection;
use Magento\CatalogRule\Model\Rule as RuleModel;
use Magento\Customer\Model\Session;
use Magento\Framework\Pricing\Adjustment\Calculator;
use Magento\Framework\Pricing\Price\BasePriceProviderInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\CatalogRule\Model\ResourceModel\Rule\CollectionFactory;
use Magento\Staging\Model\VersionManager;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class for calculation catalog rule price when staging is used
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class CatalogRulePrice extends \Magento\CatalogRule\Pricing\Price\CatalogRulePrice implements
    BasePriceProviderInterface
{
    /**
     * @var VersionManager
     */
    protected $versionManager;

    /**
     * @var CollectionFactory
     */
    protected $ruleCollectionFactory;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @param Product $saleableItem
     * @param float $quantity
     * @param Calculator $calculator
     * @param PriceCurrencyInterface $priceCurrency
     * @param TimezoneInterface $dateTime
     * @param StoreManagerInterface $storeManager
     * @param Session $customerSession
     * @param RuleResourceModel $ruleResource
     * @param CollectionFactory $ruleCollectionFactory
     * @param VersionManager $versionManager
     * @param ProductRepositoryInterface $productRepository
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Product $saleableItem,
        $quantity,
        Calculator $calculator,
        PriceCurrencyInterface $priceCurrency,
        TimezoneInterface $dateTime,
        StoreManagerInterface $storeManager,
        Session $customerSession,
        RuleResourceModel $ruleResource,
        CollectionFactory $ruleCollectionFactory,
        VersionManager $versionManager,
        ProductRepositoryInterface $productRepository
    ) {
        parent::__construct(
            $saleableItem,
            $quantity,
            $calculator,
            $priceCurrency,
            $dateTime,
            $storeManager,
            $customerSession,
            $ruleResource
        );
        $this->ruleCollectionFactory = $ruleCollectionFactory;
        $this->versionManager = $versionManager;
        $this->productRepository = $productRepository;
    }

    /**
     * Returns catalog rule value
     *
     * @return float|boolean
     */
    public function getValue()
    {
        if (!$this->versionManager->isPreviewVersion()) {
            return parent::getValue();
        }
        if (null === $this->value) {
            $this->value = $this->product->getPrice();
            $activeRules = $this->getActiveRules(
                $this->storeManager->getStore()->getWebsiteId(),
                $this->customerSession->getCustomerGroupId()
            );
            /** @var  RuleModel $rule */
            foreach ($activeRules as $rule) {
                if ($this->product->getParentId()) {
                    $this->product = $this->productRepository->getById($this->product->getParentId());
                }

                if ($rule->validate($this->product)) {
                    $this->value = $this->calculateRuleProductPrice($rule, $this->value);
                    if ($rule->getStopRulesProcessing()) {
                        break;
                    }
                }
            }
        }
        return $this->value;
    }

    /**
     * Get active rules
     *
     * @param int $websiteId
     * @param int $customerGroupId
     * @return RuleCollection
     */
    protected function getActiveRules($websiteId, $customerGroupId)
    {
        /** @var RuleCollection $ruleCollection */
        return $this->ruleCollectionFactory->create()
            ->addWebsiteFilter($websiteId)
            ->addCustomerGroupFilter($customerGroupId)
            ->addFieldToFilter('is_active', 1)
            ->setOrder('sort_order', \Magento\Framework\Data\Collection::SORT_ORDER_ASC);
    }

    /**
     * Calculate rule product price
     *
     * @param RuleModel $rule
     * @param float $currentProductPrice
     * @return float
     */
    protected function calculateRuleProductPrice(RuleModel $rule, $currentProductPrice)
    {
        switch ($rule->getSimpleAction()) {
            case 'to_fixed':
                $currentProductPrice = min($rule->getDiscountAmount(), $currentProductPrice);
                break;
            case 'to_percent':
                $currentProductPrice = $currentProductPrice * $rule->getDiscountAmount() / 100;
                break;
            case 'by_fixed':
                $currentProductPrice = max(0, $currentProductPrice - $rule->getDiscountAmount());
                break;
            case 'by_percent':
                $currentProductPrice = $currentProductPrice * (1 - $rule->getDiscountAmount() / 100);
                break;
            default:
                $currentProductPrice = 0;
        }

        return $this->priceCurrency->round($currentProductPrice);
    }
}
