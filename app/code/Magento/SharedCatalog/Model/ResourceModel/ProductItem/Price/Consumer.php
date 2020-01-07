<?php

namespace Magento\SharedCatalog\Model\ResourceModel\ProductItem\Price;

use Magento\AsynchronousOperations\Api\Data\OperationInterface;
use Magento\Framework\EntityManager\EntityManager;

/**
 * Consumer processes messages with tier prices updates, changes statuses of processed messages.
 */
class Consumer
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var \Magento\Framework\Serialize\SerializerInterface
     */
    private $serializer;

    /**
     * @var \Magento\Catalog\Api\TierPriceStorageInterface
     */
    private $tierPriceStorage;

    /**
     * @var \Magento\SharedCatalog\Model\ResourceModel\ProductItem\Price\PriceProcessor
     */
    private $priceProcessor;

    /**
     * @param \Psr\Log\LoggerInterface $logger
     * @param EntityManager $entityManager
     * @param \Magento\Framework\Serialize\SerializerInterface $serializer
     * @param \Magento\Catalog\Api\TierPriceStorageInterface $tierPriceStorage
     * @param \Magento\SharedCatalog\Model\ResourceModel\ProductItem\Price\PriceProcessor $priceProcessor
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        EntityManager $entityManager,
        \Magento\Framework\Serialize\SerializerInterface $serializer,
        \Magento\Catalog\Api\TierPriceStorageInterface $tierPriceStorage,
        \Magento\SharedCatalog\Model\ResourceModel\ProductItem\Price\PriceProcessor $priceProcessor
    ) {
        $this->logger = $logger;
        $this->entityManager = $entityManager;
        $this->serializer = $serializer;
        $this->tierPriceStorage = $tierPriceStorage;
        $this->priceProcessor = $priceProcessor;
    }

    /**
     * Processing batch of operations for update tier prices.
     *
     * @param \Magento\AsynchronousOperations\Api\Data\OperationListInterface $operationList
     * @return void
     * @throws \InvalidArgumentException
     */
    public function processOperations(\Magento\AsynchronousOperations\Api\Data\OperationListInterface $operationList)
    {
        $pricesUpdateDto = [];
        $pricesDeleteDto = [];
        $operationSkus = [];
        foreach ($operationList->getItems() as $index => $operation) {
            $serializedData = $operation->getSerializedData();
            $unserializedData = $this->serializer->unserialize($serializedData);
            $operationSkus[$index] = $unserializedData['product_sku'];
            $pricesUpdateDto = array_merge(
                $pricesUpdateDto,
                $this->priceProcessor->createPricesUpdate($unserializedData)
            );
            $pricesDeleteDto = array_merge(
                $pricesDeleteDto,
                $this->priceProcessor->createPricesDelete($unserializedData)
            );
        }

        $failedDeleteItems = [];
        $failedUpdateItems = [];
        $uncompletedOperations = [];
        try {
            $failedDeleteItems = $this->tierPriceStorage->delete($pricesDeleteDto);
            $failedUpdateItems = $this->tierPriceStorage->update($pricesUpdateDto);
        } catch (\Magento\Framework\Exception\CouldNotSaveException $e) {
            $uncompletedOperations['status'] = OperationInterface::STATUS_TYPE_RETRIABLY_FAILED;
            $uncompletedOperations['error_code'] = $e->getCode();
            $uncompletedOperations['message'] = $e->getMessage();
        } catch (\Magento\Framework\Exception\CouldNotDeleteException $e) {
            $uncompletedOperations['status'] = OperationInterface::STATUS_TYPE_RETRIABLY_FAILED;
            $uncompletedOperations['error_code'] = $e->getCode();
            $uncompletedOperations['message'] = $e->getMessage();
        } catch (\Exception $e) {
            $uncompletedOperations['status'] = OperationInterface::STATUS_TYPE_RETRIABLY_FAILED;
            $uncompletedOperations['error_code'] = $e->getCode();
            $uncompletedOperations['message'] =
                __('Sorry, something went wrong during product prices update. Please see log for details.');
        }

        $failedItems = array_merge($failedDeleteItems, $failedUpdateItems);
        $failedOperations = [];
        foreach ($failedItems as $failedItem) {
            if (isset($failedItem->getParameters()['SKU'])) {
                $failedOperations[$failedItem->getParameters()['SKU']] = $this->priceProcessor->prepareErrorMessage(
                    $failedItem
                );
            }
        }

        try {
            $this->changeOperationStatus($operationList, $failedOperations, $uncompletedOperations, $operationSkus);
        } catch (\Exception $exception) {
            // prevent consumer from failing, silently log exception
            $this->logger->critical($exception->getMessage());
        }
    }

    /**
     * Change operation status.
     *
     * Depends on message processing result: Completed, Not Retriably Failed, Retriably Failed
     *
     * @param \Magento\AsynchronousOperations\Api\Data\OperationListInterface $operationList
     * @param array $failedOperations
     * @param array $uncompletedOperations
     * @param array $operationSkus
     * @return void
     * @throws \LogicException
     * @throws \Exception
     */
    private function changeOperationStatus(
        \Magento\AsynchronousOperations\Api\Data\OperationListInterface $operationList,
        array $failedOperations,
        array $uncompletedOperations,
        array $operationSkus
    ) {
        foreach ($operationList->getItems() as $index => $operation) {
            if (isset($failedOperations[$operationSkus[$index]])) {
                $operation->setStatus(OperationInterface::STATUS_TYPE_NOT_RETRIABLY_FAILED);
                $operation->setResultMessage($failedOperations[$operationSkus[$index]]);
            } elseif (!empty($uncompletedOperations)) {
                $operation->setStatus($uncompletedOperations['status']);
                $operation->setErrorCode($uncompletedOperations['error_code']);
                $operation->setResultMessage($uncompletedOperations['message']);
            } else {
                $operation->setStatus(OperationInterface::STATUS_TYPE_COMPLETE);
                $operation->setResultMessage(null);
            }
        }
        $this->entityManager->save($operationList);
    }
}
