<?php

namespace Magento\Company\Test\Unit\Controller\Account;

use \Magento\Customer\Api\AccountManagementInterface;

/**
 * Class CreatePostTest
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CreatePostTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Authorization\Model\UserContextInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $userContext;

    /**
     * @var \Psr\Log\LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $logger;

    /**
     * @var \Magento\Framework\Api\DataObjectHelper|\PHPUnit_Framework_MockObject_MockObject
     */
    private $objectHelper;

    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $formKeyValidator;

    /**
     * @var \Magento\Company\Model\Action\Validator\Captcha|\PHPUnit_Framework_MockObject_MockObject
     */
    private $captchaValidator;

    /**
     * @var \Magento\Customer\Api\AccountManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerAccountManagement;

    /**
     * @var \Magento\Customer\Api\Data\CustomerInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerDataFactory;

    /**
     * @var \Magento\Company\Model\Create\Session|\PHPUnit_Framework_MockObject_MockObject
     */
    private $companyCreateSession;

    /**
     * @var \Magento\Framework\App\Request\Http|\PHPUnit_Framework_MockObject_MockObject
     */
    private $request;

    /**
     * @var \Magento\Framework\Controller\Result\RedirectFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultRedirectFactory;

    /**
     * @var \Magento\Framework\Controller\Result\Redirect|\PHPUnit_Framework_MockObject_MockObject
     */
    private $redirect;

    /**
     * @var \Magento\Framework\Message\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $messageManager;

    /**
     * @var \Magento\Company\Controller\Account\CreatePost
     */
    private $createPost;

    /**
     * Set up
     */
    protected function setUp()
    {
        $this->userContext = $this->createMock(\Magento\Authorization\Model\UserContextInterface::class);
        $this->logger = $this->createMock(\Psr\Log\LoggerInterface::class);
        $this->objectHelper = $this->createMock(\Magento\Framework\Api\DataObjectHelper::class);
        $this->formKeyValidator = $this->createMock(\Magento\Framework\Data\Form\FormKey\Validator::class);
        $this->captchaValidator = $this->createMock(\Magento\Company\Model\Action\Validator\Captcha::class);
        $this->customerAccountManagement = $this->getMockBuilder(AccountManagementInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['createAccount'])
            ->getMockForAbstractClass();
        $customer = $this->createMock(\Magento\Customer\Api\Data\CustomerInterface::class);
        $this->customerAccountManagement->expects($this->any())->method('createAccount')->willReturn($customer);
        $this->customerDataFactory = $this->createPartialMock(
            \Magento\Customer\Api\Data\CustomerInterfaceFactory::class,
            ['create']
        );
        $this->customerDataFactory->expects($this->any())->method('create')->willReturn($customer);
        $this->companyCreateSession = $this->createMock(\Magento\Company\Model\Create\Session::class);
        $this->request = $this->createMock(\Magento\Framework\App\Request\Http::class);
        $this->request->expects($this->any())->method('getParams')->willReturn([]);
        $this->resultRedirectFactory = $this->createPartialMock(
            \Magento\Framework\Controller\Result\RedirectFactory::class,
            ['create']
        );
        $this->redirect = $this->createMock(\Magento\Framework\Controller\Result\Redirect::class);
        $this->redirect->expects($this->any())->method('setPath')->willReturnSelf();
        $this->resultRedirectFactory->expects($this->any())->method('create')->willReturn($this->redirect);
        $this->messageManager = $this->createMock(\Magento\Framework\Message\ManagerInterface::class);
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->createPost = $objectManager->getObject(
            \Magento\Company\Controller\Account\CreatePost::class,
            [
                '_request' => $this->request,
                'messageManager' => $this->messageManager,
                'resultRedirectFactory' => $this->resultRedirectFactory,
                'userContext' => $this->userContext,
                'logger' => $this->logger,
                'objectHelper' => $this->objectHelper,
                'formKeyValidator' => $this->formKeyValidator,
                'captchaValidator' => $this->captchaValidator,
                'customerAccountManagement' => $this->customerAccountManagement,
                'customerDataFactory' => $this->customerDataFactory,
                'companyCreateSession' => $this->companyCreateSession
            ]
        );
    }

    /**
     * Test execute
     *
     * @param bool $isPost
     * @param bool $isFormValid
     * @param bool $isCaptchaValid
     * @dataProvider dataProviderExecute
     */
    public function testExecute($isPost, $isFormValid, $isCaptchaValid)
    {
        $this->prepareReturnValues($isPost, $isFormValid, $isCaptchaValid);

        $this->assertInstanceOf(\Magento\Framework\Controller\Result\Redirect::class, $this->createPost->execute());
    }

    /**
     * Test execute with exception
     */
    public function testExecuteWithException()
    {
        $this->prepareReturnValues();
        $exception = new \Exception();
        $this->customerAccountManagement->expects($this->any())->method('createAccount')
            ->willThrowException($exception);
        $this->messageManager->expects($this->once())->method('addErrorMessage');
        $this->logger->expects($this->once())->method('critical');

        $this->assertInstanceOf(\Magento\Framework\Controller\Result\Redirect::class, $this->createPost->execute());
    }

    /**
     * Test execute with LocalizedException
     */
    public function testExecuteWithLocalizedException()
    {
        $this->prepareReturnValues();
        $phrase = new \Magento\Framework\Phrase(__('Exception'));
        $exception = new \Magento\Framework\Exception\LocalizedException($phrase);
        $this->customerAccountManagement->expects($this->any())->method('createAccount')
            ->willThrowException($exception);
        $this->messageManager->expects($this->once())->method('addErrorMessage');

        $this->assertInstanceOf(\Magento\Framework\Controller\Result\Redirect::class, $this->createPost->execute());
    }

    /**
     * DataProvider execute
     *
     * @return array
     */
    public function dataProviderExecute()
    {
        return [
            [false, false, false],
            [true, false, false],
            [false, true, false],
            [false, false, true],
            [true, true, false],
            [false, true, true],
            [true, true, true]
        ];
    }

    /**
     * Prepare return values
     *
     * @param bool $isPost
     * @param bool $isFormValid
     * @param bool $isCaptchaValid
     */
    private function prepareReturnValues($isPost = true, $isFormValid = true, $isCaptchaValid = true)
    {
        $this->request->expects($this->any())->method('isPost')->willReturn($isPost);
        $this->formKeyValidator->expects($this->any())->method('validate')->willReturn($isFormValid);
        $this->captchaValidator->expects($this->any())->method('validate')->willReturn($isCaptchaValid);
    }
}
