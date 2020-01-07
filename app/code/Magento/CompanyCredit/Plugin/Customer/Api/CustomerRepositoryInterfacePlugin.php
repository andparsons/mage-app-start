<?php
namespace Magento\CompanyCredit\Plugin\Customer\Api;

use \Magento\Customer\Api\CustomerRepositoryInterface;

/**
 * Class CustomerRepositoryInterfacePlugin.
 */
class CustomerRepositoryInterfacePlugin
{
    /**
     * @var \Magento\CompanyCredit\Model\HistoryRepositoryInterface
     */
    private $historyRepository;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * GroupRepositoryInterfacePlugin constructor.
     *
     * @param \Magento\CompanyCredit\Model\HistoryRepositoryInterface $historyRepository
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        \Magento\CompanyCredit\Model\HistoryRepositoryInterface $historyRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->historyRepository = $historyRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * Set user id to NULL in credit history log.
     *
     * @param CustomerRepositoryInterface $subject
     * @param \Closure $method
     * @param int $customerId
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundDeleteById(
        CustomerRepositoryInterface $subject,
        \Closure $method,
        $customerId
    ) {
        $result = $method($customerId);
        if ($result) {
            $searchCriteria = $this->searchCriteriaBuilder
                ->addFilter(\Magento\CompanyCredit\Model\HistoryInterface::USER_ID, $customerId)
                ->create();
            $historyItems = $this->historyRepository->getList($searchCriteria)->getItems();
            foreach ($historyItems as $history) {
                $history->setUserId(null);
                $this->historyRepository->save($history);
            }
        }
        return $result;
    }
}
