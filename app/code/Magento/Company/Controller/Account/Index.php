<?php
namespace Magento\Company\Controller\Account;

use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class Index
 */
class Index extends \Magento\Framework\App\Action\Action implements HttpGetActionInterface
{
    /**
     * @var \Magento\Company\Model\Create\Session
     */
    private $session;

    /**
     * @var \Magento\Company\Api\CompanyManagementInterface
     */
    private $companyManagement;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Company\Model\Create\Session $session
     * @param \Magento\Company\Api\CompanyManagementInterface $companyManagement
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Company\Model\Create\Session $session,
        \Magento\Company\Api\CompanyManagementInterface $companyManagement
    ) {
        parent::__construct($context);
        $this->session = $session;
        $this->companyManagement = $companyManagement;
    }

    /**
     * {@inheritdoc}
     * @throws \InvalidArgumentException
     */
    public function execute()
    {
        /** @var \Magento\Framework\View\Result\Page $result */
        $result = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $result->getConfig()->getTitle()->set(__('Create New Company'));

        $customerId = $this->session->getCustomerId();
        try {
            $this->companyManagement->getByCustomerId($customerId);
        } catch (NoSuchEntityException $e) {
            $result = $this->resultRedirectFactory->create()->setRefererUrl();
        }
        return $result;
    }
}
