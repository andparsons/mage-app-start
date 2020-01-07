<?php
namespace Magento\Company\Model\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Email templates config.
 *
 * Provides access to company email templates configuration.
 */
class EmailTemplate
{
    /**
     * @var string
     */
    private $customerAssignSuperUserTemplate = 'company/email/customer_assign_super_user_template';

    /**
     * @var string
     */
    private $customerInactivateSuperUserTemplate = 'company/email/customer_inactivate_super_user_template';

    /**
     * @var string
     */
    private $customerRemoveSuperUserTemplate = 'company/email/customer_remove_super_user_template';

    /**
     * @var string
     */
    private $salesRepresentativeUserTemplate = 'company/email/customer_sales_representative_template';

    /**
     * @var string
     */
    private $companyCustomerAssignUserTemplate = 'company/email/customer_company_customer_assign_template';

    /**
     * @var string
     */
    private $companyCreateNotifyAdminTemplate = 'company/email/company_notify_admin_template';

    /**
     * @var string
     */
    private $activateCustomerTemplate = 'company/email/customer_account_activated_template';

    /**
     * @var string
     */
    private $inactivateCustomerTemplate = 'company/email/customer_account_locked_template';

    /**
     * @var string
     */
    private $companyCreateRecipient = 'company/email/company_registration';

    /**
     * @var string
     */
    private $companyCreateCopyTo = 'company/email/company_registration_copy';

    /**
     * @var string
     */
    private $companyCreateCopyMethod = 'company/email/company_copy_method';

    /**
     * @var string
     */
    private $companyStatusChangeCopyTo = 'company/email/company_status_change_copy';

    /**
     * @var string
     */
    private $companyStatusChangeCopyMethod = 'company/email/company_status_copy_method';

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Get customer assign superuser template.
     *
     * @param string $scopeType
     * @param null|string $scopeCode
     * @return string
     */
    public function getCustomerAssignSuperUserTemplateId(
        $scopeType = ScopeInterface::SCOPE_STORE,
        $scopeCode = null
    ) {
        return $this->scopeConfig->getValue($this->customerAssignSuperUserTemplate, $scopeType, $scopeCode);
    }

    /**
     * Get customer inactivate superuser template.
     *
     * @param string $scopeType
     * @param null|string $scopeCode
     * @return string
     */
    public function getCustomerInactivateSuperUserTemplateId(
        $scopeType = ScopeInterface::SCOPE_STORE,
        $scopeCode = null
    ) {
        return $this->scopeConfig->getValue($this->customerInactivateSuperUserTemplate, $scopeType, $scopeCode);
    }

    /**
     * Get customer remove superuser template.
     *
     * @param string $scopeType
     * @param null|string $scopeCode
     * @return string
     */
    public function getCustomerRemoveSuperUserTemplateId(
        $scopeType = ScopeInterface::SCOPE_STORE,
        $scopeCode = null
    ) {
        return $this->scopeConfig->getValue($this->customerRemoveSuperUserTemplate, $scopeType, $scopeCode);
    }

    /**
     * Get sales representative user template.
     *
     * @param string $scopeType
     * @param null|string $scopeCode
     * @return string
     */
    public function getSalesRepresentativeUserTemplateId(
        $scopeType = ScopeInterface::SCOPE_STORE,
        $scopeCode = null
    ) {
        return $this->scopeConfig->getValue($this->salesRepresentativeUserTemplate, $scopeType, $scopeCode);
    }

    /**
     * Get company customer assign user template.
     *
     * @param string $scopeType
     * @param null|string $scopeCode
     * @return string
     */
    public function getCompanyCustomerAssignUserTemplateId(
        $scopeType = ScopeInterface::SCOPE_STORE,
        $scopeCode = null
    ) {
        return $this->scopeConfig->getValue($this->companyCustomerAssignUserTemplate, $scopeType, $scopeCode);
    }

    /**
     * Get company registration notify admin template.
     *
     * @param string $scopeType
     * @param null|string $scopeCode
     * @return string
     */
    public function getCompanyCreateNotifyAdminTemplateId(
        $scopeType = ScopeInterface::SCOPE_STORE,
        $scopeCode = null
    ) {
        return $this->scopeConfig->getValue($this->companyCreateNotifyAdminTemplate, $scopeType, $scopeCode);
    }

    /**
     * Get company registration recipient.
     *
     * @param string $scopeType
     * @param null|string $scopeCode
     * @return string
     */
    public function getCompanyCreateRecipient(
        $scopeType = ScopeInterface::SCOPE_STORE,
        $scopeCode = null
    ) {
        return $this->scopeConfig->getValue($this->companyCreateRecipient, $scopeType, $scopeCode);
    }

    /**
     * Get company registration copyTo value.
     *
     * @param string $scopeType
     * @param null|string $scopeCode
     * @return string
     */
    public function getCompanyCreateCopyTo(
        $scopeType = ScopeInterface::SCOPE_STORE,
        $scopeCode = null
    ) {
        return $this->scopeConfig->getValue($this->companyCreateCopyTo, $scopeType, $scopeCode);
    }

    /**
     * Get company registration copyTo method.
     *
     * @param string $scopeType
     * @param null|string $scopeCode
     * @return string
     */
    public function getCompanyCreateCopyMethod(
        $scopeType = ScopeInterface::SCOPE_STORE,
        $scopeCode = null
    ) {
        return $this->scopeConfig->getValue($this->companyCreateCopyMethod, $scopeType, $scopeCode);
    }

    /**
     * Get company registration copyTo value.
     *
     * @param string $scopeType
     * @param null|string $scopeCode
     * @return string
     */
    public function getCompanyStatusChangeCopyTo(
        $scopeType = ScopeInterface::SCOPE_STORE,
        $scopeCode = null
    ) {
        return $this->scopeConfig->getValue($this->companyStatusChangeCopyTo, $scopeType, $scopeCode);
    }

    /**
     * Get company registration copyTo method.
     *
     * @param string $scopeType
     * @param null|string $scopeCode
     * @return string
     */
    public function getCompanyStatusChangeCopyMethod(
        $scopeType = ScopeInterface::SCOPE_STORE,
        $scopeCode = null
    ) {
        return $this->scopeConfig->getValue($this->companyStatusChangeCopyMethod, $scopeType, $scopeCode);
    }

    /**
     * Get activate customer template.
     *
     * @param string $scopeType
     * @param null|string $scopeCode
     * @return string
     */
    public function getActivateCustomerTemplateId(
        $scopeType = ScopeInterface::SCOPE_STORE,
        $scopeCode = null
    ) {
        return $this->scopeConfig->getValue($this->activateCustomerTemplate, $scopeType, $scopeCode);
    }

    /**
     * Get inactivate customer template.
     *
     * @param string $scopeType
     * @param null|string $scopeCode
     * @return string
     */
    public function getInactivateCustomerTemplateId(
        $scopeType = ScopeInterface::SCOPE_STORE,
        $scopeCode = null
    ) {
        return $this->scopeConfig->getValue($this->inactivateCustomerTemplate, $scopeType, $scopeCode);
    }
}
