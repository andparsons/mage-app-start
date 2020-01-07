<?php

namespace Magento\Company\Model;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Handle customer management for company.
 */
class CustomerRetriever
{
    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     */
    public function __construct(
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
    ) {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->customerRepository = $customerRepository;
    }

    /**
     * Retrieve customer from default website, if it is not there try to load from all websites.
     *
     * @param string $email
     * @return CustomerInterface|null
     * @throws LocalizedException
     */
    public function retrieveByEmail(string $email)
    {
        $customer = null;
        try {
            $customer = $this->customerRepository->get($email);
        } catch (NoSuchEntityException $e) {
            $searchCriteria = $this->searchCriteriaBuilder
                ->addFilter(CustomerInterface::EMAIL, $email)
                ->setPageSize(1)
                ->create();
            $items = $this->customerRepository->getList($searchCriteria)->getItems();
            $customer = array_shift($items);
        }

        return $customer;
    }

    /**
     * Find customer within specific website.
     *
     * @param string $email
     * @param string|null $websiteId
     *
     * @return CustomerInterface|null
     * @throws LocalizedException
     */
    public function retrieveForWebsite(string $email, string $websiteId = null)
    {
        try {
            return $this->customerRepository->get($email, $websiteId);
        } catch (NoSuchEntityException $e) {
            return null;
        }
    }
}
