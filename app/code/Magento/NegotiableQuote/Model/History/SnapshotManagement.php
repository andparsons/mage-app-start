<?php

namespace Magento\NegotiableQuote\Model\History;

use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\CartRepositoryInterface;

/**
 * Class SnapshotManagement
 */
class SnapshotManagement
{
    /**
     * @var CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var DiffProcessor
     */
    private $diffProcessor;

    /**
     * @var SnapshotInformationManagement
     */
    private $snapshotInformationManagement;

    /**
     * SnapshotManagement constructor
     * @param CartRepositoryInterface $quoteRepository
     * @param DiffProcessor $diffProcessor
     * @param SnapshotInformationManagement $snapshotInformationManagement
     */
    public function __construct(
        CartRepositoryInterface $quoteRepository,
        DiffProcessor $diffProcessor,
        SnapshotInformationManagement $snapshotInformationManagement
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->diffProcessor = $diffProcessor;
        $this->snapshotInformationManagement = $snapshotInformationManagement;
    }

    /**
     * Collect snapshot data of the new quote
     *
     * @param int $quoteId
     * @return array
     */
    public function collectSnapshotDataForNewQuote($quoteId)
    {
        $data = [];
        $quote = $this->getQuote($quoteId);
        if ($quote !== null && $quote->getExtensionAttributes() !== null
            && $quote->getExtensionAttributes()->getNegotiableQuote() !== null) {
            $data['cart'] = $this->snapshotInformationManagement->collectCartData($quote);
            $data['comments'] = $this->snapshotInformationManagement->collectCommentData($quote->getId());
            $data['status'] = $quote->getExtensionAttributes()->getNegotiableQuote()->getStatus();
        }
        return $data;
    }

    /**
     * Collect quote snapshot data
     *
     * @param int $quoteId
     * @return array
     */
    public function collectSnapshotData($quoteId)
    {
        /** @var CartInterface $quote */
        $quote = $this->getQuote($quoteId);
        if ($quote !== null && $quote->getExtensionAttributes() !== null
            && $quote->getExtensionAttributes()->getNegotiableQuote() !== null) {
            $snapshotData = $this->collectSnapshotDataForNewQuote($quote->getId());
            return $this->snapshotInformationManagement->prepareSnapshotData($quote, $snapshotData);
        }
        return [];
    }

    /**
     * Checking for system log
     *
     * @param array $data
     * @return array
     */
    public function checkForSystemLogs(array $data)
    {
        if (isset($data['check_system']) && $data['check_system'] == true) {
            unset($data['check_system']);
            return $data;
        }

        return $this->snapshotInformationManagement->prepareSystemLogData($data);
    }

    /**
     * Get customer id
     *
     * @param CartInterface $quote
     * @param bool $isSeller
     * @param bool $isExpired
     * @return int
     */
    public function getCustomerId(CartInterface $quote, $isSeller, $isExpired)
    {
        $customerId = 0;
        if (!$isExpired) {
            if (!$isSeller) {
                $customerId = $quote->getCustomer()->getId();
            } else {
                $customerId = $this->snapshotInformationManagement->getCustomerId($quote);
            }
        }
        return $customerId;
    }

    /**
     * Get quote
     *
     * @param int $quoteId
     * @return CartInterface|null
     */
    public function getQuote($quoteId)
    {
        if ($quoteId) {
            try {
                return $this->quoteRepository->get($quoteId, ['*']);
            } catch (\Exception $e) {
                return null;
            }
        }
        return null;
    }

    /**
     * Get quote with removed item
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return CartInterface|null
     */
    public function getQuoteForRemovedItem($searchCriteria)
    {
        $quote = $this->quoteRepository->getList($searchCriteria)->getItems();
        return !empty($quote) ? array_shift($quote) : null;
    }

    /**
     * Get quote comment
     *
     * @param int $quoteId
     * @param array $snapshotData
     * @return array
     */
    public function prepareCommentData($quoteId, array $snapshotData)
    {
        $comments = $this->snapshotInformationManagement->collectCommentData($quoteId);
        $data['comment'] = array_pop($comments);
        if (isset($snapshotData['status'])) {
            $data['status']['new_value'] = $snapshotData['status'];
        }

        return $data;
    }

    /**
     * Get difference between snapshots
     *
     * @param array $oldSnapshot
     * @param array $snapshotData
     * @return array
     */
    public function getSnapshotsDiff(array $oldSnapshot, array $snapshotData)
    {
        return $this->diffProcessor->processDiff($oldSnapshot, $snapshotData);
    }
}
