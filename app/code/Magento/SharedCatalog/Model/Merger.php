<?php

namespace Magento\SharedCatalog\Model;

/**
 * Merges messages from the operations queue.
 */
class Merger implements \Magento\Framework\MessageQueue\MergerInterface
{
    /**
     * @var \Magento\AsynchronousOperations\Api\Data\OperationListInterfaceFactory
     */
    private $operationListFactory;

    /**
     * @var \Magento\Framework\MessageQueue\MergedMessageInterfaceFactory
     */
    private $mergedMessageFactory;

    /**
     * @param \Magento\AsynchronousOperations\Api\Data\OperationListInterfaceFactory $operationListFactory
     * @param \Magento\Framework\MessageQueue\MergedMessageInterfaceFactory $mergedMessageFactory
     */
    public function __construct(
        \Magento\AsynchronousOperations\Api\Data\OperationListInterfaceFactory $operationListFactory,
        \Magento\Framework\MessageQueue\MergedMessageInterfaceFactory $mergedMessageFactory
    ) {
        $this->operationListFactory = $operationListFactory;
        $this->mergedMessageFactory = $mergedMessageFactory;
    }

    /**
     * @inheritdoc
     */
    public function merge(array $messages)
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
