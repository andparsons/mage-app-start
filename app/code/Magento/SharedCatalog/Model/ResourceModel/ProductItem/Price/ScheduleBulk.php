<?php

namespace Magento\SharedCatalog\Model\ResourceModel\ProductItem\Price;

use Magento\AsynchronousOperations\Api\Data\OperationInterface;
use Magento\Customer\Api\Data\GroupInterface;
use Magento\SharedCatalog\Api\Data\SharedCatalogInterface;

/**
 * Schedule bulk update of tier prices.
 */
class ScheduleBulk
{
    /**
     * @var \Magento\Framework\Bulk\BulkManagementInterface
     */
    private $bulkManagement;

    /**
     * @var \Magento\AsynchronousOperations\Api\Data\OperationInterfaceFactory
     */
    private $operationFactory;

    /**
     * @var \Magento\Framework\DataObject\IdentityGeneratorInterface
     */
    private $identityService;

    /**
     * @var \Magento\Framework\Serialize\SerializerInterface
     */
    private $serializer;

    /**
     * @var \Magento\Customer\Api\GroupRepositoryInterface
     */
    private $groupRepository;

    /**
     * @param \Magento\Framework\Bulk\BulkManagementInterface $bulkManagement
     * @param \Magento\AsynchronousOperations\Api\Data\OperationInterfaceFactory $operartionFactory
     * @param \Magento\Framework\DataObject\IdentityGeneratorInterface $identityService
     * @param \Magento\Framework\Serialize\SerializerInterface $serializer
     * @param \Magento\Customer\Api\GroupRepositoryInterface $groupRepository
     */
    public function __construct(
        \Magento\Framework\Bulk\BulkManagementInterface $bulkManagement,
        \Magento\AsynchronousOperations\Api\Data\OperationInterfaceFactory $operartionFactory,
        \Magento\Framework\DataObject\IdentityGeneratorInterface $identityService,
        \Magento\Framework\Serialize\SerializerInterface $serializer,
        \Magento\Customer\Api\GroupRepositoryInterface $groupRepository
    ) {
        $this->bulkManagement = $bulkManagement;
        $this->operationFactory = $operartionFactory;
        $this->identityService = $identityService;
        $this->serializer = $serializer;
        $this->groupRepository = $groupRepository;
    }

    /**
     * Schedule new bulk.
     *
     * @param SharedCatalogInterface $sharedCatalog
     * @param array $prices
     * @param int $userId ID of the admin user that performed update of tier prices
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return void
     */
    public function execute($sharedCatalog, array $prices, $userId)
    {
        // exception handlers are omitted for simplicity
        $prices = $this->filterUnchangedPrices($prices);
        if (!$prices) {
            return;
        }

        $bulkUuid = $this->identityService->generateId();
        $bulkDescription = __('Assign custom prices to selected products');
        $operations = [];
        foreach ($this->retrieveCustomerGroupIds($sharedCatalog) as $customerGroupId) {
            $group = $this->groupRepository->getById($customerGroupId);
            foreach ($prices as $productSku => $productPrices) {
                $dataToEncode = [
                    'shared_catalog_id' => $sharedCatalog->getId(),
                    'customer_group' => $group->getCode(),
                    'product_sku' => $productSku,
                    'meta_information' => 'SKU:' . $productSku,
                    'prices' => $productPrices
                ];
                $data = [
                    'data' => [
                        'bulk_uuid' => $bulkUuid,
                        'topic_name' => 'shared.catalog.product.price.updated',
                        'serialized_data' => $this->serializer->serialize($dataToEncode),
                        'status' => OperationInterface::STATUS_TYPE_OPEN,
                    ]
                ];

                $operation = $this->operationFactory->create($data);
                $operations[] = $operation;
            }
        }

        $result = $this->bulkManagement->scheduleBulk($bulkUuid, $operations, $bulkDescription, $userId);
        if (!$result) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Something went wrong while processing the request.')
            );
        }
    }

    /**
     * Filter prices that have not been changed and should not be queued.
     *
     * @param array $prices
     * @return array
     */
    public function filterUnchangedPrices(array $prices)
    {
        return array_filter(
            $prices,
            function ($productPrices) {
                foreach ($productPrices as $productPrice) {
                    if (!empty($productPrice['is_changed'])) {
                        return true;
                    }
                }
                return false;
            }
        );
    }

    /**
     * Retrieve customer group ids associated with shared catalog.
     *
     * @param SharedCatalogInterface $sharedCatalog
     * @return int[]
     */
    private function retrieveCustomerGroupIds(SharedCatalogInterface $sharedCatalog): array
    {
        $customerGroupIds = [(int) $sharedCatalog->getCustomerGroupId()];
        if ($sharedCatalog->getType() == SharedCatalogInterface::TYPE_PUBLIC) {
            $customerGroupIds[] = GroupInterface::NOT_LOGGED_IN_ID;
        }

        return $customerGroupIds;
    }
}
