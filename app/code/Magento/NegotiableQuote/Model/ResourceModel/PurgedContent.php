<?php

namespace Magento\NegotiableQuote\Model\ResourceModel;

/**
 * Class PurgedContent.
 */
class PurgedContent extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**#@+*/
    const QUOTE_PURGED_CONTENT_TABLE = 'negotiable_quote_purged_content';
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
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init(self::QUOTE_PURGED_CONTENT_TABLE, 'quote_id');
    }
}
