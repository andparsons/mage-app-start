<?php

namespace Magento\NegotiableQuote\Model;

/**
 * Class PurgedContent.
 */
class PurgedContent extends \Magento\Framework\Model\AbstractModel
{
    /**#@+
     * Constants
     */
    const QUOTE_ID = 'quote_id';
    const PURGED_DATA = 'purged_data';
    /**#@-*/

    /**
     * {@inheritdoc}
     */
    protected $_idFieldName = 'quote_id';

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init(\Magento\NegotiableQuote\Model\ResourceModel\PurgedContent::class);
        parent::_construct();
    }

    /**
     * Get negotiable quote ID.
     *
     * @return int
     */
    public function getQuoteId()
    {
        return $this->getData(self::QUOTE_ID);
    }

    /**
     * Set negotiable quote ID.
     *
     * @param int $quoteId
     * @return $this
     */
    public function setQuoteId($quoteId)
    {
        return $this->setData(self::QUOTE_ID, $quoteId);
    }

    /**
     * Get data of deleted user.
     *
     * @return string
     */
    public function getPurgedData()
    {
        return $this->getData(self::PURGED_DATA);
    }

    /**
     * Set data of deleted user.
     *
     * @param string $purgedData
     * @return $this
     */
    public function setPurgedData($purgedData)
    {
        return $this->setData(self::PURGED_DATA, $purgedData);
    }

    /**
     * {@inheritdoc}
     */
    protected function _afterLoad()
    {
        parent::_afterLoad();
        if (!$this->getQuoteId()) {
            $this->isObjectNew(true);
        }
        return $this;
    }
}
