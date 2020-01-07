<?php
namespace Magento\Company\Controller;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\NotFoundException;

/**
 * Class AbstractAction.
 *
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 */
abstract class AbstractAction extends \Magento\Framework\App\Action\Action
{
    /**
     * Authorization level of a company session.
     */
    const COMPANY_RESOURCE = 'Magento_Company::index';

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Magento\Company\Model\CompanyContext
     */
    protected $companyContext;

    /**
     * AbstractAction constructor.
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Company\Model\CompanyContext $companyContext
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Company\Model\CompanyContext $companyContext,
        \Psr\Log\LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->logger = $logger;
        $this->companyContext = $companyContext;
    }

    /**
     * Authenticate customer.
     *
     * @param RequestInterface $request
     * @return \Magento\Framework\App\ResponseInterface
     * @throws NotFoundException
     */
    public function dispatch(RequestInterface $request)
    {
        if (!$this->companyContext->isModuleActive()) {
            throw new NotFoundException(__('Page not found.'));
        }

        if (!$this->companyContext->isCustomerLoggedIn()) {
            $this->_actionFlag->set('', 'no-dispatch', true);
            return $this->_redirect('customer/account/login');
        }

        if (!$this->isAllowed()) {
            $this->_actionFlag->set('', 'no-dispatch', true);

            if ($this->companyContext->isCurrentUserCompanyUser()) {
                return $this->_redirect('company/accessdenied');
            }

            return $this->_redirect('noroute');
        }

        return parent::dispatch($request);
    }

    /**
     * @return bool
     */
    protected function isAllowed()
    {
        return $this->companyContext->isResourceAllowed(static::COMPANY_RESOURCE);
    }

    /**
     * Returns JSON error.
     *
     * @param string $message
     * @param array $payload
     * @return \Magento\Framework\Controller\Result\Json
     * @throws \InvalidArgumentException
     */
    protected function jsonError($message, array $payload = [])
    {
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON);
        $resultJson->setData(
            [
                'status' => 'error',
                'message' => $message,
                'payload' => $payload
            ]
        );

        return $resultJson;
    }

    /**
     * Returns JSON success.
     *
     * @param array $payload
     * @param string $message
     * @return \Magento\Framework\Controller\Result\Json
     * @throws \InvalidArgumentException
     */
    protected function jsonSuccess(array $payload, $message = '')
    {
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON);
        $resultJson->setData(
            [
                'status' => 'ok',
                'message' => $message,
                'data' => $payload
            ]
        );

        return $resultJson;
    }

    /**
     * Handle error message.
     *
     * @param string|null $errorMessage
     * @return \Magento\Framework\Controller\Result\Json
     */
    protected function handleJsonError($errorMessage = null)
    {
        $errorMessage = $errorMessage ?: __('Something went wrong.');
        $this->messageManager->addErrorMessage($errorMessage);

        return $this->jsonError($errorMessage);
    }

    /**
     * Handle success message.
     *
     * @param string $successMessage
     * @param array $payload
     * @return \Magento\Framework\Controller\Result\Json
     */
    protected function handleJsonSuccess(string $successMessage, array $payload = [])
    {
        $this->messageManager->addSuccessMessage($successMessage);

        return $this->jsonSuccess($payload, $successMessage);
    }
}
