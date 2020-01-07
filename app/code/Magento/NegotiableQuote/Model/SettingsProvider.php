<?php

namespace Magento\NegotiableQuote\Model;

/**
 * Class RestrictionSettingsProvider
 */
class SettingsProvider
{
    /**
     * Negotiable quote module config
     *
     * @var \Magento\NegotiableQuote\Model\Config
     */
    private $moduleConfig;

    /**
     * Customer url provider
     *
     * @var \Magento\Customer\Model\Url
     */
    private $customerUrl;

    /**
     * Json factory
     *
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var \Magento\Authorization\Model\UserContextInterface
     */
    private $userContext;

    /**
     * @var \Magento\Company\Model\CompanyUserPermission
     */
    private $companyUserPermission;

    /**
     * Construct
     *
     * @param \Magento\NegotiableQuote\Model\Config $moduleConfig
     * @param \Magento\Customer\Model\Url $customerUrl
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Authorization\Model\UserContextInterface $userContext
     * @param \Magento\Company\Model\CompanyUserPermission $companyUserPermission
     */
    public function __construct(
        \Magento\NegotiableQuote\Model\Config $moduleConfig,
        \Magento\Customer\Model\Url $customerUrl,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Authorization\Model\UserContextInterface $userContext,
        \Magento\Company\Model\CompanyUserPermission $companyUserPermission
    ) {
        $this->moduleConfig = $moduleConfig;
        $this->customerUrl = $customerUrl;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->userContext = $userContext;
        $this->companyUserPermission = $companyUserPermission;
    }

    /**
     * In negotiable quote module enabled
     *
     * @return bool
     */
    public function isModuleEnabled()
    {
        return $this->moduleConfig->isActive();
    }

    /**
     * Get customer login url
     *
     * @return string
     */
    public function getCustomerLoginUrl()
    {
        return $this->customerUrl->getLoginUrl();
    }

    /**
     * Return JSON success
     *
     * @param array $data
     * @param string $message
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function retrieveJsonSuccess(array $data, $message = '')
    {
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();
        $resultJson->setData([
            'status' => 'ok',
            'message' => $message,
            'data' => $data
        ]);

        return $resultJson;
    }

    /**
     * Returns JSON error
     *
     * @param string $message
     * @param string $redirectUrl
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function retrieveJsonError($message = '', $redirectUrl = '')
    {
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();
        $resultJson->setData([
            'status' => 'error',
            'message' => $message,
            'data' => ['url' => $redirectUrl]
        ]);

        return $resultJson;
    }

    /**
     * Get current user id
     *
     * @return int|null
     */
    public function getCurrentUserId()
    {
        return $this->userContext->getUserId();
    }

    /**
     * Get current user type
     *
     * @return int|null
     */
    public function getCurrentUserType()
    {
        return $this->userContext->getUserType();
    }

    /**
     * Is current user company user.
     *
     * @return bool
     */
    public function isCurrentUserCompanyUser()
    {
        return $this->companyUserPermission->isCurrentUserCompanyUser();
    }
}
