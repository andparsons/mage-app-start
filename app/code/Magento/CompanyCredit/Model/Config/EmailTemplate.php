<?php
namespace Magento\CompanyCredit\Model\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\CompanyCredit\Model\HistoryInterface;

/**
 * Class that retrieves config settings for company credit email templates.
 */
class EmailTemplate
{
    /**
     * @var string
     */
    private $creditChangeCopyTo = 'company/email/company_credit_change_copy';

    /**
     * @var string
     */
    private $companyCreditChangeSender = 'company/email/company_credit_change';

    /**
     * @var string
     */
    private $companyCreditCreateCopyMethod = 'company/email/company_credit_copy_method';

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Company\Api\CompanyRepositoryInterface
     */
    private $companyRepository;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * Email templates config.
     *
     * @var array
     */
    private $emailTemplatesConfig = [
        HistoryInterface::TYPE_ALLOCATED => 'company/email/credit_allocated_email_template',
        HistoryInterface::TYPE_UPDATED => 'company/email/credit_updated_email_template',
        HistoryInterface::TYPE_REIMBURSED => 'company/email/credit_reimbursed_email_template',
        HistoryInterface::TYPE_REFUNDED => 'company/email/credit_refunded_email_template',
        HistoryInterface::TYPE_REVERTED => 'company/email/credit_reverted_email_template',
    ];

    /**
     * EmailTemplate class constructor.
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Company\Api\CompanyRepositoryInterface $companyRepository
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Company\Api\CompanyRepositoryInterface $companyRepository,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->companyRepository = $companyRepository;
        $this->logger = $logger;
    }

     /**
      * Get company credit change copyTo value.
      *
      * @param string $scopeType
      * @param null|string $scopeCode
      * @return string
      */
    public function getCreditChangeCopyTo(
        $scopeType = ScopeInterface::SCOPE_STORE,
        $scopeCode = null
    ) {
        return $this->scopeConfig->getValue($this->creditChangeCopyTo, $scopeType, $scopeCode);
    }

    /**
     * Get company credit change recipient.
     *
     * @param int $storeId
     * @return string
     */
    public function getSenderByStoreId($storeId)
    {
        return $this->scopeConfig->getValue(
            $this->companyCreditChangeSender,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get company credit create copyTo method.
     *
     * @param string $scopeType
     * @param null|string $scopeCode
     * @return string
     */
    public function getCreditCreateCopyMethod(
        $scopeType = ScopeInterface::SCOPE_STORE,
        $scopeCode = null
    ) {
        return $this->scopeConfig->getValue($this->companyCreditCreateCopyMethod, $scopeType, $scopeCode);
    }

    /**
     * Get either first store ID from a set website or the provided as default.
     *
     * @param \Magento\Customer\Api\Data\CustomerInterface $customer
     * @param mixed $defaultStoreId
     * @return int
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getDefaultStoreId(\Magento\Customer\Api\Data\CustomerInterface $customer, $defaultStoreId = null)
    {
        if ($customer->getWebsiteId() != 0 && empty($defaultStoreId)) {
            $storeIds = $this->storeManager->getWebsite($customer->getWebsiteId())->getStoreIds();
            reset($storeIds);
            $defaultStoreId = current($storeIds);
        }
        return $defaultStoreId;
    }

    /**
     * Get template id.
     *
     * @param int $historyStatus
     * @param int $storeId
     * @return string
     */
    public function getTemplateId($historyStatus, $storeId)
    {
        $templatePath = $this->getEmailTemplate($historyStatus);
        $templateId = '';
        if ($templatePath) {
            $templateId = $this->scopeConfig->getValue($templatePath, ScopeInterface::SCOPE_STORE, $storeId);
        }
        return $templateId;
    }

    /**
     * Get email template.
     *
     * @param int $historyStatus
     * @return string|null
     */
    private function getEmailTemplate($historyStatus)
    {
        return array_key_exists($historyStatus, $this->emailTemplatesConfig)
            ? $this->emailTemplatesConfig[$historyStatus] : null;
    }

    /**
     * Check if notification can be sent to the company admin.
     *
     * @param \Magento\Customer\Api\Data\CustomerInterface $customer
     * @return bool
     */
    public function canSendNotification(\Magento\Customer\Api\Data\CustomerInterface $customer)
    {
        $canSend = false;
        $companyId = $customer->getExtensionAttributes()->getCompanyAttributes()->getCompanyId();
        if ($companyId) {
            try {
                $company = $this->companyRepository->get($companyId);
                if ($company->getStatus() == \Magento\Company\Api\Data\CompanyInterface::STATUS_APPROVED) {
                    $canSend = true;
                }
            } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                $this->logger->critical($e->getMessage());
            }
        }
        return $canSend;
    }
}
