<?php

namespace Magento\NegotiableQuote\Plugin\Quote\Model;

use Magento\Framework\Exception\NoSuchEntityException;

/**
 * QuoteAddressValidatorPlugin disables validation of quote addresses for deleted users.
 */
class QuoteAddressValidatorPlugin
{
    /**
     * QuoteAddressValidatorPlugin constructor.
     *
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     */
    public function __construct(
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
    ) {
        $this->customerRepository = $customerRepository;
    }

    /**
     * Skip validation for not existing users.
     *
     * @param \Magento\Quote\Model\QuoteAddressValidator $subject
     * @param \Closure $proceed
     * @param \Magento\Quote\Api\Data\AddressInterface $addressData
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundValidate(
        \Magento\Quote\Model\QuoteAddressValidator $subject,
        \Closure $proceed,
        \Magento\Quote\Api\Data\AddressInterface $addressData
    ) {
        try {
            $this->customerRepository->getById($addressData->getCustomerId());
            return $proceed($addressData);
        } catch (NoSuchEntityException $e) {
            return true;
        }
    }
}
