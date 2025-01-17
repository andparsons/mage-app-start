<?php
namespace Magento\Sales\Model\ResourceModel\Order\Invoice\Comment;

use Magento\Sales\Api\Data\InvoiceCommentSearchResultInterface;
use Magento\Sales\Model\ResourceModel\Order\Comment\Collection\AbstractCollection;

/**
 * Flat sales order invoice comment collection
 *
 * @api
 * @author      Magento Core Team <core@magentocommerce.com>
 * @since 100.0.2
 */
class Collection extends AbstractCollection implements InvoiceCommentSearchResultInterface
{
    /**
     * Event prefix
     *
     * @var string
     */
    protected $_eventPrefix = 'sales_order_invoice_comment_collection';

    /**
     * Event object
     *
     * @var string
     */
    protected $_eventObject = 'order_invoice_comment_collection';

    /**
     * Model initialization
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init(
            \Magento\Sales\Model\Order\Invoice\Comment::class,
            \Magento\Sales\Model\ResourceModel\Order\Invoice\Comment::class
        );
    }

    /**
     * Set invoice filter
     *
     * @param int $invoiceId
     * @return $this
     */
    public function setInvoiceFilter($invoiceId)
    {
        return $this->setParentFilter($invoiceId);
    }
}
