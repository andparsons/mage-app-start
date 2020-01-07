<?php
namespace Magento\Company\Model\Action;

use Magento\Company\Api\CompanyUserManagerInterface;
use Magento\Company\Api\Data\CompanyCustomerInterface;
use Magento\Company\Model\CompanyUser;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Company\Api\CompanyRepositoryInterface;

/**
 * Create or update customer from request.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SaveCustomer
{
    /**
     * @var \Magento\Company\Model\Action\Customer\Populator
     */
    private $customerPopulator;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var \Magento\Company\Api\CompanyRepositoryInterface
     */
    private $companyRepository;

    /**
     * @var \Magento\Company\Model\Action\Customer\Assign
     */
    private $roleAssigner;

    /**
     * @var \Magento\Company\Model\Action\Customer\Create
     */
    private $customerCreator;

    /**
     * @var CompanyUserManagerInterface
     */
    private $userManager;

    /**
     * @var CompanyUser
     */
    private $userHelper;

    /**
     * @param Customer\Populator $customerPopulator
     * @param CustomerRepositoryInterface $customerRepository
     * @param CompanyRepositoryInterface $companyRepository
     * @param Customer\Assign $roleAssigner
     * @param Customer\Create $customerCreator
     * @param CompanyUserManagerInterface|null $userManager
     * @param CompanyUser|null $companyUser
     */
    public function __construct(
        Customer\Populator $customerPopulator,
        CustomerRepositoryInterface $customerRepository,
        CompanyRepositoryInterface $companyRepository,
        Customer\Assign $roleAssigner,
        Customer\Create $customerCreator,
        ?CompanyUserManagerInterface $userManager = null,
        ?CompanyUser $companyUser = null
    ) {
        $this->customerPopulator = $customerPopulator;
        $this->customerRepository = $customerRepository;
        $this->companyRepository = $companyRepository;
        $this->roleAssigner = $roleAssigner;
        $this->customerCreator = $customerCreator;
        $this->userManager = $userManager ?? ObjectManager::getInstance()->get(CompanyUserManagerInterface::class);
        $this->userHelper = $companyUser ?? ObjectManager::getInstance()->get(CompanyUser::class);
    }

    /**
     * Create customer from request.
     *
     * @param RequestInterface $request
     * @return CustomerInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws InviteConfirmationNeededException
     * @throws \Magento\Framework\Exception\State\InputMismatchException
     */
    public function create(RequestInterface $request)
    {
        try {
            $customer = $this->customerRepository->get($request->getParam('email'));
            if ($this->hasCustomerCompany($customer)) {
                throw new \Magento\Framework\Exception\State\InputMismatchException(
                    __('A customer with the same email already assigned to company.')
                );
            }
        } catch (NoSuchEntityException $e) {
            $customer = null;
        }

        $customer = $this->customerPopulator->populate($request->getParams(), $customer);
        if ($customer->getId()) {
            $this->sendInvitationToExisting($customer);
            throw new InviteConfirmationNeededException(
                __(
                    'Invitation was sent to an existing customer, '
                    .'they will be added to your organization once they accept the invitation.'
                ),
                $customer
            );
        }
        $targetId = $request->getParam('target_id');
        $customer = $this->customerCreator->execute($customer, $targetId);
        $this->roleAssigner->assignCustomerRole($customer, $request->getParam('role'));

        return $customer;
    }

    /**
     * Update customer from request.
     *
     * @param RequestInterface $request
     * @return CustomerInterface
     * @throws InputMismatchException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function update(RequestInterface $request)
    {
        $customerId = $request->getParam('customer_id');

        $customer = $this->customerRepository->getById($customerId);
        $company = $this->companyRepository->get(
            $customer->getExtensionAttributes()->getCompanyAttributes()->getCompanyId()
        );
        $customer = $this->customerPopulator->populate(
            $request->getParams(),
            $customer
        );
        $this->customerRepository->save($customer);
        if ($company->getSuperUserId() != $customerId) {
            $this->roleAssigner->assignCustomerRole($customer, $request->getParam('role'));
        }

        return $customer;
    }

    /**
     * Has customer company.
     *
     * @param CustomerInterface $customer
     * @return bool
     */
    private function hasCustomerCompany(CustomerInterface $customer)
    {
        return $customer->getExtensionAttributes()
        && $customer->getExtensionAttributes()->getCompanyAttributes()
        && (int)$customer->getExtensionAttributes()->getCompanyAttributes()->getCompanyId() > 0;
    }

    /**
     * When trying to assign existing customer then sending them an invite first.
     *
     * @param CustomerInterface $customer
     * @throws LocalizedException
     * @return void
     */
    private function sendInvitationToExisting(CustomerInterface $customer): void
    {
        if (!$companyId = $this->userHelper->getCurrentCompanyId()) {
            throw new \RuntimeException('Meant to be initiated by a company customer');
        }
        /** @var CompanyCustomerInterface $companyAttributes */
        $companyAttributes = $customer->getExtensionAttributes()->getCompanyAttributes();
        $companyAttributes->setCustomerId($customer->getId());
        $companyAttributes->setCompanyId($companyId);
        $this->userManager->sendInvitation($companyAttributes, null);
    }
}
