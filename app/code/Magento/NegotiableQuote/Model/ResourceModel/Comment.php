<?php

namespace Magento\NegotiableQuote\Model\ResourceModel;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\NegotiableQuote\Api\Data\CommentInterface;

/**
 * Negotiable Quote Comment resource model
 */
class Comment extends AbstractDb
{
    /**#@+
     * Negotiable quote comment table
     */
    const NEGOTIABLE_QUOTE_COMMENT_TABLE = 'negotiable_quote_comment';
    /**#@-*/

    /**
     * Define main table
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(self::NEGOTIABLE_QUOTE_COMMENT_TABLE, 'entity_id');
    }

    /**
     * Assign comment data
     *
     * @param CommentInterface $comment
     * @return $this
     * @throws CouldNotSaveException
     */
    public function saveCommentData(
        CommentInterface $comment
    ) {
        $commentData = $comment->getData();

        if ($commentData) {
            try {
                $this->getConnection()->insertOnDuplicate(
                    $this->getTable(self::NEGOTIABLE_QUOTE_COMMENT_TABLE),
                    $commentData,
                    array_keys($commentData)
                );
            } catch (\Exception $e) {
                throw new CouldNotSaveException(__('There is an error while saving comment.'));
            }
        }

        return $this;
    }
}
