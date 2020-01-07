<?php

namespace Magento\NegotiableQuote\Model\ResourceModel;

/**
 * Negotiable Quote Company Config Model resource model
 */
class CompanyQuoteConfig extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**#@+*/
    const COMPANY_QUOTE_CONFIG_TABLE = 'negotiable_quote_company_config';
    /**#@-*/

    /**
     * {@inheritdoc}
     */
    protected $_useIsObjectNew = true;

    /**
     * {@inheritdoc}
     */
    protected $_isPkAutoIncrement = false;

    /**
     * Define main table
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(self::COMPANY_QUOTE_CONFIG_TABLE, 'company_entity_id');
    }
}
