<?php
namespace Magento\CompanyCredit\Model;

use Magento\Authorization\Model\UserContextInterface;

/**
 * Retrieve creator name by type (integration, admin, customer) and Id.
 */
class Creator
{
    /**
     * @var \Magento\User\Model\ResourceModel\User
     */
    private $userResource;

    /**
     * @var \Magento\User\Api\Data\UserInterfaceFactory
     */
    private $userFactory;

    /**
     * @var \Magento\Integration\Api\IntegrationServiceInterface
     */
    private $integration;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var \Magento\Customer\Api\CustomerNameGenerationInterface
     */
    private $customerNameGeneration;

    /**
     * @var \Magento\NegotiableQuote\Model\Purged\Provider
     */
    private $provider;

    /**
     * @param \Magento\User\Model\ResourceModel\User $userResource
     * @param \Magento\User\Api\Data\UserInterfaceFactory $userFactory
     * @param \Magento\Integration\Api\IntegrationServiceInterface $integration
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Magento\Customer\Api\CustomerNameGenerationInterface $customerNameGeneration
     * @param \Magento\NegotiableQuote\Model\Purged\Provider $provider
     */
    public function __construct(
        \Magento\User\Model\ResourceModel\User $userResource,
        \Magento\User\Api\Data\UserInterfaceFactory $userFactory,
        \Magento\Integration\Api\IntegrationServiceInterface $integration,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Customer\Api\CustomerNameGenerationInterface $customerNameGeneration,
        \Magento\NegotiableQuote\Model\Purged\Provider $provider
    ) {
        $this->userResource = $userResource;
        $this->userFactory = $userFactory;
        $this->integration = $integration;
        $this->customerRepository = $customerRepository;
        $this->customerNameGeneration = $customerNameGeneration;
        $this->provider = $provider;
    }

    /**
     * Retrieve creator name by type (integration, admin, customer) and Id.
     *
     * @param int $type
     * @param int $id
     * @return string
     */
    public function retrieveCreatorName($type, $id)
    {
        if ($type == UserContextInterface::USER_TYPE_ADMIN) {
            $user = $this->userFactory->create();
            $this->userResource->load($user, $id);
            return $user->getFirstName() . ' ' . $user->getLastName();
        } elseif ($type == UserContextInterface::USER_TYPE_INTEGRATION) {
            $integration = $this->integration->get($id);
            return $integration->getName();
        } elseif ($type == UserContextInterface::USER_TYPE_CUSTOMER) {
            $customer = $this->customerRepository->getById($id);
            return $this->customerNameGeneration->getCustomerName($customer);
        }

        return '';
    }
}
