<?php

declare(strict_types=1);

namespace Magento\Company\Model;

use Magento\Company\Api\CompanyManagementInterface;
use Magento\Company\Api\CompanyUserManagerInterface;
use Magento\Company\Api\Data\CompanyCustomerInterface;
use Magento\Company\Model\Action\Customer\Assign;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * @inheritdoc
 */
class CompanyUserManager implements CompanyUserManagerInterface
{
    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var EncryptorInterface
     */
    private $encryptor;

    /**
     * @var TransportBuilder
     */
    private $transportBuilder;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ScopeConfigInterface
     */
    private $config;

    /**
     * @var CompanyManagementInterface
     */
    private $management;

    /**
     * @var Assign
     */
    private $roleAssigner;

    /**
     * @param CustomerRepositoryInterface $customerRepository
     * @param EncryptorInterface $encryptor
     * @param TransportBuilder $transportBuilder
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface $config
     * @param CompanyManagementInterface $management
     * @param Assign $roleAssigner
     */
    public function __construct(
        CustomerRepositoryInterface $customerRepository,
        EncryptorInterface $encryptor,
        TransportBuilder $transportBuilder,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $config,
        CompanyManagementInterface $management,
        Assign $roleAssigner
    ) {
        $this->customerRepository = $customerRepository;
        $this->encryptor = $encryptor;
        $this->transportBuilder = $transportBuilder;
        $this->storeManager = $storeManager;
        $this->config = $config;
        $this->management = $management;
        $this->roleAssigner = $roleAssigner;
    }

    /**
     * @inheritDoc
     */
    public function acceptInvitation(
        string $invitationCode,
        CompanyCustomerInterface $attributes,
        ?string $roleId
    ): void {
        $customer = $this->customerRepository->getById($attributes->getCustomerId());

        if ($customer->getExtensionAttributes()->getCompanyAttributes()
            && $customer->getExtensionAttributes()->getCompanyAttributes()->getCompanyId()
        ) {
            throw new CouldNotSaveException(__('Already assigned to a company'));
        }
        $this->validateCode($invitationCode, $attributes);
        $customer->getExtensionAttributes()->setCompanyAttributes($attributes);
        try {
            $this->customerRepository->save($customer);
            if ($roleId) {
                $this->roleAssigner->assignCustomerRole($customer, $roleId);
            }
        } catch (\Throwable $exception) {
            throw new CouldNotSaveException(__('Could not assign to the company'), $exception);
        }
    }

    /**
     * Validate invitation.
     *
     * @param string $code
     * @param CompanyCustomerInterface $data
     * @throws CouldNotSaveException
     * @return void
     */
    private function validateCode(string $code, CompanyCustomerInterface $data): void
    {
        $flat = $this->flattenCustomerData($data);
        if (!$this->encryptor->isValidHash($flat, $code)) {
            throw new CouldNotSaveException(__('Invalid code provided'));
        }
    }

    /**
     * @inheritDoc
     */
    public function sendInvitation(CompanyCustomerInterface $forCustomer, ?string $roleId): void
    {
        $code = $this->createCode($forCustomer);
        $customer = $this->customerRepository->getById($forCustomer->getCustomerId());
        $admin = $this->management->getAdminByCompanyId($forCustomer->getCompanyId());
        $company = $this->management->getByCustomerId($admin->getId());
        $storeId = $customer->getStoreId();
        if (!$storeId && $customer->getWebsiteId()) {
            $stores = $this->storeManager->getWebsite($customer->getWebsiteId())->getStoreIds();
            $storeId = reset($stores);
        }

        $transport = $this->transportBuilder->setTemplateIdentifier('company_invite_existing_customer_template')
            ->setTemplateOptions(['area' => 'frontend', 'store' => $storeId])
            ->setTemplateVars(
                [
                    'code' => $code,
                    'companyAttributes' => $forCustomer->getData(),
                    'customer' => $customer,
                    'admin' => $admin,
                    'company' => $company,
                    'roleId' => $roleId
                ]
            )
            ->setFrom(
                $this->config->getValue(
                    'customer/create_account/email_identity',
                    ScopeInterface::SCOPE_STORE,
                    $storeId
                )
            )
            ->addTo($customer->getEmail(), $customer->getFirstname())
            ->getTransport();
        $transport->sendMessage();
    }

    /**
     * Flatten array to string.
     *
     * @param CompanyCustomerInterface $data
     * @return string
     */
    private function flattenCustomerData(CompanyCustomerInterface $data): string
    {
        $data = $data->getData();
        ksort($data);

        return implode('|', $data);
    }

    /**
     * Create secret code to validate invitation.
     *
     * @param CompanyCustomerInterface $customerData
     * @return string
     */
    private function createCode(CompanyCustomerInterface $customerData): string
    {
        return $this->encryptor->hash($this->flattenCustomerData($customerData));
    }
}
