<?php

namespace Magento\NegotiableQuote\Block\Quote;

use Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface;
use Magento\Framework\View\Element\Template;
use Magento\Quote\Api\Data\CartInterface;

/**
 * Class provides quote information.
 *
 * @api
 * @since 100.0.0
 */
class Info extends Template
{
    /**
     * @var \Magento\NegotiableQuote\Model\Status\LabelProviderInterface
     */
    private $labelProvider;

    /**
     * @var \Magento\NegotiableQuote\Model\Expiration
     */
    private $expiration;

    /**
     * @var \Magento\Company\Api\AuthorizationInterface
     */
    private $authorization;

    /**
     * @var \Magento\NegotiableQuote\Model\Company\DetailsProviderFactory
     */
    private $companyDetailsProviderFactory;

    /**
     * @var \Magento\NegotiableQuote\Model\Customer\AddressProviderFactory
     */
    private $addressProviderFactory;

    /**
     * @var \Magento\NegotiableQuote\Model\Customer\AddressProvider
     */
    private $addressProvider;

    /**
     * @var \Magento\NegotiableQuote\Model\Company\DetailsProvider
     */
    private $companyDetailsProvider;

    /**
     * @var \Magento\NegotiableQuote\Helper\Quote
     */
    private $negotiableQuoteHelper;

