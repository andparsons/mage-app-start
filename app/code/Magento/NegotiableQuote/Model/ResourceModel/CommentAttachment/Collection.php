<?php

namespace Magento\NegotiableQuote\Model\ResourceModel\CommentAttachment;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Class Collection
 */
class Collection extends AbstractCollection
{
    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \Magento\NegotiableQuote\Model\CommentAttachment::class,
            \Magento\NegotiableQuote\Model\ResourceModel\CommentAttachment::class
        );
    }
}
