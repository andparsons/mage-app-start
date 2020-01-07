<?php

namespace Magento\CompanyCredit\Controller;

/**
 * Class AbstractAction.
 */
abstract class AbstractAction extends \Magento\Framework\App\Action\Action
{
    /**
     * Authorization level of a company credit.
     */
    const COMPANY_CREDIT_RESOURCE = 'Magento_Company::credit_history';

    /**
     * @var \Magento\Company\Api\StatusServiceInterface
     */
    protected $moduleConfig;

    /**
     * @var \Magento\Authorization\Model\UserContextInterface
     */
    protected $userContext;

    /**
     * @var \Magento\CompanyCredit\Model\PaymentMethodStatus
     */
    protected $paymentMethodStatus;

    /**
     * @var \Magento\Company\Api\AuthorizationInterface
     */
    private $authorization;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Magento\Company\Model\CompanyUserPermission
     */
    protected $companyUserPermission;

    /**
     * AbstractAction constructor.
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Company\Api\StatusServiceInterface $moduleConfig
     * @param \Magento\Authorization\Model\UserContextInterface $userContext
     * @param \Magento\CompanyCredit\Model\PaymentMethodStatus $paymentMethodStatus
     * @param \Magento\Company\Api\AuthorizationInterface $authorization
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Company\Model\CompanyUserPermission $companyUserPermission
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Company\Api\StatusServiceInterface $moduleConfig,
        \Magento\Authorization\Model\UserContextInterface $userContext,
        \Magento\CompanyCredit\Model\PaymentMethodStatus $paymentMethodStatus,
        \Magento\Company\Api\AuthorizationInterface $authorization,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Company\Model\CompanyUserPermission $companyUserPermission
    ) {
        parent::__construct($context);
        $this->moduleConfig = $moduleConfig;
        $this->userContext = $userContext;
        $this->paymentMethodStatus = $paymentMethodStatus;
        $this->logger = $logger;
        $this->authorization = $authorization;
        $this->companyUserPermission = $companyUserPermission;
    }

    /**
     * Authenticate customer.
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @return \Magento\Framework\App\ResponseInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function dispatch(\Magento\Framework\App\RequestInterface $request)
    {
        if (!$this->moduleConfig->isActive() || !$this->paymentMethodStatus->isEnabled()) {
            throw new \Magento\Framework\Exception\NotFoundException(__('Page not found.'));
        }

        $authenticated = (bool) $this->userContext->getUserId();

        if (!$authenticated) {
            $this->_actionFlag->set('', 'no-dispatch', true);
            return $this->_redirect('customer/account/login');
        }

        if (!$this->isAllowed()) {
            $this->_actionFlag->set('', 'no-dispatch', true);

            if ($this->companyUserPermission->isCurrentUserCompanyUser()) {
                return $this->_redirect('company/accessdenied');
            }

            return $this->_redirect('noroute');
        }

        return parent::dispatch($request);
    }

    /**
     * Is allowed.
     *
     * @return bool
     */
    protected function isAllowed()
    {
        return $this->authorization->isAllowed(static::COMPANY_CREDIT_RESOURCE);
    }
}
