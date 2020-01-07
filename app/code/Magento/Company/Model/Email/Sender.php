<?php

namespace Magento\Company\Model\Email;

use Magento\Company\Api\CompanyRepositoryInterface;
use Magento\Company\Model\Config\EmailTemplate as EmailTemplateConfig;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\CustomerNameGenerationInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Sending company related emails.
 */
class Sender
{
    /**
     * Email template for identity.
     */
    private $xmlPathRegisterEmailIdentity = 'customer/create_account/email_identity';

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var \Magento\Company\Model\Email\Transporter
     */
    private $transporter;

    /**
     * @var \Magento\Customer\Api\CustomerNameGenerationInterface
     */
    private $customerViewHelper;

    /**
     * @var \Magento\Company\Model\Email\CustomerData
     */
    private $customerData;

    /**
     * @var \Magento\Company\Model\Config\EmailTemplate
     */
    private $emailTemplateConfig;

    /**
     * @var \Magento\Company\Api\CompanyRepositoryInterface
     */
    private $companyRepository;

    /**
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface $scopeConfig
     * @param Transporter $transporter
     * @param CustomerNameGenerationInterface $customerViewHelper
     * @param CustomerData $customerData
     * @param EmailTemplateConfig $emailTemplateConfig
     * @param CompanyRepositoryInterface $companyRepository
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        Transporter $transporter,
        CustomerNameGenerationInterface $customerViewHelper,
        CustomerData $customerData,
        EmailTemplateConfig $emailTemplateConfig,
        CompanyRepositoryInterface $companyRepository
    ) {
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->transporter = $transporter;
        $this->customerViewHelper = $customerViewHelper;
        $this->customerData = $customerData;
        $this->emailTemplateConfig = $emailTemplateConfig;
        $this->companyRepository = $companyRepository;
    }

    /**
     * Send email to customer with assign message.
     *
     * @param CustomerInterface $customer
     * @param int $companyId
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function sendAssignSuperUserNotificationEmail(CustomerInterface $customer, $companyId)
    {
        $recipients = [];
        $storeId = $customer->getStoreId();
        if (!$storeId) {
            $storeId = $this->getWebsiteStoreId($customer);
        }

        $company = $this->companyRepository->get((int)$companyId);
        $salesRepData = $this->customerData->getDataObjectSalesRepresentative(
            $companyId,
            $company->getSalesRepresentativeId()
        );
        $recipients[] = $salesRepData->getEmail();

        $customerEmailData = $this->customerData->getDataObjectByCustomer($customer, $companyId);
        $recipients[] = $customerEmailData->getEmail();

        if (count($recipients)) {
            foreach ($recipients as $recipientEmail) {
                if (null !== $recipientEmail) {
                    $this->sendEmailTemplate(
                        $recipientEmail,
                        $this->customerViewHelper->getCustomerName($customer),
                        $this->emailTemplateConfig->getCustomerAssignSuperUserTemplateId(
                            ScopeInterface::SCOPE_STORE,
                            $storeId
                        ),
                        $this->xmlPathRegisterEmailIdentity,
                        ['customer' => $customerEmailData],
                        $storeId
                    );
                }
            }
        }
        return $this;
    }

    /**
     * Send email to customer with remove message.
     *
     * @param CustomerInterface $customer
     * @param int $companyId
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function sendRemoveSuperUserNotificationEmail(CustomerInterface $customer, $companyId)
    {
        $storeId = $customer->getStoreId();
        if (!$storeId) {
            $storeId = $this->getWebsiteStoreId($customer);
        }
        $customerEmailData = $this->customerData->getDataObjectByCustomer($customer, $companyId);
        if ($customerEmailData !== null) {
            $this->sendEmailTemplate(
                $customer->getEmail(),
                $this->customerViewHelper->getCustomerName($customer),
                $this->emailTemplateConfig->getCustomerRemoveSuperUserTemplateId(
                    ScopeInterface::SCOPE_STORE,
                    $storeId
                ),
                $this->xmlPathRegisterEmailIdentity,
                ['customer' => $customerEmailData],
                $storeId
            );
        }
        return $this;
    }

    /**
     * Send email to customer with inactivate message.
     *
     * @param CustomerInterface $customer
     * @param int $companyId
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function sendInactivateSuperUserNotificationEmail(CustomerInterface $customer, $companyId)
    {
        $storeId = $customer->getStoreId();
        if (!$storeId) {
            $storeId = $this->getWebsiteStoreId($customer);
        }
        $customerEmailData = $this->customerData->getDataObjectByCustomer($customer, $companyId);
        if ($customerEmailData !== null) {
            $this->sendEmailTemplate(
                $customer->getEmail(),
                $this->customerViewHelper->getCustomerName($customer),
                $this->emailTemplateConfig->getCustomerInactivateSuperUserTemplateId(
                    ScopeInterface::SCOPE_STORE,
                    $storeId
                ),
                $this->xmlPathRegisterEmailIdentity,
                ['customer' => $customerEmailData],
                $storeId
            );
        }
        return $this;
    }

    /**
     * Get either first store ID from a set website or the provided as default.
     *
     * @param CustomerInterface $customer
     * @return int
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getWebsiteStoreId(CustomerInterface $customer)
    {
        $defaultStoreId = \Magento\Store\Model\Store::DEFAULT_STORE_ID;
        if ($customer->getWebsiteId() != 0) {
            $storeIds = $this->storeManager->getWebsite($customer->getWebsiteId())->getStoreIds();
            reset($storeIds);
            $defaultStoreId = current($storeIds);
        }
        return $defaultStoreId;
    }

    /**
     * Send email to sales representative.
     *
     * @param int $companyId
     * @param int $salesRepresentativeId [optional]
     * @return $this
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function sendSalesRepresentativeNotificationEmail($companyId, $salesRepresentativeId = 0)
    {
        $salesRepresentativeDataObject = $this->customerData
            ->getDataObjectSalesRepresentative($companyId, $salesRepresentativeId);
        if ($salesRepresentativeDataObject !== null) {
            $this->sendEmailTemplate(
                $salesRepresentativeDataObject->getEmail(),
                $salesRepresentativeDataObject->getName(),
                $this->emailTemplateConfig->getSalesRepresentativeUserTemplateId(),
                $this->xmlPathRegisterEmailIdentity,
                ['customer' => $salesRepresentativeDataObject],
                \Magento\Store\Model\Store::DEFAULT_STORE_ID
            );
        }

        return $this;
    }

    /**
     * Send email to customer after assign company to him.
     *
     * @param \Magento\Customer\Api\Data\CustomerInterface $customer
     * @param int $companyId
     * @return $this
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function sendCustomerCompanyAssignNotificationEmail(
        \Magento\Customer\Api\Data\CustomerInterface $customer,
        $companyId
    ) {
        $customerName = $this->customerViewHelper->getCustomerName($customer);
        $companySuperUser = $this->customerData->getDataObjectSuperUser($companyId);
        $mergedCustomerData = $this->customerData->getDataObjectByCustomer($customer, $companyId);

        if ($companySuperUser && $mergedCustomerData) {
            $sender = [
                'name' => $companySuperUser->getName(),
                'email' => $companySuperUser->getEmail()
            ];

            $mergedCustomerData->setData('companyAdminEmail', $companySuperUser->getEmail());
            $this->sendEmailTemplate(
                $customer->getEmail(),
                $customerName,
                $this->emailTemplateConfig->getCompanyCustomerAssignUserTemplateId(
                    ScopeInterface::SCOPE_STORE,
                    $customer->getStoreId()
                ),
                $sender,
                ['customer' => $mergedCustomerData],
                $customer->getStoreId()
            );
        }

        return $this;
    }

    /**
     * Notify admin about new company.
     *
     * @param CustomerInterface $customer
     * @param string $companyName
     * @param string $companyUrl
     * @return $this
     */
    public function sendAdminNotificationEmail(CustomerInterface $customer, $companyName, $companyUrl)
    {
        $toCode = $this->emailTemplateConfig->getCompanyCreateRecipient(ScopeInterface::SCOPE_STORE);
        $toEmail = $this->scopeConfig->getValue('trans_email/ident_' . $toCode . '/email', ScopeInterface::SCOPE_STORE);
        $toName = $this->scopeConfig->getValue('trans_email/ident_' . $toCode . '/name', ScopeInterface::SCOPE_STORE);

        $copyTo = $this->emailTemplateConfig->getCompanyCreateCopyTo(ScopeInterface::SCOPE_STORE);
        $copyMethod = $this->emailTemplateConfig->getCompanyCreateCopyMethod(ScopeInterface::SCOPE_STORE);
        $storeId = \Magento\Store\Model\Store::DEFAULT_STORE_ID;

        $sendTo = [];
        if ($copyTo && $copyMethod == 'copy') {
            $sendTo = explode(',', $copyTo);
        }
        array_unshift($sendTo, $toEmail);

        foreach ($sendTo as $recipient) {
            $this->sendEmailTemplate(
                $recipient,
                $toName,
                $this->emailTemplateConfig->getCompanyCreateNotifyAdminTemplateId(),
                [
                    'email' => $customer->getEmail(),
                    'name' => $this->customerViewHelper->getCustomerName($customer)
                ],
                [
                    'customer' => $customer->getFirstname(),
                    'company' => $companyName,
                    'admin' => $toName,
                    'company_url' => $companyUrl
                ],
                $storeId,
                ($copyTo && $copyMethod == 'bcc') ? explode(',', $copyTo) : []
            );
        }

        return $this;
    }

