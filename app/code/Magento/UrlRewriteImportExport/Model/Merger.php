<?php
declare(strict_types=1);

namespace Magento\UrlRewriteImportExport\Model;

use Magento\AsynchronousOperations\Api\Data\OperationListInterfaceFactory;
use Magento\Framework\MessageQueue\MergedMessageInterfaceFactory;
use Magento\AsynchronousOperations\Api\Data\OperationListInterface;
use Magento\Framework\MessageQueue\MergedMessageInterface;

/**
 * Merges messages from the operations queue
 */
class Merger implements \Magento\Framework\MessageQueue\MergerInterface
{
    /**
     * The factory class for @see OperationListInterface
     *
     * @var OperationListInterfaceFactory
     */
    private $operationListFactory;

    /**
     * The factory class for @see MergedMessageInterface
     *
     * @var MergedMessageInterfaceFactory
     */
    private $mergedMessageFactory;

    /**
     * @param OperationListInterfaceFactory $operationListFactory The factory class for @see OperationListInterface
     * @param MergedMessageInterfaceFactory $mergedMessageFactory The factory class for @see MergedMessageInterface
     */
    public function __construct(
        OperationListInterfaceFactory $operationListFactory,
        MergedMessageInterfaceFactory $mergedMessageFactory
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
