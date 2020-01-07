<?php
declare(strict_types=1);

namespace Magento\NegotiableQuote\Model;

use Magento\AsynchronousOperations\Api\Data\OperationInterface as BulkOperation;
use Magento\Framework\Serialize\SerializerInterface as Serializer;
use Magento\AsynchronousOperations\Api\Data\OperationInterfaceFactory as OperationFactory;

/**
 * Bulk operation builder.
 */
class OperationBuilder
{
    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @var OperationFactory
     */
    private $operationFactory;

    /**
     * @param Serializer $serializer
     * @param OperationFactory $operationFactory
     */
    public function __construct(Serializer $serializer, OperationFactory $operationFactory)
    {
        $this->serializer = $serializer;
        $this->operationFactory = $operationFactory;
    }

    /**
     * Build bulk operation.
     *
     * @param string $bulkId
     * @param string $queueTopic
     * @param string|int|float|bool|array|null $operationData
     * @return BulkOperation
     */
    public function build(string $bulkId, string $queueTopic, $operationData): BulkOperation
    {
        $serializedData = $this->serializer->serialize($operationData);
        $data = [
            'data' => [
                BulkOperation::BULK_ID => $bulkId,
                BulkOperation::TOPIC_NAME => $queueTopic,
                BulkOperation::SERIALIZED_DATA => $serializedData,
                BulkOperation::STATUS => BulkOperation::STATUS_TYPE_OPEN,
            ]
        ];

        return $this->operationFactory->create($data);
    }
}
