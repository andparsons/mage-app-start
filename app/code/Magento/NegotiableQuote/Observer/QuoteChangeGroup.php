<?php

declare(strict_types=1);

namespace Magento\NegotiableQuote\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * Change quote customer group ids after change group in customer.
 */
class QuoteChangeGroup implements ObserverInterface
{
    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var \Magento\NegotiableQuote\Api\NegotiableQuoteRepositoryInterface
     */
    private $negotiableQuoteRepository;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\NegotiableQuote\Api\NegotiableQuoteRepositoryInterface $negotiableQuoteRepository
     * @param \Magento\Quote\Api\CartRepositoryInterface $cartRepository
     */
    public function __construct(
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\NegotiableQuote\Api\NegotiableQuoteRepositoryInterface $negotiableQuoteRepository,
        \Magento\Quote\Api\CartRepositoryInterface $cartRepository
    ) {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->negotiableQuoteRepository = $negotiableQuoteRepository;
        $this->cartRepository = $cartRepository;
    }

    /**
     * @inheritdoc
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var \Magento\Customer\Api\Data\CustomerInterface $customer */
        $customer = $observer->getEvent()->getData('customer_data_object');
        /** @var \Magento\Customer\Api\Data\CustomerInterface $prevCustomer */
        $prevCustomer = $observer->getEvent()->getData('orig_customer_data_object');

        if ($prevCustomer && $customer->getGroupId() != $prevCustomer->getGroupId()) {
            $this->searchCriteriaBuilder->addFilter('customer_id', $customer->getId());
            $this->searchCriteriaBuilder->addFilter('customer_group_id', $prevCustomer->getGroupId());
            $quotes = $this->negotiableQuoteRepository->getList($this->searchCriteriaBuilder->create())->getItems();
            /** @var \Magento\Quote\Model\Quote $quote */
            foreach ($quotes as $quote) {
                $quote->setData('customer_group_id', $customer->getGroupId());
                $this->cartRepository->save($quote);
            }
        }
    }
}
