<?php
namespace Magento\Rma\Block\Adminhtml\Order\View\Tab;

use Magento\Rma\Model\ResourceModel\Rma\Collection;
use Magento\Rma\Model\ResourceModel\Rma\Status\History\CollectionFactory as HistoryCollectionFactory;
use Magento\Rma\Model\Rma\Source\Status;
use Magento\Rma\Model\Rma\Status\History as StatusHistory;
use Magento\Sales\Block\Adminhtml\Order\View\Tab\History;

/**
 * Class HistoryPlugin
 * @package Magento\Rma\Block\Adminhtml\Order\View\Tab
 */
class HistoryPlugin
{
    /**
     * @var Collection
     */
    private $rmaCollection;

    /**
     * @var HistoryCollectionFactory
     */
    private $historyCollectionFactory;

    /**
     * @param Collection $rmaCollection
     * @param HistoryCollectionFactory $historyCollectionFactory
     */
    public function __construct(Collection $rmaCollection, HistoryCollectionFactory $historyCollectionFactory)
    {
        $this->rmaCollection = $rmaCollection;
        $this->historyCollectionFactory = $historyCollectionFactory;
    }

    /**
     * Add Returns to Order Comments history
     *
     * @param History $subject
     * @param array $history
     * @return array
     */
    public function afterGetFullHistory(History $subject, array $history)
    {
        $collection = $this->rmaCollection->addFieldToFilter('order_id', $subject->getOrder()->getId())->load();
        $creationSystemComment = StatusHistory::getSystemCommentByStatus(Status::STATE_PENDING);
        /** @var $historyCollection \Magento\Rma\Model\ResourceModel\Rma\Status\History\Collection */
        $historyCollection = $this->historyCollectionFactory->create();
        /** @var \Magento\Rma\Model\Rma $rma */
        foreach ($collection as $rma) {
            /** @var $comments \Magento\Rma\Model\Rma\Status\History[] */
            $comments = $historyCollection->getItemsByColumnValue('rma_entity_id', $rma->getId());
            foreach ($comments as $comment) {
                if ($comment->getComment() == $creationSystemComment) {
                    $history[] = [
                        'title' => sprintf('Return #%s created', $rma->getIncrementId()),
                        'notified' => $comment->getIsCustomerNotified(),
                        'comment' => '',
                        'created_at' => $comment->getCreatedAtDate(),
                    ];
                }
            }
        }
        usort($history, [get_class($subject), 'sortHistoryByTimestamp']);
        return $history;
    }
}
