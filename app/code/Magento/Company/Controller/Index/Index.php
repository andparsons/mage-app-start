<?php
namespace Magento\Company\Controller\Index;

use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;

/**
 * Class Index.
 */
class Index extends \Magento\Company\Controller\AbstractAction implements HttpGetActionInterface
{
    /**
     * Authorization level of a company session.
     */
    const COMPANY_RESOURCE = 'Magento_Company::users_view';

    /**
     * @var \Magento\Company\Model\CompanyContext
     */
    protected $companyContext;

    /**
     * @var \Magento\Company\Api\CompanyManagementInterface
     */
    private $companyManagement;

    /**
     * Index constructor.
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Company\Model\CompanyContext $companyContext
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Company\Api\CompanyManagementInterface $companyManagement
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Company\Model\CompanyContext $companyContext,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Company\Api\CompanyManagementInterface $companyManagement
    ) {
        parent::__construct($context, $companyContext, $logger);
        $this->companyContext = $companyContext;
        $this->companyManagement = $companyManagement;
    }

    /**
     * Company dashboard.
     *
     * @return void
     * @throws \RuntimeException
     */
    public function execute()
    {
        $this->_view->loadLayout();
        $this->_view->loadLayoutUpdates();
        $this->_view->getPage()->getConfig()->getTitle()->set(__('Company Structure'));
        $this->_view->renderLayout();
    }

    /**
     * @return bool
     */
    protected function isAllowed()
    {
        $company = $this->companyManagement->getByCustomerId($this->companyContext->getCustomerId());
        return !$company || parent::isAllowed();
    }
}
