<?php
declare(strict_types=1);

namespace Magento\RequisitionList\Model\Checker;

use Magento\RequisitionList\Api\Data\RequisitionListItemInterface;
use Magento\RequisitionList\Api\RequisitionListRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\RequisitionList\Model\RequisitionList\Items;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Catalog\Api\Data\ProductCustomOptionInterface;
use Magento\Catalog\Model\Product\Type\AbstractType;

/**
 * Responsible for checking availability of requisition list item option.
 */
class RequisitionListItemOptionAvailability
{
    /**
     * @var RequisitionListRepositoryInterface
     */
    private $requisitionListRepository;

    /**
     * @var Items
     */
    private $requisitionListItems;

    /**
     * @var UserContextInterface
     */
    private $userContext;

    /**
     * @var ProductCustomOptionInterface
     */
    private $productCustomOption;

    /**
     * @param RequisitionListRepositoryInterface $requisitionListRepository
     * @param Items $requisitionListItems
     * @param UserContextInterface $userContext
     * @param ProductCustomOptionInterface $productCustomOption
     */
    public function __construct(
        RequisitionListRepositoryInterface $requisitionListRepository,
        Items $requisitionListItems,
        UserContextInterface $userContext,
        ProductCustomOptionInterface $productCustomOption
    ) {
        $this->requisitionListRepository = $requisitionListRepository;
        $this->requisitionListItems = $requisitionListItems;
        $this->userContext = $userContext;
        $this->productCustomOption = $productCustomOption;
    }

    /**
     * Is requisition list item available for current user.
     *
     * @param RequisitionListItemInterface $requisitionListItem
     * @return bool
     */
    public function isAvailableForCurrentUser(RequisitionListItemInterface $requisitionListItem): bool
    {
        if ($this->userContext->getUserType() == UserContextInterface::USER_TYPE_GUEST) {
            return false;
        }

        try {
            $list = $this->requisitionListRepository->get($requisitionListItem->getRequisitionListId());
        } catch (NoSuchEntityException $e) {
            return false;
        }

        if (!($list->getCustomerId() == $this->userContext->getUserId())) {
            return false;
        }

        return true;
    }

    /**
     * Check is custom option for download.
     *
     * @param string $code
     * @return bool
     */
    public function isCustomOptionForDownload(string $code): bool
    {
        $optionId = $this->getOptionId($code);
        $productOption = $optionId ? $this->productCustomOption->load($optionId) : null;
        return ($productOption && $productOption->getId() && $productOption->getType() == 'file');
    }

    /**
     * Get option id.
     *
     * @param string $code
     * @return int|null
     */
    private function getOptionId(string $code)
    {
        if (strpos($code, AbstractType::OPTION_PREFIX) === 0) {
            $optionId = str_replace(
                AbstractType::OPTION_PREFIX,
                '',
                $code
            );
            if ((int)$optionId != $optionId) {
                $optionId = null;
            }
        }

        return $optionId;
    }
}
