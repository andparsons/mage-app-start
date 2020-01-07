<?php

namespace Magento\Company\Test\Unit\Controller\Adminhtml\Index;

/**
 * Unit test for Magento\Company\Controller\Adminhtml\Index\Delete class.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DeleteTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Company\Controller\Adminhtml\Index\Delete
     */
    private $delete;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $request;

    /**
     * @var \Magento\Framework\Controller\Result\Redirect|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultRedirect;

    /**
     * @var \Magento\Company\Api\CompanyRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $companyRepository;

    /**
     * @var \Magento\Framework\Controller\Result\RawFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultRawFactory;

    /**
     * @var \Magento\Backend\Model\UrlInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $url;

    /**
     * @var \Magento\Framework\Message\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $messageManager;

    /**
     * @var \Psr\Log\LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $logger;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->messageManager = $this->getMockBuilder(\Magento\Framework\Message\ManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->logger = $this->getMockBuilder(\Psr\Log\LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->companyRepository = $this->getMockBuilder(\Magento\Company\Api\CompanyRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->resultRedirect = $this->getMockBuilder(\Magento\Framework\Controller\Result\Redirect::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->url = $this->getMockBuilder(\Magento\Backend\Model\UrlInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->request = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->resultRawFactory = $this->getMockBuilder(\Magento\Framework\Controller\Result\RawFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->delete = $objectManagerHelper->getObject(
            \Magento\Company\Controller\Adminhtml\Index\Delete::class,
            [
                'companyRepository' => $this->companyRepository,
                'resultRawFactory' => $this->resultRawFactory,
                '_request' => $this->request,
                'url' => $this->url,
                'messageManager' => $this->messageManager,
                'logger' => $this->logger,
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
        $companyId = 1;
        $companyName = 'Company name';
        $company = $this->getMockBuilder(\Magento\Company\Api\Data\CompanyInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $response = $this->getMockBuilder(\Magento\Framework\Controller\Result\Raw::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->request->expects($this->once())->method('getParam')->with('id')->willReturn($companyId);
        $this->companyRepository->expects($this->once())->method('get')->with($companyId)->willReturn($company);
        $this->companyRepository->expects($this->once())->method('deleteById')->with($companyId)->willReturn(true);
        $company->expects($this->once())->method('getCompanyName')->willReturn($companyName);
        $this->messageManager->expects($this->once())
            ->method('addSuccessMessage')
            ->with(__('You have deleted company %companyName.', ['companyName' => 'Company name']))
            ->willReturnSelf();
        $this->url->expects($this->once())
            ->method('getUrl')
            ->with('company/index')
            ->willReturn('http://exanple.com/admin/company/index');
        $this->resultRawFactory->expects($this->once())->method('create')->willReturn($response);
        $response->expects($this->once())->method('setHeader')->with('Content-type', 'text/plain')->willReturnSelf();
        $response->expects($this->once())->method('setContents')
            ->with(json_encode(['url' => 'http://exanple.com/admin/company/index']))
            ->willReturnSelf();

        $this->assertEquals($response, $this->delete->execute());
    }

    /**
     * Test execute method when company doesn't exist.
     *
     * @return void
     */
    public function testExecuteWithNoSuchEntityException()
    {
        $companyId = 1;
        $exception = new \Magento\Framework\Exception\NoSuchEntityException();
        $response = $this->getMockBuilder(\Magento\Framework\Controller\Result\Raw::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->request->expects($this->once())->method('getParam')->with('id')->willReturn($companyId);
        $this->companyRepository->expects($this->once())
            ->method('get')
            ->with($companyId)
            ->willThrowException($exception);
        $this->messageManager->expects($this->once())
            ->method('addErrorMessage')
            ->with('The company no longer exists.')
            ->willReturnSelf();
        $this->url->expects($this->once())
            ->method('getUrl')
            ->with('company/*/')
            ->willReturn('http://exanple.com/admin/company/*/');
        $this->resultRawFactory->expects($this->once())->method('create')->willReturn($response);
        $response->expects($this->once())->method('setHeader')->with('Content-type', 'text/plain')->willReturnSelf();
        $response->expects($this->once())->method('setContents')
            ->with(json_encode(['url' => 'http://exanple.com/admin/company/*/']))
            ->willReturnSelf();

        $this->assertEquals($response, $this->delete->execute());
    }

    /**
     * Test execute method when company can't be deleted.
     *
     * @return void
     */
    public function testExecuteWithLocalizedException()
    {
        $companyId = 1;
        $company = $this->getMockBuilder(\Magento\Company\Api\Data\CompanyInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $exception = new \Magento\Framework\Exception\LocalizedException(__('Exception message'));
        $response = $this->getMockBuilder(\Magento\Framework\Controller\Result\Raw::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->request->expects($this->once())->method('getParam')->with('id')->willReturn($companyId);
        $this->companyRepository->expects($this->once())->method('get')->with($companyId)->willReturn($company);
        $this->companyRepository->expects($this->once())
            ->method('deleteById')
            ->with($companyId)
            ->willThrowException($exception);
        $this->messageManager->expects($this->once())
            ->method('addErrorMessage')
            ->with('Exception message')
            ->willReturnSelf();
        $this->url->expects($this->once())
            ->method('getUrl')
            ->with('company/index/edit', ['id' => 1])
            ->willReturn('http://exanple.com/admin/company/edit/id/1');
        $this->resultRawFactory->expects($this->once())->method('create')->willReturn($response);
        $response->expects($this->once())->method('setHeader')->with('Content-type', 'text/plain')->willReturnSelf();
        $response->expects($this->once())->method('setContents')
            ->with(json_encode(['url' => 'http://exanple.com/admin/company/edit/id/1']))
            ->willReturnSelf();

        $this->assertEquals($response, $this->delete->execute());
    }

    /**
     * Test execute method when Exception is thrown.
     *
     * @return void
     */
    public function testExecuteWithException()
    {
        $companyId = 1;
        $company = $this->getMockBuilder(\Magento\Company\Api\Data\CompanyInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $exception = new \Exception();
        $response = $this->getMockBuilder(\Magento\Framework\Controller\Result\Raw::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->request->expects($this->once())->method('getParam')->with('id')->willReturn($companyId);
        $this->companyRepository->expects($this->once())->method('get')->with($companyId)->willReturn($company);
        $this->companyRepository->expects($this->once())
            ->method('deleteById')
            ->with($companyId)
            ->willThrowException($exception);
        $this->messageManager->expects($this->once())
            ->method('addErrorMessage')
            ->with(__('Something went wrong. Please try again later.'))
            ->willReturnSelf();
        $this->url->expects($this->once())
            ->method('getUrl')
            ->with('company/index/edit', ['id' => 1])
            ->willReturn('http://exanple.com/admin/company/edit/id/1');
        $this->logger->expects($this->once())->method('critical')->with($exception)->willReturnSelf();
        $this->resultRawFactory->expects($this->once())->method('create')->willReturn($response);
        $response->expects($this->once())->method('setHeader')->with('Content-type', 'text/plain')->willReturnSelf();
        $response->expects($this->once())->method('setContents')
            ->with(json_encode(['url' => 'http://exanple.com/admin/company/edit/id/1']))
            ->willReturnSelf();

        $this->assertEquals($response, $this->delete->execute());
    }
}