    /**
     * Notify company admin of company status change.
     *
     * @param CustomerInterface $customer
     * @param int $companyId
     * @param string $templatePath
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function sendCompanyStatusChangeNotificationEmail(CustomerInterface $customer, $companyId, $templatePath)
    {
        $storeId = $customer->getStoreId();
        if (!$storeId) {
            $storeId = $this->getWebsiteStoreId($customer);
        }

        $copyTo = $this->emailTemplateConfig->getCompanyStatusChangeCopyTo(ScopeInterface::SCOPE_STORE);
        $copyMethod = $this->emailTemplateConfig->getCompanyStatusChangeCopyMethod(ScopeInterface::SCOPE_STORE);

        $sendTo = [];
        if ($copyTo && $copyMethod == 'copy') {
            $sendTo = explode(',', $copyTo);
        }
        array_unshift($sendTo, $customer->getEmail());

        $customerEmailData = $this->customerData->getDataObjectByCustomer($customer, $companyId);
        if ($customerEmailData !== null) {
            foreach ($sendTo as $recipient) {
                $this->sendEmailTemplate(
                    $recipient,
                    $this->customerViewHelper->getCustomerName($customer),
                    $this->scopeConfig->getValue($templatePath, ScopeInterface::SCOPE_STORE, $storeId),
                    $this->xmlPathRegisterEmailIdentity,
                    ['customer' => $customerEmailData],
                    $storeId,
                    ($copyTo && $copyMethod == 'bcc') ? explode(',', $copyTo) : []
                );
            }
        }
        return $this;
    }

    /**
     * Send email to customer with status update message.
     *
     * @param CustomerInterface $customer
     * @param int $status
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function sendUserStatusChangeNotificationEmail(CustomerInterface $customer, $status)
    {
        $storeId = $customer->getStoreId();
        if (!$storeId) {
            $storeId = $this->getWebsiteStoreId($customer);
        }
        $templateId = $status
            ? $this->emailTemplateConfig->getActivateCustomerTemplateId(ScopeInterface::SCOPE_STORE, $storeId)
            : $this->emailTemplateConfig->getInactivateCustomerTemplateId(ScopeInterface::SCOPE_STORE, $storeId);
        $customerEmailData = $this->customerData->getDataObjectByCustomer($customer);
        if ($customerEmailData !== null) {
            $this->sendEmailTemplate(
                $customer->getEmail(),
                $this->customerViewHelper->getCustomerName($customer),
                $templateId,
                $this->xmlPathRegisterEmailIdentity,
                ['customer' => $customerEmailData],
                $storeId
            );
        }
        return $this;
    }

    /**
     * Send corresponding email template.
     *
     * @param string $customerEmail
     * @param string $customerName
     * @param string $templateId
     * @param string|array $sender configuration path of email identity
     * @param array $templateParams [optional]
     * @param int|null $storeId [optional]
     * @param array $bcc [optional]
     * @return void
     */
    private function sendEmailTemplate(
        $customerEmail,
        $customerName,
        $templateId,
        $sender,
        array $templateParams = [],
        $storeId = null,
        array $bcc = []
    ) {
        $from = $sender;
        if (is_string($sender)) {
            $from = $this->scopeConfig->getValue($sender, ScopeInterface::SCOPE_STORE, $storeId);
        }
        $this->transporter->sendMessage(
            $customerEmail,
            $customerName,
            $from,
            $templateId,
            $templateParams,
            $storeId,
            $bcc
        );
    }
}
