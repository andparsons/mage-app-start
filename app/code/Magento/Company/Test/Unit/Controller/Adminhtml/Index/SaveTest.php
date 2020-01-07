<?php

namespace Magento\Company\Test\Unit\Controller\Adminhtml\Index;

use Magento\Company\Api\Data\CompanyInterface;

/**
 * Unit tests for adminhtml company save controller.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SaveTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Reflection\DataObjectProcessor|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dataObjectProcessor;

    /**
     * @var \Magento\Company\Model\CompanySuperUserGet|\PHPUnit_Framework_MockObject_MockObject
     */
    private $companySuperUserGet;

    /**
     * @var \Magento\Company\Api\Data\CompanyInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $companyDataFactory;

    /**
     * @var \Magento\Company\Api\CompanyRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $companyRepository;

    /**
     * @var \Magento\Framework\Api\DataObjectHelper|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dataObjectHelper;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $request;

    /**
     * @var \Magento\Framework\Event\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $eventManager;

    /**
     * @var \Magento\Framework\Message\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $messageManager;

    /**
     * @var \Magento\Framework\Controller\Result\RedirectFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultRedirectFactory;

    /**
     * @var \Magento\Backend\Model\Session|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $session;

    /**
     * @var \Magento\Company\Api\Data\CompanyInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $companyMock;

    /**
     * @var \Magento\Company\Controller\Adminhtml\Index\Save
     */
    private $save;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->dataObjectProcessor = $this->getMockBuilder(\Magento\Framework\Reflection\DataObjectProcessor::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->companySuperUserGet = $this->getMockBuilder(\Magento\Company\Model\CompanySuperUserGet::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->companyDataFactory = $this->getMockBuilder(\Magento\Company\Api\Data\CompanyInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->companyRepository = $this->getMockBuilder(\Magento\Company\Api\CompanyRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->dataObjectHelper = $this->getMockBuilder(\Magento\Framework\Api\DataObjectHelper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->request = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->eventManager = $this->getMockBuilder(\Magento\Framework\Event\ManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->messageManager = $this->getMockBuilder(\Magento\Framework\Message\ManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->resultRedirectFactory = $this->getMockBuilder(\Magento\Backend\Model\View\Result\RedirectFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->session = $this->getMockBuilder(\Magento\Backend\Model\Session::class)
            ->disableOriginalConstructor()
            ->setMethods(['setCompanyData'])
            ->getMock();
        $this->companyMock = $this->getMockBuilder(\Magento\Company\Api\Data\CompanyInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->save = $objectManagerHelper->getObject(
            \Magento\Company\Controller\Adminhtml\Index\Save::class,
            [
                'dataObjectProcessor' => $this->dataObjectProcessor,
                'superUser' => $this->companySuperUserGet,
                'companyDataFactory' => $this->companyDataFactory,
                'companyRepository' => $this->companyRepository,
                'dataObjectHelper' => $this->dataObjectHelper,
                '_request' => $this->request,
                '_eventManager' => $this->eventManager,
                'messageManager' => $this->messageManager,
                'resultRedirectFactory' => $this->resultRedirectFactory,
                '_session' => $this->session,
            ]
        );
    }

    /**
     * Test for execute method.
     *
     * @return void
     */
    public function testExecute()
    {
        $companyId = 1;
        $params = [
            [
                CompanyInterface::COMPANY_ID => $companyId,
                CompanyInterface::EMAIL => 'exampl@test.com',
                CompanyInterface::NAME => 'Example Company Name',
                CompanyInterface::REGION_ID => 2,
                CompanyInterface::COUNTRY_ID => 'US',
                CompanyInterface::REGION => 'Alabama',
            ]
        ];
        $this->request->expects($this->exactly(3))
            ->method('getParam')
            ->withConsecutive(['id'], ['id'], ['back'])
            ->willReturnOnConsecutiveCalls($companyId, $companyId, false);
        $this->request->expects($this->once())->method('getParams')->willReturn($params);
        $company = $this->getMockBuilder(\Magento\Company\Api\Data\CompanyInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->companyRepository->expects($this->once())->method('get')->with($companyId)->willReturn($company);
        $this->dataObjectHelper->expects($this->once())
            ->method('populateWithArray')
            ->with($company, $params[0], \Magento\Company\Api\Data\CompanyInterface::class)->willReturnSelf();
        $this->companyRepository->expects($this->once())->method('save')->with($company)->willReturn($company);
        $customerMock = $this->getMockBuilder(\Magento\Customer\Api\Data\CustomerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->companySuperUserGet->expects($this->once())->method('getUserForCompanyAdmin')->willReturn($customerMock);
        $this->eventManager->expects($this->once())
            ->method('dispatch')
            ->with('adminhtml_company_save_after', ['company' => $company, 'request' => $this->request]);
        $company->expects($this->once())->method('getCompanyName')->willReturn($params[0][CompanyInterface::NAME]);
        $this->messageManager->expects($this->once())
            ->method('addSuccessMessage')
            ->with(__('You have saved company %companyName.', ['companyName' => $params[0][CompanyInterface::NAME]]))
            ->willReturnSelf();
        $result = $this->getMockBuilder(\Magento\Framework\Controller\Result\Redirect::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultRedirectFactory->expects($this->once())->method('create')->willReturn($result);
        $result->expects($this->once())->method('setPath')->with('company/index')->willReturnSelf();
        $this->assertEquals($result, $this->save->execute());
    }

    /**
     * Test for execute method with empty company id.
     *
     * @return void
     */
    public function testExecuteWithEmptyCompanyId()
    {
        $params = [
            [
                CompanyInterface::EMAIL => 'exampl@test.com',
                CompanyInterface::NAME => 'Example Company Name',
                CompanyInterface::REGION_ID => 2,
                CompanyInterface::COUNTRY_ID => 'US',
                CompanyInterface::REGION => 'Alabama',
            ]
        ];
        $this->request->expects($this->exactly(2))
            ->method('getParam')
            ->withConsecutive(['id'], ['back'])
            ->willReturnOnConsecutiveCalls(null, true);
        $this->request->expects($this->once())->method('getParams')->willReturn($params);
        $company = $this->getMockBuilder(\Magento\Company\Api\Data\CompanyInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->companyDataFactory->expects($this->once())->method('create')->willReturn($company);
        $this->dataObjectHelper->expects($this->once())
            ->method('populateWithArray')
            ->with($company, $params[0], \Magento\Company\Api\Data\CompanyInterface::class)
            ->willReturnSelf();
        $this->companyRepository->expects($this->once())->method('save')->with($company)->willReturn($company);
        $company->expects($this->once())->method('getId')->willReturn(null);
        $customerMock = $this->getMockBuilder(\Magento\Customer\Api\Data\CustomerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->companySuperUserGet->expects($this->once())->method('getUserForCompanyAdmin')->willReturn($customerMock);
        $this->eventManager->expects($this->once())
            ->method('dispatch')
            ->with('adminhtml_company_save_after', ['company' => $company, 'request' => $this->request]);
        $company->expects($this->once())->method('getCompanyName')->willReturn($params[0][CompanyInterface::NAME]);
        $this->messageManager->expects($this->once())
            ->method('addSuccessMessage')
            ->with(__('You have created company %companyName.', ['companyName' => $params[0][CompanyInterface::NAME]]))
            ->willReturnSelf();
        $result = $this->getMockBuilder(\Magento\Framework\Controller\Result\Redirect::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultRedirectFactory->expects($this->once())->method('create')->willReturn($result);
        $result->expects($this->once())->method('setPath')->with('company/index/new')->willReturnSelf();
        $this->assertEquals($result, $this->save->execute());
    }

    /**
     * Test for execute() method with localized exception.
     *
     * @return void
     */
    public function testExecuteWithLocalizedException()
    {
        $this->prepareExceptionsMocks();
        $phrase = new \Magento\Framework\Phrase(__('Exception message'));
        $exception = new \Magento\Framework\Exception\LocalizedException($phrase);
        $this->eventManager->expects($this->once())
            ->method('dispatch')
            ->with('adminhtml_company_save_after', ['company' => $this->companyMock, 'request' => $this->request])
            ->willThrowException($exception);
        $this->messageManager->expects($this->once())
            ->method('addErrorMessage')
            ->with($exception->getMessage())
            ->willReturnSelf();
        $result = $this->getMockBuilder(\Magento\Framework\Controller\Result\Redirect::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultRedirectFactory->expects($this->once())->method('create')->willReturn($result);
        $result->expects($this->once())->method('setPath')->with('company/index/edit')->willReturnSelf();
        $this->assertEquals($result, $this->save->execute());
    }

    /**
     * Test for execute() method with exception.
     *
     * @return void
     */
    public function testExecuteWithException()
    {
        $this->prepareExceptionsMocks();
        $phrase = new \Magento\Framework\Phrase(__('Something went wrong. Please try again later.'));
        $exception = new \Exception();
        $this->eventManager->expects($this->once())
            ->method('dispatch')
            ->with('adminhtml_company_save_after', ['company' => $this->companyMock, 'request' => $this->request])
            ->willThrowException($exception);
        $this->messageManager->expects($this->once())
            ->method('addExceptionMessage')
            ->with($exception, $phrase)
            ->willReturnSelf();
        $result = $this->getMockBuilder(\Magento\Framework\Controller\Result\Redirect::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultRedirectFactory->expects($this->once())->method('create')->willReturn($result);
        $result->expects($this->once())->method('setPath')->with('company/index/edit')->willReturnSelf();
        $this->assertEquals($result, $this->save->execute());
    }

    /**
     * Prepare mocks for Execute method test when exceptions are thrown.
     *
     * @return void
     */
    private function prepareExceptionsMocks()
    {
        $companyId = 1;
        $params = [
            [
                CompanyInterface::EMAIL => 'exampl@test.com',
                CompanyInterface::NAME => 'Example Company Name',
                CompanyInterface::REGION_ID => 2,
                CompanyInterface::COUNTRY_ID => 'US',
                CompanyInterface::REGION => 'Alabama',
            ]
        ];
        $companyData = [
            CompanyInterface::EMAIL => 'example@test.com',
        ];
        $this->request->expects($this->exactly(2))->method('getParam')
            ->withConsecutive(['id'], ['id'])
            ->willReturnOnConsecutiveCalls($companyId, $companyId);
        $this->request->expects($this->once())->method('getParams')->willReturn($params);
        $this->companyRepository->expects($this->once())
            ->method('get')
            ->with($companyId)
            ->willReturn($this->companyMock);
        $this->dataObjectHelper->expects($this->once())
            ->method('populateWithArray')
            ->with($this->companyMock, $params[0], \Magento\Company\Api\Data\CompanyInterface::class)
            ->willReturnSelf();
        $this->companyRepository->expects($this->once())->method('save')->with($this->companyMock)
            ->willReturn($this->companyMock);
        $customerMock = $this->getMockBuilder(\Magento\Customer\Api\Data\CustomerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->companySuperUserGet->expects($this->once())->method('getUserForCompanyAdmin')->willReturn($customerMock);
        $this->companyMock->expects($this->atLeastOnce())->method('getId')->willReturn($companyId);
        $this->dataObjectProcessor->expects($this->once())
            ->method('buildOutputDataArray')
            ->with($this->companyMock, \Magento\Company\Api\Data\CompanyInterface::class)
            ->willReturn($companyData);
        $this->session->expects($this->once())->method('setCompanyData')->with($companyData)->willReturnSelf();
    }
}
