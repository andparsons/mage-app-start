<?php

namespace Magento\NegotiableQuote\Model;

use Magento\NegotiableQuote\Api\Data\CommentInterface;
use Magento\NegotiableQuote\Api\Data\CommentInterfaceFactory;
use Magento\NegotiableQuote\Model\ResourceModel\Comment as CommentResource;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Psr\Log\LoggerInterface;
use Magento\NegotiableQuote\Model\Comment\SearchProvider;

/**
 * Class CommentRepository
 */
class CommentRepository implements CommentRepositoryInterface
{
    /**
     * @var \Magento\NegotiableQuote\Api\Data\CommentInterface[]
     */
    private $instances = [];

    /**
     * Negotiable quote comment resource
     *
     * @var \Magento\NegotiableQuote\Model\ResourceModel\Comment
     */
    private $commentResource;

    /**
     * @var \Magento\NegotiableQuote\Api\Data\CommentInterfaceFactory`
     */
    private $commentFactory;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \Magento\NegotiableQuote\Model\Comment\SearchProvider
     */
    private $searchProvider;

    /**
     * @param CommentResource         $commentResource
     * @param CommentInterfaceFactory $commentFactory
     * @param LoggerInterface         $logger
     * @param SearchProvider  $searchProvider
     */
    public function __construct(
        CommentResource $commentResource,
        CommentInterfaceFactory $commentFactory,
        LoggerInterface $logger,
        SearchProvider $searchProvider
    ) {
        $this->commentResource = $commentResource;
        $this->commentFactory = $commentFactory;
        $this->logger = $logger;
        $this->searchProvider = $searchProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function save(CommentInterface $comment)
    {
        if (!$comment->getEntityId()) {
            return false;
        }
        try {
            $this->commentResource->saveCommentData($comment);
        } catch (\Exception $exception) {
            $this->logger->critical($exception);
            throw new CouldNotSaveException(__('There was an error saving comment.'));
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function get($id)
    {
        if (!isset($this->instances[$id])) {
            /** @var CommentInterface $comment */
            $comment = $this->commentFactory->create();
            $comment->load($id);
            if (!$comment->getId()) {
                throw NoSuchEntityException::singleField('id', $id);
            }
            $this->instances[$id] = $comment;
        }
        return $this->instances[$id];
    }

    /**
     * {@inheritdoc}
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        return $this->searchProvider->getList($searchCriteria);
    }

    /**
     * {@inheritdoc}
     */
    public function delete(CommentInterface $comment)
    {
        try {
            $this->commentResource->delete($comment);
        } catch (\Exception $exception) {
            $this->logger->critical($exception);
            throw new StateException(
                __('Cannot delete comment with id %1', $comment->getEntityId()),
                $exception
            );
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($id)
    {
        return $this->delete($this->get($id));
    }
}
