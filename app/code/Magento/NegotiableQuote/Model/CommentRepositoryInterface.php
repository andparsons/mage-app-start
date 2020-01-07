<?php

namespace Magento\NegotiableQuote\Model;

use Magento\NegotiableQuote\Api\Data\CommentInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Interface CommentRepositoryInterface
 * @api
 * @since 100.0.0
 */
interface CommentRepositoryInterface
{
    /**
     * Set the comment for a negotiable quote.
     *
     * @param CommentInterface $comment comment.
     * @return bool
     * @throws CouldNotSaveException
     */
    public function save(CommentInterface $comment);

    /**
     * Get comment by ID
     *
     * @param int $id
     * @return CommentInterface
     * @throws NoSuchEntityException
     */
    public function get($id);

    /**
     * Get list of comments
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return SearchResultsInterface
     * @throws \InvalidArgumentException
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * Delete comment.
     *
     * @param CommentInterface $comment
     * @return bool
     * @throws StateException
     */
    public function delete(CommentInterface $comment);

    /**
     * Delete comment by ID
     *
     * @param int $id
     * @return bool
     * @throws \Magento\Framework\Exception\StateException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function deleteById($id);
}
