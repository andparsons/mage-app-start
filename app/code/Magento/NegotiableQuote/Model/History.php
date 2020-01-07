<?php

namespace Magento\NegotiableQuote\Model;

use Magento\NegotiableQuote\Api\Data\HistoryInterface;
use Magento\Framework\Model\AbstractExtensibleModel;

/**
 * Negotiable quote history log model
 */
class History extends AbstractExtensibleModel implements HistoryInterface
{
    /**
     * Initialize resource
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Magento\NegotiableQuote\Model\ResourceModel\History::class);
        parent::_construct();
    }

    /**
     * {@inheritdoc}
     */
    public function getHistoryId()
    {
        return $this->getData(HistoryInterface::HISTORY_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setHistoryId($id)
    {
        return $this->setData(HistoryInterface::HISTORY_ID, $id);
    }

    /**
     * {@inheritdoc}
     */
    public function getQuoteId()
    {
        return $this->getData(HistoryInterface::QUOTE_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setQuoteId($id)
    {
        return $this->setData(HistoryInterface::QUOTE_ID, $id);
    }

    /**
     * {@inheritdoc}
     */
    public function getIsSeller()
    {
        return (bool)$this->getData(HistoryInterface::IS_SELLER);
    }

    /**
     * {@inheritdoc}
     */
    public function setIsSeller($flag)
    {
        return $this->setData(HistoryInterface::IS_SELLER, $flag);
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthorId()
    {
        return $this->getData(HistoryInterface::AUTHOR_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setAuthorId($authorId)
    {
        return $this->setData(HistoryInterface::AUTHOR_ID, $authorId);
    }

    /**
     * {@inheritdoc}
     */
    public function getIsDraft()
    {
        return $this->getData(HistoryInterface::IS_DRAFT);
    }

    /**
     * {@inheritdoc}
     */
    public function setIsDraft($flag)
    {
        return $this->setData(HistoryInterface::IS_DRAFT, $flag);
    }

    /**
     * {@inheritdoc}
     */
    public function getStatus()
    {
        return $this->getData(HistoryInterface::STATUS);
    }

    /**
     * {@inheritdoc}
     */
    public function setStatus($logStatus)
    {
        return $this->setData(HistoryInterface::STATUS, $logStatus);
    }

    /**
     * {@inheritdoc}
     */
    public function getLogData()
    {
        return $this->getData(HistoryInterface::LOG_DATA);
    }

    /**
     * {@inheritdoc}
     */
    public function setLogData($logData)
    {
        return $this->setData(HistoryInterface::LOG_DATA, $logData);
    }

    /**
     * {@inheritdoc}
     */
    public function getSnapshotData()
    {
        return $this->getData(HistoryInterface::SNAPSHOT_DATA);
    }

    /**
     * {@inheritdoc}
     */
    public function setSnapshotData($snapshotData)
    {
        return $this->setData(HistoryInterface::SNAPSHOT_DATA, $snapshotData);
    }

    /**
     * {@inheritdoc}
     */
    public function getCreatedAt()
    {
        return $this->getData(HistoryInterface::CREATED_AT);
    }

    /**
     * {@inheritdoc}
     */
    public function setCreatedAt($timestamp)
    {
        return $this->setData(HistoryInterface::CREATED_AT, $timestamp);
    }

    /**
     * {@inheritdoc}
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * {@inheritdoc}
     */
    public function setExtensionAttributes(
        \Magento\NegotiableQuote\Api\Data\HistoryExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
