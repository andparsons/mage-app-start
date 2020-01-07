<?php

namespace Magento\NegotiableQuote\Model\Plugin\Customer\Model;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;

/**
 * CustomerRepository plugin detects changes in customer and stores appropriate data to quotes.
 */
class CustomerRepository
{
    /**
     * @var \Magento\NegotiableQuote\Model\ResourceModel\QuoteGrid
     */
    private $quoteGrid;

    /**
     * @var \Magento\Customer\Api\CustomerNameGenerationInterface
     */
    private $customerViewHelper;

    /**
     * @var \Magento\NegotiableQuote\Model\Purged\Extractor
     */
    private $extractor;

    /**
     * @var \Magento\NegotiableQuote\Model\Purged\Handler
     */
    private $purgedContentsHandler;

    /**
     * CustomerRepository constructor.
     *
     * @param \Magento\NegotiableQuote\Model\ResourceModel\QuoteGrid $quoteGrid
     * @param \Magento\Customer\Api\CustomerNameGenerationInterface $customerViewHelper
     * @param \Magento\NegotiableQuote\Model\Purged\Extractor $extractor
     * @param \Magento\NegotiableQuote\Model\Purged\Handler $purgedContentsHandler
     */
    public function __construct(
        \Magento\NegotiableQuote\Model\ResourceModel\QuoteGrid $quoteGrid,
        \Magento\Customer\Api\CustomerNameGenerationInterface $customerViewHelper,
        \Magento\NegotiableQuote\Model\Purged\Extractor $extractor,
        \Magento\NegotiableQuote\Model\Purged\Handler $purgedContentsHandler
    ) {
        $this->quoteGrid = $quoteGrid;
        $this->customerViewHelper = $customerViewHelper;
        $this->extractor = $extractor;
        $this->purgedContentsHandler = $purgedContentsHandler;
    }

    /**
     * Company around save
     *
     * @param CustomerRepositoryInterface $subject
     * @param \Closure $proceed
     * @param CustomerInterface $customer
     * @param string|null $passwordHash
     * @return CustomerInterface
     */
    public function aroundSave(
        CustomerRepositoryInterface $subject,
        \Closure $proceed,
        CustomerInterface $customer,
        $passwordHash = null
    ) {
        $oldCustomerData = null;

        if ($customer->getId()) {
            $oldCustomerData = $subject->getById($customer->getId());
        }

        /** @var CustomerInterface $result */
        $result = $proceed($customer, $passwordHash);

        if ($oldCustomerData && $this->hasNameChanges($customer, $oldCustomerData)) {
            $this->quoteGrid->refreshValue(
                \Magento\NegotiableQuote\Model\ResourceModel\QuoteGrid::CUSTOMER_ID,
                $customer->getId(),
                \Magento\NegotiableQuote\Model\ResourceModel\QuoteGrid::SUBMITTED_BY,
                $this->customerViewHelper->getCustomerName($customer)
            );
        }

        return $result;
    }

    /**
     * Check is customer has name changes
     *
     * @param CustomerInterface $customer
     * @param CustomerInterface $oldCustomer
     * @return bool
     */
    protected function hasNameChanges(CustomerInterface $customer, CustomerInterface $oldCustomer)
    {
        $hasNameChanges = false;

        if ($this->customerViewHelper->getCustomerName($customer)
            !== $this->customerViewHelper->getCustomerName($oldCustomer)) {
            $hasNameChanges = true;
        }

        return $hasNameChanges;
    }

    /**
     * Store customer related data in quote after delete customer.
     *
     * @param CustomerRepositoryInterface $subject
     * @param int $customerId
     * @return void
     * @throws \Exception
     */
    public function beforeDeleteById(
        CustomerRepositoryInterface $subject,
        $customerId
    ) {
        $customer = $subject->getById($customerId);

        if ($customer->getExtensionAttributes()
            && $customer->getExtensionAttributes()->getCompanyAttributes()
            && $customer->getExtensionAttributes()->getCompanyAttributes()->getCompanyId()
        ) {
            $associatedCustomerData = $this->extractor->extractCustomer($subject->getById($customerId));
            $this->purgedContentsHandler->process($associatedCustomerData, $customerId);
        }
    }
}
