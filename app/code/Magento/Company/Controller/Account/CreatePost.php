<?php
namespace Magento\Company\Controller\Account;

use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;

/**
 * Class CreatePost
 */
class CreatePost extends \Magento\Framework\App\Action\Action implements HttpPostActionInterface
{
    /**
     * @var string
     */
    private $formId = 'company_create';

    /**
     * @var \Magento\Framework\Api\DataObjectHelper
     */
    private $objectHelper;

    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator
     */
    private $formKeyValidator;

    /**
     * @var \Magento\Company\Model\Action\Validator\Captcha
     */
    private $captchaValidator;

    /**
     * @var \Magento\Authorization\Model\UserContextInterface
     */
    private $userContext;

    /**
     * @var \Magento\Customer\Api\AccountManagementInterface
     */
    private $customerAccountManagement;

    /**
     * @var \Magento\Customer\Api\Data\CustomerInterfaceFactory
     */
    private $customerDataFactory;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \Magento\Company\Model\Create\Session
     */
    private $companyCreateSession;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Authorization\Model\UserContextInterface $userContext
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Api\DataObjectHelper $objectHelper
     * @param \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
     * @param \Magento\Company\Model\Action\Validator\Captcha $captchaValidator
     * @param \Magento\Customer\Api\AccountManagementInterface $customerAccountManagement
     * @param \Magento\Customer\Api\Data\CustomerInterfaceFactory $customerDataFactory
     * @param \Magento\Company\Model\Create\Session $companyCreateSession
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Authorization\Model\UserContextInterface $userContext,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Api\DataObjectHelper $objectHelper,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        \Magento\Company\Model\Action\Validator\Captcha $captchaValidator,
        \Magento\Customer\Api\AccountManagementInterface $customerAccountManagement,
        \Magento\Customer\Api\Data\CustomerInterfaceFactory $customerDataFactory,
        \Magento\Company\Model\Create\Session $companyCreateSession
    ) {
        parent::__construct($context);
        $this->userContext = $userContext;
        $this->logger = $logger;
        $this->objectHelper = $objectHelper;
        $this->formKeyValidator = $formKeyValidator;
        $this->captchaValidator = $captchaValidator;
        $this->customerAccountManagement = $customerAccountManagement;
        $this->customerDataFactory = $customerDataFactory;
        $this->companyCreateSession = $companyCreateSession;
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        $request = $this->getRequest();
        $resultRedirect = $this->resultRedirectFactory->create()->setPath('*/account/create');

        if (!$this->validateRequest()) {
            return $resultRedirect;
        }

        try {
            $customer = $this->customerDataFactory->create();
            $customerData = $request->getParams();

            if (isset($customerData['company']['status'])) {
                unset($customerData['company']['status']);
            }

            $this->objectHelper->populateWithArray(
                $customer,
                $customerData,
                \Magento\Customer\Api\Data\CustomerInterface::class
            );
            $customer = $this->customerAccountManagement->createAccount($customer);
            $this->companyCreateSession->setCustomerId($customer->getId());
            $this->messageManager->addSuccessMessage(
                __('Thank you! We\'re reviewing your request and will contact you soon')
            );
            $resultRedirect->setPath('*/account/index');
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(
                __('An error occurred on the server. Your changes have not been saved.')
            );
            $this->logger->critical($e);
        }

        return $resultRedirect;
    }

    /**
     * Validate request
     *
     * @return bool
     */
    private function validateRequest()
    {
        if (!$this->getRequest()->isPost()) {
            return false;
        }

        if (!$this->formKeyValidator->validate($this->getRequest())) {
            return false;
        }

        if (!$this->captchaValidator->validate($this->formId, $this->getRequest())) {
            $this->messageManager->addErrorMessage(__('Incorrect CAPTCHA'));
            return false;
        }

        return true;
    }
}
