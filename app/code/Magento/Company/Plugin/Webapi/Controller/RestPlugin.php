<?php

namespace Magento\Company\Plugin\Webapi\Controller;

use Magento\Framework\Exception\AuthorizationException;
use Magento\Framework\App\RequestInterface;
use Magento\Webapi\Controller\Rest;

/**
 * Class RestPlugin
 */
class RestPlugin
{
    /**
     * @var \Magento\Company\Plugin\Webapi\Controller\CustomerResolver
     */
    protected $customerResolver;

    /**
     * @var \Magento\Company\Model\Customer\PermissionInterface
     */
    protected $permission;

    /**
     * @var \Magento\Framework\Webapi\ErrorProcessor
     */
    private $errorProcessor;

    /**
     * @var \Magento\Framework\Webapi\Rest\Response
     */
    private $response;

    /**
     * @var \Magento\Customer\Controller\Account\Logout
     */
    private $logoutAction;

    /**
     * @param \Magento\Company\Plugin\Webapi\Controller\CustomerResolver $customerResolver
     * @param \Magento\Company\Model\Customer\PermissionInterface $permission
     * @param \Magento\Framework\Webapi\ErrorProcessor $errorProcessor
     * @param \Magento\Framework\Webapi\Rest\Response $response
     * @param \Magento\Customer\Controller\Account\Logout $logoutAction
     */
    public function __construct(
        \Magento\Company\Plugin\Webapi\Controller\CustomerResolver $customerResolver,
        \Magento\Company\Model\Customer\PermissionInterface $permission,
        \Magento\Framework\Webapi\ErrorProcessor $errorProcessor,
        \Magento\Framework\Webapi\Rest\Response $response,
        \Magento\Customer\Controller\Account\Logout $logoutAction
    ) {
        $this->customerResolver = $customerResolver;
        $this->permission = $permission;
        $this->errorProcessor = $errorProcessor;
        $this->response = $response;
        $this->logoutAction = $logoutAction;
    }

    /**
     * Around dispatch plugin.
     *
     * @param Rest $subject
     * @param \Closure $proceed
     * @param RequestInterface $request
     * @return \Magento\Framework\Controller\ResultInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundDispatch(Rest $subject, \Closure $proceed, RequestInterface $request)
    {
        if ($request->isPost()) {
            $customer = $this->customerResolver->getCustomer();
            if ($customer && !$this->permission->isLoginAllowed($customer)) {
                $this->logoutAction->execute();
                return $this->getLogoutResult();
            }
        }

        return $proceed($request);
    }

    /**
     * Get logout result.
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    private function getLogoutResult()
    {
        $webapiException = $this->errorProcessor->maskException(
            new AuthorizationException(__('The consumer isn\'t authorized to access resource.'))
        );

        return $this->response->setException($webapiException);
    }
}
