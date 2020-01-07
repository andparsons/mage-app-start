<?php

namespace Magento\NegotiableQuote\Model\Email\Provider;

/**
 * Sales Representative (Sales Rep) is an identity associated with specific automated Email messages from the merchant.
 */
class SalesRepresentative
{
    /**
     * @var \Magento\User\Api\Data\UserInterface[]
     */
    private $userEntities = [];

    /**
     * @var \Magento\User\Api\Data\UserInterfaceFactory
     */
    private $userFactory;

    /**
     * @var \Magento\Company\Api\CompanyManagementInterface
     */
    private $companyManagement;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @param \Magento\User\Api\Data\UserInterfaceFactory $userFactory
     * @param \Magento\Company\Api\CompanyManagementInterface $companyManagement
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Magento\User\Api\Data\UserInterfaceFactory $userFactory,
        \Magento\Company\Api\CompanyManagementInterface $companyManagement,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->userFactory = $userFactory;
        $this->companyManagement = $companyManagement;
        $this->logger = $logger;
    }

    /**
     * Get merchant user entity from quote.
     *
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     * @return \Magento\User\Api\Data\UserInterface|null
     */
    public function getSalesRepresentativeForQuote(\Magento\Quote\Api\Data\CartInterface $quote)
    {
        $customerId = $quote->getCustomer() && $quote->getCustomer()->getId()
            ? $quote->getCustomer()->getId()
            : 0;

        if (!isset($this->userEntities[$customerId])) {
            try {
                $company = $this->companyManagement->getByCustomerId($customerId);
                if ($company && $company->getSalesRepresentativeId()) {
                    $user = $this->userFactory->create()->load($company->getSalesRepresentativeId());
                    $this->userEntities[$customerId] = $user;
                }
            } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                $this->logger->error($e);
                return null;
            }
        }

        return $this->userEntities[$customerId];
    }
}