    /**
     * @var \Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface
     */
    private $negotiableQuote;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\NegotiableQuote\Helper\Quote $negotiableQuoteHelper
     * @param \Magento\NegotiableQuote\Model\Status\LabelProviderInterface $labelProvider
     * @param \Magento\NegotiableQuote\Model\Expiration $expiration
     * @param \Magento\Company\Api\AuthorizationInterface $authorization
     * @param \Magento\NegotiableQuote\Model\Company\DetailsProviderFactory $companyDetailsProviderFactory
     * @param \Magento\NegotiableQuote\Model\Customer\AddressProviderFactory $addressProviderFactory
     * @param array $data [optional]
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\NegotiableQuote\Helper\Quote $negotiableQuoteHelper,
        \Magento\NegotiableQuote\Model\Status\LabelProviderInterface $labelProvider,
        \Magento\NegotiableQuote\Model\Expiration $expiration,
        \Magento\Company\Api\AuthorizationInterface $authorization,
        \Magento\NegotiableQuote\Model\Company\DetailsProviderFactory $companyDetailsProviderFactory,
        \Magento\NegotiableQuote\Model\Customer\AddressProviderFactory $addressProviderFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->negotiableQuoteHelper = $negotiableQuoteHelper;
        $this->labelProvider = $labelProvider;
        $this->expiration = $expiration;
        $this->authorization = $authorization;
        $this->companyDetailsProviderFactory = $companyDetailsProviderFactory;
        $this->addressProviderFactory = $addressProviderFactory;
    }

    /**
     * Retrieve full name of quote owner.
     *
     * @return string
     */
    public function getQuoteOwnerFullName()
    {
        return $this->getCompanyDetailsProvider()->getQuoteOwnerName();
    }

    /**
     * Retrieve sales rep.
     *
     * @return string
     */
    public function getSalesRep()
    {
        return $this->getCompanyDetailsProvider()->getSalesRepresentativeName();
    }

    /**
     * Get quote status label.
     *
     * @return string
     */
    public function getQuoteStatusLabel()
    {
        $status = '';

        $negotiableQuote = $this->getNegotiableQuote();
        if ($negotiableQuote && $negotiableQuote->getStatus()) {
            $status = $negotiableQuote->getStatus();
        }

        return $this->labelProvider->getLabelByStatus($status);
    }

    /**
     * Returns the cart creation date and time.
     *
     * @param int $format [optional]
     * @return null|string
     */
    public function getQuoteCreatedAt($format = \IntlDateFormatter::MEDIUM)
    {
        $createdAt = null;

        if ($this->getQuote()) {
            $createdAt = $this->formatDate(
                $this->getQuote()->getCreatedAt(),
                $format,
                true
            );
        }

        return $createdAt;
    }

    /**
     * Retrieve negotiable quote name.
     *
     * @return string|null
     */
    public function getQuoteName()
    {
        $quoteName = null;

        $negotiableQuote = $this->getNegotiableQuote();
        if ($negotiableQuote && $negotiableQuote->getQuoteName()) {
            $quoteName = $negotiableQuote->getQuoteName();
        }

        return $quoteName;
    }

    /**
     * Retrieve negotiable quote from $this->getQuote().
     *
     * @return \Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface|null
     */
    protected function getNegotiableQuote()
    {
        if (!$this->negotiableQuote) {
            $negotiableQuote = null;
            $quoteExtensionAttributes = null;

            if ($this->getQuote()) {
                $quoteExtensionAttributes = $this->getQuote()->getExtensionAttributes();
            }

            if ($quoteExtensionAttributes && $quoteExtensionAttributes->getNegotiableQuote()) {
                $negotiableQuote = $quoteExtensionAttributes->getNegotiableQuote();
            }

            $this->negotiableQuote = $negotiableQuote;
        }

        return $this->negotiableQuote;
    }

    /**
     * Get expiration period time.
     *
     * @return \DateTime|null
     */
    public function getExpirationPeriodTime()
    {
        return $this->expiration->getExpirationPeriodTime($this->getQuote(true));
    }

    /**
     * Check if quote has expiration date.
     *
     * @return bool
     */
    public function isQuoteExpirationDateDisplayed()
    {
        return $this->getExpirationPeriodTime() !== null && $this->getExpirationPeriodTime()->getTimestamp() > 0;
    }

    /**
     * Get date format by type.
     *
     * @param int $type
     * @return string
     */
    public function getDateFormat($type = \IntlDateFormatter::SHORT)
    {
        return $this->_localeDate->getDateFormat($type);
    }

    /**
     * Render an address as HTML and return the result.
     *
     * @return string
     */
    public function getAddressHtml()
    {
        $address = $this->getQuote(true)->getShippingAddress();
        return $this->getAddressProvider()->getRenderedAddress($address);
    }

    /**
     * Get all existing customer addresses.
     *
     * @return array
     */
    public function getAllAddresses()
    {
        return $this->getAddressProvider()->getAllCustomerAddresses();
    }

    /**
     * Determine whether this is the default address for this quote.
     *
     * @param int $addressId
     * @return bool
     */
    public function isDefaultAddress($addressId)
    {
        $defaultAddressId = $this->getQuote()->getCustomer()->getDefaultShipping();
        $isDefault = false;
        if (($defaultAddressId === $addressId)
            || ($this->getQuote()
                && $this->getQuote()->getShippingAddress()
                && ($this->getQuote()->getShippingAddress()->getCustomerAddressId() === $addressId))
        ) {
            $isDefault = true;
        }
        return $isDefault;
    }

    /**
     * Retrieve address html by $addressId.
     *
     * @param int $addressId
     * @return string
     */
    public function getLineAddressHtml($addressId)
    {
        return $this->getAddressProvider()->getRenderedLineAddress($addressId);
    }

    /**
     * Get Quote Shipping Address ID.
     *
     * @return int|null
     */
    public function getQuoteShippingAddressId()
    {
        return (
            $this->getQuote() !== null
            && $this->getQuote()->getShippingAddress() !== null
            && $this->getQuote()->getShippingAddress()->getId() !== null
        ) ? $this->getQuote()->getShippingAddress()->getId() : null;
    }

    /**
     * Retrieve the URL for adding a shipping address.
     *
     * @return string
     */
    public function getAddShippingAddressUrl()
    {
        return $this->getUrl(
            'customer/address/new',
            ['quoteId' => $this->getQuote()->getId()]
        );
    }

    /**
     * Retrieve the URL for updating a shipping address.
     *
     * @return string
     */
    public function getUpdateShippingAddressUrl()
    {
        return $this->getUrl(
            '*/*/updateAddress'
        );
    }

    /**
     * Return flag whether to display shipping method.
     *
     * @return bool
     */
    public function hideShippingMethod()
    {
        $negotiableQuote = $this->getNegotiableQuote();
        return !$this->getQuote(true)->getShippingAddress()->getShippingDescription()
            && ($negotiableQuote->getStatus() == NegotiableQuoteInterface::STATUS_ORDERED
                || $negotiableQuote->getStatus() == NegotiableQuoteInterface::STATUS_CLOSED);
    }

    /**
     * Check whether quote management is allowed for this customer.
     *
     * @return bool
     */
    public function isAllowedManage()
    {
        return $this->authorization->isAllowed('Magento_NegotiableQuote::manage');
    }

    /**
     * Get address provider.
     *
     * @return \Magento\NegotiableQuote\Model\Customer\AddressProvider
     */
    protected function getAddressProvider()
    {
        if (!$this->addressProvider) {
            $this->addressProvider = $this->addressProviderFactory->create(
                ['customer' => $this->getQuote()->getCustomer()]
            );
        }

        return $this->addressProvider;
    }

    /**
     * Get company details provider.
     *
     * @return \Magento\NegotiableQuote\Model\Company\DetailsProvider
     */
    protected function getCompanyDetailsProvider()
    {
        if (!$this->companyDetailsProvider) {
            $this->companyDetailsProvider = $this->companyDetailsProviderFactory->create(
                ['quote' => $this->getQuote()]
            );
        }

        return $this->companyDetailsProvider;
    }

    /**
     * Get label for currency rate if base and quote currencies are different.
     *
     * @return string
     */
    public function getCurrencyRateLabel()
    {
        $label = '';
        $quoteCurrency = $this->getQuote()->getCurrency();
        if ($quoteCurrency->getBaseCurrencyCode() != $quoteCurrency->getQuoteCurrencyCode()) {
            $label = $quoteCurrency->getBaseCurrencyCode() . ' / ' . $quoteCurrency->getQuoteCurrencyCode();
        }
        return $label;
    }

    /**
     * Get currency rate if base and quote currencies are different.
     *
     * @return string
     */
    public function getCurrencyRate()
    {
        $rate = 1;
        $quoteCurrency = $this->getQuote()->getCurrency();
        if ($quoteCurrency->getBaseCurrencyCode() != $quoteCurrency->getQuoteCurrencyCode()
            && $quoteCurrency->getBaseToQuoteRate()
        ) {
            $rate = $quoteCurrency->getBaseToQuoteRate();
        }
        return $rate;
    }

    /**
     * Retrieve current quote.
     *
     * @param bool $snapshot [optional]
     * @return CartInterface|null
     */
    public function getQuote($snapshot = false)
    {
        return $this->negotiableQuoteHelper->resolveCurrentQuote($snapshot);
    }

    /**
     * Check whether customer can edit the quote.
     *
     * @return bool
     */
    public function canEdit()
    {
        return $this->negotiableQuoteHelper->isSubmitAvailable();
    }
}
