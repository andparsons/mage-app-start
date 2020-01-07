<?php
declare(strict_types=1);

namespace Magento\NegotiableQuote\Model\ResourceModel\NegotiableQuotePrice;

use Magento\AsynchronousOperations\Api\Data\OperationListInterface as OperationList;
use Magento\AsynchronousOperations\Api\Data\OperationInterface as Operation;
use Magento\Framework\EntityManager\EntityManager;
use Magento\NegotiableQuote\Model\ResourceModel\NegotiableQuotePrice\ScheduleBulk as NegotiableQuoteBulk;
use Magento\Framework\Serialize\SerializerInterface as Serializer;
use Magento\NegotiableQuote\Api\NegotiableQuoteItemManagementInterface as NegotiableQuoteItemManagement;

/**
 * Consumer for negotiable quotes. It recalculates price tax of negotiable quote items.
 */
class Consumer
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @var NegotiableQuoteItemManagement
     */
    private $quoteItemManagement;

    /**
     * @param EntityManager $entityManager
     * @param Serializer $serializer
     * @param NegotiableQuoteItemManagement $quoteItemManagement
     */
    public function __construct(
        EntityManager $entityManager,
        Serializer $serializer,
        NegotiableQuoteItemManagement $quoteItemManagement
    ) {
        $this->entityManager = $entityManager;
        $this->serializer = $serializer;
        $this->quoteItemManagement = $quoteItemManagement;
    }

    /**
     * Process bulk operations.
     *
     * @param OperationList $operationList
     * @throws \Exception
     */
    public function processOperations(OperationList $operationList): void
    {
        foreach ($operationList->getItems() as $operation) {
            $this->processOperation($operation);
            $operation->setStatus(Operation::STATUS_TYPE_COMPLETE);
            $operation->setResultMessage(null);
        }

        $this->entityManager->save($operationList);
    }

    /**
     * Process bulk operation for recalculate price taxes of quote items.
     *
     * @param Operation $operation
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function processOperation(Operation $operation): void
    {
        $serializedData = $operation->getSerializedData();
        $unserializedData = $this->serializer->unserialize($serializedData);

        $quoteId = $unserializedData[NegotiableQuoteBulk::OPERATION_DATA_QUOTE_ID];
        $this->quoteItemManagement->recalculateOriginalPriceTax($quoteId, false, false, false);
    }
}
