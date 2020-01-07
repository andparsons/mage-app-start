<?php

namespace Magento\NegotiableQuote\Model\History;

use Magento\NegotiableQuote\Model\ResourceModel\History\Collection as HistoryCollection;

/**
 * Prepares negotiable quote history log information.
 */
class LogInformation
{
    /**
     * @var \Magento\NegotiableQuote\Model\Restriction\RestrictionInterface
     */
    private $restriction;

    /**
     * @var \Magento\NegotiableQuote\Helper\Quote
     */
    private $negotiableQuoteHelper;

    /**
     * @var \Magento\NegotiableQuote\Model\HistoryManagementInterface
     */
    private $historyManagement;

    /**
     * @var \Magento\Customer\Model\Address\Config
     */
    private $addressConfig;

    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    private $localeResolver;

    /**
     * @param \Magento\NegotiableQuote\Model\Restriction\RestrictionInterface $restriction
     * @param \Magento\NegotiableQuote\Helper\Quote $negotiableQuoteHelper
     * @param \Magento\NegotiableQuote\Model\HistoryManagementInterface $historyManagement
     * @param \Magento\Customer\Model\Address\Config $addressConfig
     * @param \Magento\Framework\Locale\ResolverInterface $localeResolver
     */
    public function __construct(
        \Magento\NegotiableQuote\Model\Restriction\RestrictionInterface $restriction,
        \Magento\NegotiableQuote\Helper\Quote $negotiableQuoteHelper,
        \Magento\NegotiableQuote\Model\HistoryManagementInterface $historyManagement,
        \Magento\Customer\Model\Address\Config $addressConfig,
        \Magento\Framework\Locale\ResolverInterface $localeResolver
    ) {
        $this->restriction = $restriction;
        $this->negotiableQuoteHelper = $negotiableQuoteHelper;
        $this->historyManagement = $historyManagement;
        $this->addressConfig = $addressConfig;
        $this->localeResolver = $localeResolver;
    }

    /**
     * Is quote can be submitted.
     *
     * @return bool
     */
    public function isCanSubmit()
    {
        return $this->restriction->canSubmit();
    }

    /**
     * Get history log for negotiable quote.
     *
     * @return HistoryCollection
     */
    public function getQuoteHistory()
    {
        $history = null;

        if ($this->getQuote() && $this->getQuote()->getEntityId()) {
            $quoteId = $this->getQuote()->getEntityId();
            $this->historyManagement->updateSystemLogsStatus($quoteId);
            $history = $this->historyManagement->getQuoteHistory($quoteId);
        }

        return $history;
    }

    /**
     * Get data object with quote updates.
     *
     * @param int $logId
     * @return \Magento\Framework\DataObject
     */
    public function getQuoteUpdates($logId)
    {
        $updatesArray = $this->historyManagement->getLogUpdatesList($logId);
        $updates = new \Magento\Framework\DataObject();

        if (isset($updatesArray['negotiated_price'])) {
            unset($updatesArray['negotiated_price']);
        }

        $updates->setData($updatesArray);

        return $updates;
    }

    /**
     * Retrieve current quote.
     *
     * @return \Magento\Quote\Api\Data\CartInterface|null
     */
    private function getQuote()
    {
        return $this->negotiableQuoteHelper->resolveCurrentQuote();
    }

    /**
     * Is postcode set for the address.
     *
     * @param array $flatAddressArray
     * @return bool
     */
    public function isSetPostcode(array $flatAddressArray)
    {
        return !empty($flatAddressArray[\Magento\Quote\Api\Data\AddressInterface::KEY_POSTCODE]);
    }

    /**
     * Get renderer for log address.
     *
     * @return \Magento\Customer\Block\Address\Renderer\RendererInterface
     */
    public function getLogAddressRenderer()
    {
        return $this->addressConfig->getFormatByCode('html')->getRenderer();
    }

    /**
     * Prepare formatted date.
     *
     * @param string $date
     * @param int $dateType
     * @return string
     */
    public function formatDate($date, $dateType)
    {
        $dateObject = new \DateTime($date);
        $formatter = new \IntlDateFormatter(
            $this->localeResolver->getLocale(),
            $dateType,
            \IntlDateFormatter::NONE,
            null,
            null,
            null
        );

        return $formatter->format($dateObject);
    }
}
