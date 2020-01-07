<?php
namespace Magento\RequisitionList\CustomerData;

use Magento\Customer\CustomerData\SectionSourceInterface;
use Magento\Authorization\Model\UserContextInterface;
use Magento\RequisitionList\Api\RequisitionListRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\RequisitionList\Model\Config as ModuleConfig;
use Magento\RequisitionList\Api\Data\RequisitionListInterface;
use Magento\Framework\Api\SortOrderBuilder;

/**
 * Requisition section
 */
class Requisition implements SectionSourceInterface
{
    /**
     * @var RequisitionListRepositoryInterface
     */
    private $requisitionListRepository;

    /**
     * @var \Magento\Authorization\Model\UserContextInterface
     */
    private $userContext;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var ModuleConfig
     */
    private $moduleConfig;

    /**
     * @var SortOrderBuilder
     */
    private $sortOrderBuilder;

    /**
     * @var \Magento\Framework\App\Http\Context
     */
    private $httpContext;

    /**
     * @param RequisitionListRepositoryInterface $requisitionListRepository
     * @param UserContextInterface $userContext
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param ModuleConfig $moduleConfig
     * @param SortOrderBuilder $sortOrderBuilder
     * @param \Magento\Framework\App\Http\Context $httpContext
     */
    public function __construct(
        RequisitionListRepositoryInterface $requisitionListRepository,
        UserContextInterface $userContext,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        ModuleConfig $moduleConfig,
        SortOrderBuilder $sortOrderBuilder,
        \Magento\Framework\App\Http\Context $httpContext
    ) {
        $this->requisitionListRepository = $requisitionListRepository;
        $this->userContext = $userContext;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->moduleConfig = $moduleConfig;
        $this->sortOrderBuilder = $sortOrderBuilder;
        $this->httpContext = $httpContext;
    }

    /**
     * {@inheritdoc}
     */
    public function getSectionData()
    {
        return [
            'items' => $this->getRequisitionLists(),
            'max_allowed_requisition_lists' => $this->moduleConfig->getMaxCountRequisitionList(),
            'is_enabled' => (bool)$this->httpContext->getValue(\Magento\Customer\Model\Context::CONTEXT_AUTH)
        ];
    }

    /**
     * Get RequisitionList items
     *
     * @return array
     */
    private function getRequisitionLists()
    {
        $customerId = $this->userContext->getUserId();
        if (!$customerId) {
            return [];
        }
        /**@var \Magento\Framework\Api\SortOrder $nameSort */
        $nameSort = $this->sortOrderBuilder
            ->setField(RequisitionListInterface::NAME)
            ->setAscendingDirection()
            ->create();
        $builder = $this->searchCriteriaBuilder
            ->addFilter(RequisitionListInterface::CUSTOMER_ID, $customerId)
            ->addSortOrder($nameSort);

        $lists = $this->requisitionListRepository->getList($builder->create())->getItems();
        if (empty($lists)) {
            return [];
        }

        $items = [];
        /**@var \Magento\RequisitionList\Api\Data\RequisitionListInterface $list */
        foreach ($lists as $list) {
            $items[] = [
                'id' => $list->getId(),
                'name' => $list->getName()
            ];
        }
        return $items;
    }
}
