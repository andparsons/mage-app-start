<?php

namespace Magento\NegotiableQuote\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Interface CompanyQuoteConfigInterface
 * @api
 * @since 100.0.0
 */
interface CompanyQuoteConfigInterface extends ExtensibleDataInterface
{
    /**#@+*/
    const COMPANY_ID = 'company_entity_id';
    const IS_QUOTE_ENABLED = 'is_quote_enabled';
    /**#@-*/

    /**
     * Get company id
     *
     * @return string|null
     */
    public function getCompanyId();

    /**
     * Set company id
     *
     * @param string $id
     * @return $this
     */
    public function setCompanyId($id);

    /**
     * Is quote enabled for company
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getIsQuoteEnabled();

    /**
     * Set is quote enabled for company
     * @param bool $isEnabled
     * @return $this
     */
    public function setIsQuoteEnabled($isEnabled);

    /**
     * Retrieve existing extension attributes object.
     *
     * @return \Magento\NegotiableQuote\Api\Data\CompanyQuoteConfigExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\NegotiableQuote\Api\Data\CompanyQuoteConfigExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Magento\NegotiableQuote\Api\Data\CompanyQuoteConfigExtensionInterface $extensionAttributes
    );
}
