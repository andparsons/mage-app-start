<?php
namespace Magento\Company\Block\Company\Management;

use Magento\Framework\View\Element\Template\Context;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Company\Api\CompanyManagementInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Company management info.
 *
 * @api
 * @since 100.0.0
 */
class Info extends \Magento\Framework\View\Element\Template
{
    /**
     * @var UserContextInterface
     */
    private $userContext;

    /**
     * @var CompanyManagementInterface
     */
    private $companyManagement;

    /**
     * @var string
     */
    private $xmlPathAllowRegister = 'company/general/allow_company_registration';

    /**
     * @var \Magento\Company\Api\AuthorizationInterface
     */
    private $authorization;

    /**
     * @param Context $context
     * @param UserContextInterface $userContext
     * @param CompanyManagementInterface $companyManagement
     * @param \Magento\Company\Api\AuthorizationInterface $authorization
     * @param array $data [optional]
     */
    public function __construct(
        Context $context,
        UserContextInterface $userContext,
        CompanyManagementInterface $companyManagement,
        \Magento\Company\Api\AuthorizationInterface $authorization,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->userContext = $userContext;
        $this->companyManagement = $companyManagement;
        $this->authorization = $authorization;
    }

    /**
     * Checks if user edit is allowed.
     *
     * @return bool
     */
    public function isUserEditAllowed()
    {
        return $this->authorization->isAllowed('Magento_Company::users_edit');
    }

    /**
     * Checks if roles edit is allowed.
     *
     * @return bool
     */
    public function isRoleEditAllowed()
    {
        return $this->authorization->isAllowed('Magento_Company::roles_edit');
    }

    /**
     * Has current customer company.
     *
     * @return bool
     */
    public function hasCustomerCompany()
    {
        $hasCompany = false;
        $customerId = $this->userContext->getUserId();
        if ($customerId) {
            $company = $this->companyManagement->getByCustomerId($customerId);
            if ($company) {
                $hasCompany = true;
            }
        }

        return $hasCompany;
    }

    /**
     * Get create new company url.
     *
     * @return string
     */
    public function getCreateCompanyAccountUrl()
    {
        return $this->getUrl('company/account/create');
    }

    /**
     * Is company registration allowed.
     *
     * @param string $scopeType
     * @param string|null $scopeCode
     * @return bool
     */
    public function isAllowedRegister($scopeType = ScopeInterface::SCOPE_WEBSITE, $scopeCode = null)
    {
        return $this->_scopeConfig->isSetFlag($this->xmlPathAllowRegister, $scopeType, $scopeCode);
    }
}
