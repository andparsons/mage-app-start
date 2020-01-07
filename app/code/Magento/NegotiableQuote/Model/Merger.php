<?php
declare(strict_types=1);

namespace Magento\NegotiableQuote\Model;

use Magento\AsynchronousOperations\Api\Data\OperationListInterfaceFactory as OperationListFactory;
use Magento\Framework\MessageQueue\MergedMessageInterfaceFactory as MergedMessageFactory;

/**
 * Merges messages from the operations queue.
 */
class Merger implements \Magento\Framework\MessageQueue\MergerInterface
{
    /**
     * @var OperationListFactory
     */
    private $operationListFactory;

    /**
     * @var MergedMessageFactory
     */
    private $mergedMessageFactory;

    /**
     * @param OperationListFactory $operationListFactory
     * @param MergedMessageFactory $mergedMessageFactory
     */
    public function __construct(
        OperationListFactory $operationListFactory,
        MergedMessageFactory $mergedMessageFactory
    ) {
        $this->operationListFactory = $operationListFactory;
        $this->mergedMessageFactory = $mergedMessageFactory;
    }

    /**
     * @inheritdoc
     */
    public function merge(array $messages): array
    {
        $result = [];

        foreach ($messages as $topicName => $topicMessages) {
            $operationList = $this->operationListFactory->create(['items' => $topicMessages]);
            $messagesIds = array_keys($topicMessages);
            $result[$topicName][] = $this->mergedMessageFactory->create(
                [
                    'mergedMessage' => $operationList,
                    'originalMessagesIds' => $messagesIds
                ]
            );
        }

        return $result;
    }
}
