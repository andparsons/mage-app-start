<?php
namespace Magento\CompanyCredit\Model\Sales;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\Data\OrderInterface;

/**
 * This class is used to retrieve order by increment Id.
 */
class OrderLocator
{
    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->orderRepository = $orderRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * Retrieve order by increment Id using repository.
     *
     * @param string $incrementId
     * @return OrderInterface
     * @throws NoSuchEntityException
     */
    public function getOrderByIncrementId($incrementId)
    {
        $this->searchCriteriaBuilder->addFilter(
            OrderInterface::INCREMENT_ID,
            $incrementId
        );
        $searchCriteria = $this->searchCriteriaBuilder->create();
        $result = $this->orderRepository->getList($searchCriteria);
        $items = $result->getItems();
        if (empty($items)) {
            throw new NoSuchEntityException(__(
                'No such entity with %fieldName = %fieldValue',
                [
                    'fieldName' => OrderInterface::INCREMENT_ID,
                    'fieldValue' => $incrementId
                ]
            ));
        }
        return reset($items);
    }
}
