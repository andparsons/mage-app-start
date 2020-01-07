<?php
namespace Magento\NegotiableQuote\Model\ResourceModel\Quote;

/**
 * Quotes collection
 */
class Collection extends \Magento\Quote\Model\ResourceModel\Quote\Collection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'entity_id';
}
