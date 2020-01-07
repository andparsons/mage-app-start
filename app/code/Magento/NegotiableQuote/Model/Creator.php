<?php
namespace Magento\NegotiableQuote\Model;

use Magento\Authorization\Model\UserContextInterface;

/**
 * Class for retrieve creator name.
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
     * Retrieve name of creator by type and id.
     *
     * @param int $type
     * @param int $id
     * @param int|null $quoteId [optional]
     * @return string
     * @throws \Magento\Framework\Exception\IntegrationException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function retrieveCreatorName($type, $id, $quoteId = null)
    {
        if ($type == UserContextInterface::USER_TYPE_ADMIN) {
            try {
                $user = $this->userFactory->create();
                $this->userResource->load($user, $id);
                return $user->getFirstName() . ' ' . $user->getLastName();
            } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                if ($quoteId) {
                    return $this->provider->getSalesRepresentativeName($quoteId);
                }
            }
        } elseif ($type == UserContextInterface::USER_TYPE_INTEGRATION) {
            $integration = $this->integration->get($id);
            return $integration->getName();
        } elseif ($type == UserContextInterface::USER_TYPE_CUSTOMER) {
            try {
                $customer = $this->customerRepository->getById($id);
                return $this->customerNameGeneration->getCustomerName($customer);
            } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                if ($quoteId) {
                    return $this->provider->getCustomerName($quoteId);
                }
            }
        }

        return '';
    }
}
