<?php

namespace Magento\SharedCatalog\Model\ResourceModel\Permission\CategoryPermissions;

use Magento\AsynchronousOperations\Api\Data\OperationInterface;
use Magento\AsynchronousOperations\Api\Data\OperationInterfaceFactory;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Framework\Bulk\BulkManagementInterface;
use Magento\Framework\DataObject\IdentityGeneratorInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\SerializerInterface;

/**
 * Schedule bulk update of Categories Permissions.
 */
class ScheduleBulk
{
    /**
     * @var BulkManagementInterface
     */
    private $bulkManagement;

    /**
     * @var OperationInterfaceFactory
     */
    private $operationFactory;

    /**
     * @var IdentityGeneratorInterface
     */
    private $identityService;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var GroupRepositoryInterface
     */
    private $groupRepository;

    /**
     * @var UserContextInterface
     */
    private $userContext;

    /**
     * @var string
     */
    private $queueTopic = 'shared.catalog.category.permissions.updated';

    /**
     * @param BulkManagementInterface $bulkManagement
     * @param OperationInterfaceFactory $operartionFactory
     * @param IdentityGeneratorInterface $identityService
     * @param SerializerInterface $serializer
     * @param GroupRepositoryInterface $groupRepository
     * @param UserContextInterface $userContext
     */
    public function __construct(
        BulkManagementInterface $bulkManagement,
        OperationInterfaceFactory $operartionFactory,
        IdentityGeneratorInterface $identityService,
        SerializerInterface $serializer,
        GroupRepositoryInterface $groupRepository,
        UserContextInterface $userContext
    ) {
        $this->bulkManagement = $bulkManagement;
        $this->operationFactory = $operartionFactory;
        $this->identityService = $identityService;
        $this->serializer = $serializer;
        $this->groupRepository = $groupRepository;
        $this->userContext = $userContext;
    }

    /**
     * Create task with operations of update Category Permission from Shared Catalog permission.
     *
     * @param array $categoryIds Shared Category IDs array
     * @param array $groupIds Shared Catalog customer group IDs
     * @param int|null $userId Task creator User ID
     * @throws LocalizedException
     * @return void
     */
    public function execute(array $categoryIds, array $groupIds, ?int $userId = null): void
    {
        if (empty($categoryIds)) {
            return;
        }

        if (!$userId && (int) $this->userContext->getUserType() === UserContextInterface::USER_TYPE_ADMIN) {
            $userId = $this->userContext->getUserId();
        }

        $categoryIds = array_unique($categoryIds);
        $groupIds = array_unique($groupIds);

        $bulkUuid = $this->identityService->generateId();
        $bulkDescription = __('Assign Categories to Shared Catalog');
        $operations = [];
        foreach ($categoryIds as $categoryId) {
            $dataToEncode = [
                'category_id' => $categoryId,
                'group_ids' => implode(',', $groupIds)
            ];
            $data = [
                'data' => [
                    'bulk_uuid' => $bulkUuid,
                    'topic_name' => $this->queueTopic,
                    'serialized_data' => $this->serializer->serialize($dataToEncode),
                    'status' => OperationInterface::STATUS_TYPE_OPEN,
                ]
            ];

            /** @var OperationInterface $operation */
            $operation = $this->operationFactory->create($data);
            $operations[] = $operation;
        }

        $result = $this->bulkManagement->scheduleBulk($bulkUuid, $operations, $bulkDescription, $userId);
        if (!$result) {
            throw new LocalizedException(
                __('Something went wrong while scheduling operations.')
            );
        }
    }
}
