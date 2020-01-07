<?php

declare(strict_types=1);

namespace Magento\Company\Controller\Customer;

use Magento\Company\Api\CompanyUserManagerInterface;
use Magento\Company\Api\Data\CompanyCustomerInterface;
use Magento\Company\Api\Data\CompanyCustomerInterfaceFactory;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\Api\DataObjectHelper;

/**
 * Accept invitation to a company.
 */
class AcceptInvitation extends Action implements HttpGetActionInterface
{
    /**
     * @var DataObjectHelper
     */
    private $objectHelper;

    /**
     * @var CompanyUserManagerInterface
     */
    private $userManager;

    /**
     * @var CompanyCustomerInterfaceFactory
     */
    private $customerFactory;

    /**
     * @param Context $context
     * @param DataObjectHelper $dataObjectHelper
     * @param CompanyUserManagerInterface $userManager
     * @param CompanyCustomerInterfaceFactory $userFactory
     */
    public function __construct(
        Context $context,
        DataObjectHelper $dataObjectHelper,
        CompanyUserManagerInterface $userManager,
        CompanyCustomerInterfaceFactory $userFactory
    ) {
        parent::__construct($context);
        $this->objectHelper = $dataObjectHelper;
        $this->userManager = $userManager;
        $this->customerFactory = $userFactory;
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        /** @var HttpRequest $request */
        $request = $this->getRequest();
        $code = $request->get('code');
        $roleId = $request->get('role_id');
        if (empty($roleId)) {
            $roleId = null;
        }
        /** @var CompanyCustomerInterface $customer */
        $customer = $this->customerFactory->create();
        $this->objectHelper->populateWithArray(
            $customer,
            $request->get('customer'),
            CompanyCustomerInterface::class
        );

        try {
            $this->userManager->acceptInvitation($code, $customer, $roleId);
            $this->messageManager->addSuccessMessage(__('You have accepted the invitation to the company'));
        } catch (\Throwable $exception) {
            $this->messageManager->addErrorMessage(
                __('Error occurred when trying to accept the invitation. Please try again later.')
            );
        }

        $result = $this->resultRedirectFactory->create();
        $result->setPath('/');
        return $result;
    }
}
