<?php

namespace Magento\Company\Test\Unit\Controller\Profile;

/**
 * Unit test for Magento\Company\Controller\Profile\EditPost class.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class EditPostTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Company\Api\CompanyManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $companyManagement;

    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $formKeyValidator;

    /**
     * @var \Magento\Company\Model\CompanyProfile|\PHPUnit_Framework_MockObject_MockObject
     */
    private $companyProfile;

    /**
     * @var \Magento\Company\Api\CompanyRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $companyRepository;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $request;

    /**
     * @var \Magento\Framework\Controller\Result\RedirectFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultRedirectFactory;

    /**
     * @var \Magento\Company\Model\CompanyContext|\PHPUnit_Framework_MockObject_MockObject
     */
    private $companyContext;

    /**
     * @var \Magento\Framework\Message\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $messageManager;

    /**
     * @var \Magento\Company\Controller\Profile\EditPost
     */
    private $editPost;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->companyManagement = $this->getMockBuilder(\Magento\Company\Api\CompanyManagementInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->formKeyValidator = $this->getMockBuilder(\Magento\Framework\Data\Form\FormKey\Validator::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->companyProfile = $this->getMockBuilder(\Magento\Company\Model\CompanyProfile::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->companyRepository = $this->getMockBuilder(\Magento\Company\Api\CompanyRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->request = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['isPost', 'getParams'])
            ->getMockForAbstractClass();
        $this->resultRedirectFactory = $this
            ->getMockBuilder(\Magento\Framework\Controller\Result\RedirectFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->companyContext = $this->getMockBuilder(\Magento\Company\Model\CompanyContext::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->messageManager = $this->getMockBuilder(\Magento\Framework\Message\ManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->editPost = $objectManager->getObject(
            \Magento\Company\Controller\Profile\EditPost::class,
            [
                'companyManagement' => $this->companyManagement,
                'formKeyValidator' => $this->formKeyValidator,
                'companyProfile' => $this->companyProfile,
                'companyRepository' => $this->companyRepository,
                '_request' => $this->request,
                'resultRedirectFactory' => $this->resultRedirectFactory,
                'companyContext' => $this->companyContext,
                'messageManager' => $this->messageManager,
            ]
        );
    }

    /**
     * Test execute method.
     *
     * @return void
     */
    public function testExecute()
    {
        $customerId = 1;
        $resultRedirect = $this->getMockBuilder(\Magento\Framework\Controller\Result\Redirect::class)
            ->disableOriginalConstructor()
            ->getMock();
        $company = $this->getMockBuilder(\Magento\Company\Api\Data\CompanyInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->resultRedirectFactory->expects($this->once())->method('create')->willReturn($resultRedirect);
        $resultRedirect->expects($this->once())->method('setPath')->with('*/profile/')->willReturnSelf();
        $this->request->expects($this->once())->method('isPost')->willReturn(true);
        $this->formKeyValidator->expects($this->once())->method('validate')->with($this->request)->willReturn(true);
        $this->companyContext->expects($this->once())->method('getCustomerId')->willReturn($customerId);
        $this->companyManagement->expects($this->once())
            ->method('getByCustomerId')
            ->with($customerId)
            ->willReturn($company);
        $company->expects($this->once())->method('getId')->willReturn(1);
        $this->request->expects($this->once())->method('getParams')->willReturn([]);
        $this->companyProfile->expects($this->once())->method('populate')->with($company, []);
        $this->companyRepository->expects($this->once())->method('save')->with($company)->willReturnSelf();
        $this->messageManager->expects($this->once())
            ->method('addSuccess')
            ->with(__('The changes you made on the company profile have been successfully saved.'))
            ->willReturnSelf();

        $this->assertSame($resultRedirect, $this->editPost->execute());
    }

    /**
     * Test execute with invalid form key.
     *
     * @return void
     */
    public function testExecuteWithInvalidFormKey()
    {
        $resultRedirect = $this->getMockBuilder(\Magento\Framework\Controller\Result\Redirect::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultRedirectFactory->expects($this->once())->method('create')->willReturn($resultRedirect);
        $resultRedirect->expects($this->once())->method('setPath')->with('*/profile/')->willReturnSelf();
        $this->request->expects($this->once())->method('isPost')->willReturn(true);
        $this->formKeyValidator->expects($this->once())->method('validate')->with($this->request)->willReturn(false);

        $this->assertSame($resultRedirect, $this->editPost->execute());
    }

    /**
     * Test execute with Exception.
     *
     * @return void
     */
    public function testExecuteWithException()
    {
        $customerId = 1;
        $exception = new \Exception();
        $resultRedirect = $this->getMockBuilder(\Magento\Framework\Controller\Result\Redirect::class)
            ->disableOriginalConstructor()
            ->getMock();
        $company = $this->getMockBuilder(\Magento\Company\Api\Data\CompanyInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->resultRedirectFactory->expects($this->once())->method('create')->willReturn($resultRedirect);
        $resultRedirect->expects($this->atLeastOnce())
            ->method('setPath')
            ->withConsecutive(['*/profile/'], ['*/profile/edit'])
            ->willReturnSelf();
        $this->request->expects($this->once())->method('isPost')->willReturn(true);
        $this->formKeyValidator->expects($this->once())->method('validate')->with($this->request)->willReturn(true);
        $this->companyContext->expects($this->once())->method('getCustomerId')->willReturn($customerId);
        $this->companyManagement->expects($this->once())
            ->method('getByCustomerId')
            ->with($customerId)
            ->willReturn($company);
        $company->expects($this->once())->method('getId')->willReturn(1);
        $this->request->expects($this->once())->method('getParams')->willReturn([]);
        $this->companyProfile->expects($this->once())->method('populate')->with($company, []);
        $this->companyRepository->expects($this->once())
            ->method('save')
            ->with($company)
            ->willThrowException($exception);
        $this->messageManager->expects($this->once())
            ->method('addError')
            ->with(__('An error occurred on the server. Your changes have not been saved.'))
            ->willReturnSelf();

        $this->assertSame($resultRedirect, $this->editPost->execute());
    }

    /**
     * Test execute with LocalizedException.
     *
     * @return void
     */
    public function testExecuteWithLocalizedException()
    {
        $customerId = 1;
        $phrase = new \Magento\Framework\Phrase('exception');
        $localizedException = new \Magento\Framework\Exception\LocalizedException($phrase);
        $resultRedirect = $this->getMockBuilder(\Magento\Framework\Controller\Result\Redirect::class)
            ->disableOriginalConstructor()
            ->getMock();
        $company = $this->getMockBuilder(\Magento\Company\Api\Data\CompanyInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->resultRedirectFactory->expects($this->once())->method('create')->willReturn($resultRedirect);
        $resultRedirect->expects($this->atLeastOnce())
            ->method('setPath')
            ->withConsecutive(['*/profile/'], ['*/profile/edit'])
            ->willReturnSelf();
        $this->request->expects($this->once())->method('isPost')->willReturn(true);
        $this->formKeyValidator->expects($this->once())->method('validate')->with($this->request)->willReturn(true);
        $this->companyContext->expects($this->once())->method('getCustomerId')->willReturn($customerId);
        $this->companyManagement->expects($this->once())
            ->method('getByCustomerId')
            ->with($customerId)
            ->willReturn($company);
        $company->expects($this->once())->method('getId')->willReturn(1);
        $this->request->expects($this->once())->method('getParams')->willReturn([]);
        $this->companyProfile->expects($this->once())->method('populate')->with($company, []);
        $this->companyRepository->expects($this->once())
            ->method('save')
            ->with($company)
            ->willThrowException($localizedException);
        $this->messageManager->expects($this->once())
            ->method('addError')
            ->with(__('You must fill in all required fields before you can continue.'))
            ->willReturnSelf();

        $this->assertSame($resultRedirect, $this->editPost->execute());
    }
}
