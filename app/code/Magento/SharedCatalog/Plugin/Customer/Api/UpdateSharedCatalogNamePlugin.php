<?php
namespace Magento\SharedCatalog\Plugin\Customer\Api;

use Magento\Customer\Api\GroupRepositoryInterface;
use \Magento\Customer\Api\Data\GroupInterface;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Set shared catalog name equal to the related customer group code.
 */
class UpdateSharedCatalogNamePlugin
{
    /**
     * @var \Magento\SharedCatalog\Model\SharedCatalogLocator
     */
    private $sharedCatalogLocator;

    /**
     * @var \Magento\SharedCatalog\Api\SharedCatalogRepositoryInterface
     */
    private $sharedCatalogRepository;

    /**
     * @param \Magento\SharedCatalog\Model\SharedCatalogLocator $sharedCatalogLocator
     * @param \Magento\SharedCatalog\Api\SharedCatalogRepositoryInterface $sharedCatalogRepository
     */
    public function __construct(
        \Magento\SharedCatalog\Model\SharedCatalogLocator $sharedCatalogLocator,
        \Magento\SharedCatalog\Api\SharedCatalogRepositoryInterface $sharedCatalogRepository
    ) {
        $this->sharedCatalogLocator = $sharedCatalogLocator;
        $this->sharedCatalogRepository = $sharedCatalogRepository;
    }

    /**
     * Set shared catalog name equal to the related customer group code.
     *
     * @param GroupRepositoryInterface $subject
     * @param GroupInterface $group
     * @return GroupInterface
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterSave(GroupRepositoryInterface $subject, GroupInterface $group)
    {
        try {
            $sharedCatalog = $this->sharedCatalogLocator->getSharedCatalogByCustomerGroup($group->getId());

            if ($sharedCatalog && $sharedCatalog->getName() != $group->getCode()) {
                $sharedCatalog->setName($group->getCode());
                $this->sharedCatalogRepository->save($sharedCatalog);
            }
        } catch (NoSuchEntityException $exception) {
            // Normal behaviour. There is no shared catalog linked with the customer group.
        }

        return $group;
    }
}
