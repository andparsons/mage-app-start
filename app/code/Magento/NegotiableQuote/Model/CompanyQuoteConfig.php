<?php

namespace Magento\NegotiableQuote\Model;

use Magento\Framework\Model\AbstractExtensibleModel;
use Magento\NegotiableQuote\Api\Data\CompanyQuoteConfigInterface;

/**
 * Negotiable Quote Company Config Model
 */
class CompanyQuoteConfig extends AbstractExtensibleModel implements CompanyQuoteConfigInterface
{
    /**
     * Initialize resource
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Magento\NegotiableQuote\Model\ResourceModel\CompanyQuoteConfig::class);
        parent::_construct();
    }

    /**
     * Get company id
     *
     * @return string|null
     */
    public function getCompanyId()
    {
        return $this->getData(self::COMPANY_ID);
    }

    /**
     * Set company id
     *
     * @param string $id
     * @return $this
     */
    public function setCompanyId($id)
    {
        return $this->setData(self::COMPANY_ID, $id);
    }

    /**
     * Is quote enabled for company
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getIsQuoteEnabled()
    {
        return (bool)$this->getData(self::IS_QUOTE_ENABLED);
    }

    /**
     * Set is quote enabled for company
     *
     * @param bool $isEnabled
     * @return $this
     */
    public function setIsQuoteEnabled($isEnabled)
    {
        $this->setData(self::IS_QUOTE_ENABLED, (bool)$isEnabled);
    }

    /**
     * Retrieve existing extension attributes object.
     *
     * @return \Magento\NegotiableQuote\Api\Data\CompanyQuoteConfigExtensionInterface|null
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\NegotiableQuote\Api\Data\CompanyQuoteConfigExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Magento\NegotiableQuote\Api\Data\CompanyQuoteConfigExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }

    /**
     * Processing object after load data
     *
     * @return $this
     */
    protected function _afterLoad()
    {
        parent::_afterLoad();
        if (!$this->getCompanyId()) {
            $this->isObjectNew(true);
        }
        return $this;
    }
}
