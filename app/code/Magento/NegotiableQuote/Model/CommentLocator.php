<?php

namespace Magento\NegotiableQuote\Model;

use Magento\NegotiableQuote\Api\CommentLocatorInterface;
use Magento\NegotiableQuote\Model\ResourceModel\Comment\CollectionFactory;
use Magento\NegotiableQuote\Model\ResourceModel\Quote\CollectionFactory as NegotiableQuoteCollectionFactory;

/**
 * Class for load quote comments with attachment.
 */
class CommentLocator implements CommentLocatorInterface
{
    /**
     * @var \Magento\NegotiableQuote\Model\ResourceModel\Comment\CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var CommentManagementInterface
     */
    private $commentManagement;

    /**
     * @var NegotiableQuoteCollectionFactory
     */
    private $negotiableQuoteCollectionFactory;

    /**
     * @param CollectionFactory $collectionFactory
     * @param CommentManagementInterface $commentManagement
     * @param NegotiableQuoteCollectionFactory $negotiableQuoteCollectionFactory
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        CommentManagementInterface $commentManagement,
        NegotiableQuoteCollectionFactory $negotiableQuoteCollectionFactory
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->commentManagement = $commentManagement;
        $this->negotiableQuoteCollectionFactory = $negotiableQuoteCollectionFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getListForQuote($quoteId)
    {
        $negotiableQuoteCollection = $this->negotiableQuoteCollectionFactory->create();
        $negotiableQuoteCollection->addFieldToFilter('entity_id', $quoteId);
        if (!$negotiableQuoteCollection->getSize()) {
            throw new \Magento\Framework\Exception\NoSuchEntityException(
                __(
                    'Requested quote is not found. Row ID: %fieldName = %fieldValue',
                    ['fieldName' => 'quoteId', 'fieldValue' => $quoteId]
                )
            );
        }
        /** @var \Magento\NegotiableQuote\Model\ResourceModel\Comment\Collection $commentCollection */
        $commentCollection = $this->collectionFactory->create();
        $commentCollection->addFieldToFilter('parent_id', $quoteId);
        $items = $commentCollection->getItems();
        foreach ($items as $item) {
            $item->setAttachments(
                $this->commentManagement
                    ->getCommentAttachments($item->getEntityId())
                    ->getItems()
            );
        }
        return $items;
    }
}
