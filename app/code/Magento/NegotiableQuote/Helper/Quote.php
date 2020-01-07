<?php

namespace Magento\NegotiableQuote\Helper;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\NegotiableQuote\Model\Quote\ViewAccessInterface;
use Magento\NegotiableQuote\Model\Restriction\RestrictionInterface;
use Magento\Company\Api\CompanyManagementInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\NegotiableQuote\Api\NegotiableQuoteManagementInterface;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Framework\App\Helper\Context;

/**
 * Helper for quote block output (we need it because we strongly rely on original quote blocks).
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Quote extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var StockRegistryInterface
     */
    private $stockRepository;

    /**
     * @var RestrictionInterface
     */
    private $restriction;

    /**
     * @var CompanyManagementInterface
     */
    private $companyManagement;

    /**
     * @var CartInterface
     */
    private $quote;

    /**
     * @var CartInterface
     */
    private $snapshotQuote;

    /**
     * @var CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var NegotiableQuoteManagementInterface
     */
    private $negotiableQuoteManagement;

    /**
     * @var \Magento\Company\Api\AuthorizationInterface
     */
    private $authorization;

    /**
     * @var \Magento\Authorization\Model\UserContextInterface
     */
    private $userContext;

    /**
     * @var \Magento\NegotiableQuote\Model\PriceFormatter
     */
    private $priceFormatter;

    /**
     * @var ViewAccessInterface
     */
    private $viewAccess;

    /**
     * @param Context $context
     * @param StockRegistryInterface $stockRepository
     * @param RestrictionInterface $restriction
     * @param CompanyManagementInterface $companyManagement
     * @param CartRepositoryInterface $quoteRepository
     * @param NegotiableQuoteManagementInterface $negotiableQuoteManagement
     * @param \Magento\Company\Api\AuthorizationInterface $authorization
     * @param \Magento\Authorization\Model\UserContextInterface $userContext
     * @param \Magento\NegotiableQuote\Model\PriceFormatter $priceFormatter
     * @param ViewAccessInterface $viewAccess
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        StockRegistryInterface $stockRepository,
        RestrictionInterface $restriction,
        CompanyManagementInterface $companyManagement,
        CartRepositoryInterface $quoteRepository,
        NegotiableQuoteManagementInterface $negotiableQuoteManagement,
        \Magento\Company\Api\AuthorizationInterface $authorization,
        \Magento\Authorization\Model\UserContextInterface $userContext,
        \Magento\NegotiableQuote\Model\PriceFormatter $priceFormatter,
        ViewAccessInterface $viewAccess
    ) {
        parent::__construct($context);
        $this->stockRepository = $stockRepository;
        $this->restriction = $restriction;
        $this->companyManagement = $companyManagement;
        $this->quoteRepository = $quoteRepository;
        $this->negotiableQuoteManagement = $negotiableQuoteManagement;
        $this->authorization = $authorization;
        $this->userContext = $userContext;
        $this->priceFormatter = $priceFormatter;
        $this->viewAccess = $viewAccess;
    }

    /**
     * Fetch snapshot of current quote.
     *
     * @return CartInterface|null
     */
    private function resolveSnapshotQuote()
    {
        $this->restriction->setQuote($this->quote);

        if (!$this->restriction->canSubmit()) {
            if (!$this->snapshotQuote
                && $this->quote
                && $this->quote->getExtensionAttributes()->getNegotiableQuote()
            ) {
                $this->snapshotQuote = $this
                    ->negotiableQuoteManagement
                    ->getSnapshotQuote($this->quote->getId());
            }
        }

        return $this->snapshotQuote;
    }

    /**
     * Resolves current quote.
     *
     * @param bool $snapshot [optional]
     * @return CartInterface|null
     */
    public function resolveCurrentQuote($snapshot = false)
    {
        if (!$this->quote) {
            $quoteId = $this->_request->getParam('quote_id');

            if (!$quoteId) {
                return null;
            }

            try {
                $quote = $this->quoteRepository->get($quoteId, ['*']);
            } catch (NoSuchEntityException $e) {
                return null;
            }

            //Checking access.
            try {
                $customerHasAccess = $this->viewAccess->canViewQuote($quote);
            } catch (LocalizedException $exception) {
                //Something went wrong, ignoring.
                $customerHasAccess = true;
            }
            if (!$customerHasAccess) {
                return null;
            }

            $this->quote = $quote;
        }

        if ($snapshot && $snapshotQuote = $this->resolveSnapshotQuote()) {
            return $snapshotQuote;
        }

        return $this->quote;
    }

    /**
     * Is quoting enabled for customer.
     *
     * @return bool
     */
    public function isEnabled()
    {
        $customerId = $this->userContext->getUserId();
        try {
            $company = $this->companyManagement->getByCustomerId($customerId);
            if ($company
                && $company->getExtensionAttributes()
                && $company->getExtensionAttributes()->getQuoteConfig()
            ) {
                return $company->getExtensionAttributes()->getQuoteConfig()->getIsQuoteEnabled();
            }
        } catch (NoSuchEntityException $e) {
            //do nothing, just return false
        }
        return false;
    }

    /**
     * Get current user id.
     *
     * @return int|null
     */
    public function getCurrentUserId()
    {
        return $this->userContext->getUserId();
    }

    /**
     * Retrieve sales representative.
     *
     * @param int $quoteId
     * @param bool $returnId [optional]
     * @return string|bool
     */
    public function getSalesRepresentative($quoteId, $returnId = false)
    {
        $quote = $this->quoteRepository->get($quoteId, ['*']);
        $customerId = null;
        $salesRepresentative = '';
        $quoteCustomer = ($quote && $quote->getCustomer()) ? $quote->getCustomer() : null;

        if ($quoteCustomer && $quoteCustomer->getId()) {
            $customerId = $quoteCustomer->getId();
        }

        if ($customerId) {
            try {
                $company = $this->companyManagement->getByCustomerId($customerId);

                if ($company) {
                    $salesRepresentative = $company->getSalesRepresentativeId();

                    if (!$returnId) {
                        $salesRepresentative = $this->companyManagement->getSalesRepresentative($salesRepresentative);
                    }
                }
            } catch (NoSuchEntityException $e) {
                return false;
            }
        }

        return $salesRepresentative;
    }

    /**
     * Get formatted price value including currency.
     *
     * @param float $price
     * @param string $code [optional]
     * @return string
     */
    public function formatPrice($price, $code = null)
    {
        return $this->priceFormatter->formatPrice($price, $code);
    }

    /**
     * Is lock message displayed.
     *
     * @return bool
     */
    public function isLockMessageDisplayed()
    {
        return $this->restriction->isLockMessageDisplayed();
    }

    /**
     * Is expired message displayed.
     *
     * @return bool
     */
    public function isExpiredMessageDisplayed()
    {
        return $this->restriction->isExpiredMessageDisplayed();
    }

    /**
     * Format original price.
     *
     * @param CartItemInterface $item
     * @param string $quoteCurrency [optional]
     * @param string $baseCurrency [optional]
     * @return float
     */
    public function getFormattedOriginalPrice(CartItemInterface $item, $quoteCurrency = null, $baseCurrency = null)
    {
        return $this->priceFormatter->getFormattedOriginalPrice($item, $quoteCurrency, $baseCurrency);
    }

    /**
     * Format cart price.
     *
     * @param CartItemInterface $item
     * @param string $quoteCurrency [optional]
     * @param string $baseCurrency [optional]
     * @return float
     */
    public function getFormattedCartPrice(CartItemInterface $item, $quoteCurrency = null, $baseCurrency = null)
    {
        return $this->priceFormatter->getFormattedCartPrice($item, $quoteCurrency, $baseCurrency);
    }

    /**
     * Retrieve item options.
     *
     * @param CartItemInterface $item
     * @param bool $isString [optional]
     * @return array|string
     */
    public function retrieveCustomOptions(CartItemInterface $item, $isString = true)
    {
        $options = [];
        $optionsNames = [
            'super_attribute',
            'options',
            'bundle_option',
            'custom_giftcard_amount',
            'giftcard_amount',
            'giftcard_message',
            'giftcard_recipient_email',
            'giftcard_recipient_name',
            'giftcard_sender_email',
            'giftcard_sender_name'
        ];
        $request = $item->getBuyRequest();
        foreach ($optionsNames as $option) {
            if ($request->hasData($option) && $request->getData($option)) {
                $options[$option] = $request->getData($option);
            }
        }
        if ($isString) {
            $options = $options ? http_build_query($options) : '';
        }
        return $options;
    }

    /**
     * Format catalog price.
     *
     * @param CartItemInterface $item
     * @param string $quoteCurrency [optional]
     * @param string $baseCurrency [optional]
     * @return float
     */
    public function getFormattedCatalogPrice(CartItemInterface $item, $quoteCurrency = null, $baseCurrency = null)
    {
        return $this->priceFormatter->getFormattedCatalogPrice($item, $quoteCurrency, $baseCurrency);
    }

    /**
     * Is submit available.
     *
     * @return bool
     */
    public function isSubmitAvailable()
    {
        return $this->restriction->canSubmit();
    }

    /**
     * Returns stock level for quote item.
     *
     * @param CartItemInterface $item
     * @return float
     */
    public function getStockForProduct(CartItemInterface $item)
    {
        if ($item->getProductType() == 'configurable') {
            foreach ($item->getQuote()->getAllItems() as $itemQuote) {
                if ($itemQuote->getParentItemId() == $item->getId()) {
                    $item = $itemQuote;
                    break;
                }
            }
        }
        $productId = $item->getProduct()->getId();

        return $this->stockRepository->getStockItem($productId)->getQty();
    }

    /**
     * Checks if quote management is allowed.
     *
     * @return bool
     */
    public function isAllowedManage()
    {
        return $this->authorization->isAllowed('Magento_NegotiableQuote::manage');
    }

    /**
     * Get item total.
     *
     * @param CartItemInterface $item
     * @param string $quoteCurrency [optional]
     * @param string $baseCurrency [optional]
     * @return float
     */
    public function getItemTotal(CartItemInterface $item, $quoteCurrency = null, $baseCurrency = null)
    {
        return $this->priceFormatter->getItemTotal($item, $quoteCurrency, $baseCurrency);
    }
}
