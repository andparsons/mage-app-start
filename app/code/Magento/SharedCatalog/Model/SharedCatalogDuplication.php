<?php
declare(strict_types=1);

namespace Magento\SharedCatalog\Model;

use Magento\Customer\Api\Data\GroupInterface;
use Magento\SharedCatalog\Api\Data\SharedCatalogInterface;

/**
 * Shared catalog duplication action.
 */
class SharedCatalogDuplication implements \Magento\SharedCatalog\Api\SharedCatalogDuplicationInterface
{
    /**
     * @var \Magento\SharedCatalog\Api\ProductItemManagementInterface
     */
    private $sharedCatalogProductItemManagement;

    /**
     * @var \Magento\SharedCatalog\Model\SharedCatalogInvalidation
     */
    private $sharedCatalogInvalidation;

    /**
     * @param \Magento\SharedCatalog\Api\ProductItemManagementInterface $productItemManagement
     * @param \Magento\SharedCatalog\Model\SharedCatalogInvalidation $sharedCatalogInvalidation
     */
    public function __construct(
        \Magento\SharedCatalog\Api\ProductItemManagementInterface $productItemManagement,
        \Magento\SharedCatalog\Model\SharedCatalogInvalidation $sharedCatalogInvalidation
    ) {
        $this->sharedCatalogProductItemManagement = $productItemManagement;
        $this->sharedCatalogInvalidation = $sharedCatalogInvalidation;
    }

    /**
     * @inheritdoc
     *
     * Assign products by SKUs to each shared catalog (customer group) which id provided in $sharedCatalogId parameter.
     * If system identifies that provided shared catalog type is public - the "Not Logged In" customer group will be
     * added to the list of customer groups where products should be assigned.
     */
    public function assignProductsToDuplicate(int $sharedCatalogId, array $productsSku): void
    {
        $sharedCatalog = $this->sharedCatalogInvalidation->checkSharedCatalogExist($sharedCatalogId);
        $customerGroupIds[] = $sharedCatalog->getCustomerGroupId();
        if ($sharedCatalog->getType() === SharedCatalogInterface::TYPE_PUBLIC) {
            $customerGroupIds[] = GroupInterface::NOT_LOGGED_IN_ID;
        }
        foreach ($customerGroupIds as $customerGroupId) {
            $this->sharedCatalogProductItemManagement->addItems(
                $customerGroupId,
                $productsSku
            );
        }
    }
}
