<?php
namespace Magento\NegotiableQuote\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Negotiable quote comment attachment resource model
 */
class CommentAttachment extends AbstractDb
{
    /**#@+
     * Negotiable quote comment attachment table
     */
    const NEGOTIABLE_QUOTE_COMMENT_ATTACHMENT_TABLE = 'negotiable_quote_comment_attachment';
    /**#@-*/

    /**
     * Define main table
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(self::NEGOTIABLE_QUOTE_COMMENT_ATTACHMENT_TABLE, 'attachment_id');
    }
}
