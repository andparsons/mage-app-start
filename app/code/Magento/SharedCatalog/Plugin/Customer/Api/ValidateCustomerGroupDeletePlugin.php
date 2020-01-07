<?php
namespace Magento\SharedCatalog\Plugin\Customer\Api;

use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotDeleteException;

/**
 * Check is shared catalog linked to the customer group.
 */
class ValidateCustomerGroupDeletePlugin
{
    /**
     * @var \Magento\SharedCatalog\Model\SharedCatalogLocator
     */
    private $sharedCatalogLocator;

    /**
     * @param \Magento\SharedCatalog\Model\SharedCatalogLocator $sharedCatalogLocator
     */
    public function __construct(\Magento\SharedCatalog\Model\SharedCatalogLocator $sharedCatalogLocator)
    {
        $this->sharedCatalogLocator = $sharedCatalogLocator;
    }

    /**
     * Throw exception if shared catalog is linked to the customer group.
     *
     * @param GroupRepositoryInterface $subject
     * @param int $groupId
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     * @return array
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeDeleteById(GroupRepositoryInterface $subject, $groupId)
    {
        if ($this->groupHasSharedCatalog($groupId)) {
            throw new CouldNotDeleteException(
                __(
                    'A shared catalog is linked to this customer group. '
                    . 'You must delete the shared catalog before you can delete this customer group.'
                )
            );
        };

        return [$groupId];
    }

    /**
     * Check are shared catalogs linked to the customer group.
     *
     * @param int $groupId
     * @return bool
     */
    private function groupHasSharedCatalog($groupId)
    {
        try {
            $sharedCatalog = $this->sharedCatalogLocator->getSharedCatalogByCustomerGroup($groupId);
            if (!empty($sharedCatalog)) {
                return true;
            }
        } catch (NoSuchEntityException $exception) {
            // Normal behaviour. There is no shared catalog linked with the customer group.
        }

        return false;
    }
}
