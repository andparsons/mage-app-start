<?php

namespace Magento\Company\Test\Unit\Controller\Adminhtml\Index;

/**
 * Class EditTest.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class EditTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\Company\Controller\Adminhtml\Index\Edit */
    private $controller;

    /** @var \Magento\Backend\Model\View\Result\Page|\PHPUnit\Framework\MockObject_MockObject */
    private $resultPage;

    /** @var \Magento\Framework\View\Result\PageFactory|\PHPUnit\Framework\MockObject_MockObject */
    private $resultPageFactory;

    /** @var \Magento\Company\Api\CompanyRepositoryInterface|\PHPUnit\Framework\MockObject_MockObject */
    private $companyRepository;

    /** @var \Magento\Framework\App\RequestInterface|\PHPUnit\Framework\MockObject_MockObject */
    private $request;

    /** @var \Magento\Framework\Controller\Result\Redirect|\PHPUnit\Framework\MockObject_MockObject */
    private $resultRedirect;

    /** @var \Magento\Framework\Message\ManagerInterface|\PHPUnit\Framework\MockObject_MockObject */
    private $messageManager;

    /** @var \Psr\Log\LoggerInterface|\PHPUnit\Framework\MockObject_MockObject */
    private $logger;

    /** @var int */
    private $companyId;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->companyId = 1;

        $resultForwardFactory = $this->createMock(\Magento\Backend\Model\View\Result\ForwardFactory::class);
        $this->resultPage = $this->createMock(\Magento\Backend\Model\View\Result\Page::class);

        $this->resultPageFactory = $this->createMock(\Magento\Framework\View\Result\PageFactory::class);
        $this->resultPageFactory->expects($this->once())->method('create')->willReturn($this->resultPage);

        $this->companyRepository = $this->createMock(\Magento\Company\Api\CompanyRepositoryInterface::class);

        $this->request = $this->createMock(\Magento\Framework\App\RequestInterface::class);
        $this->request->expects($this->once())->method('getParam')->willReturn($this->companyId);

        $this->resultRedirect = $this->createMock(\Magento\Framework\Controller\Result\Redirect::class);

        $resultRedirectFactory = $this->createMock(\Magento\Backend\Model\View\Result\RedirectFactory::class);
        $resultRedirectFactory->expects($this->any())->method('create')->willReturn($this->resultRedirect);

        $this->messageManager = $this->getMockForAbstractClass(
            \Magento\Framework\Message\ManagerInterface::class,
            [],
            '',
            false
        );
        $this->logger = $this->getMockForAbstractClass(\Psr\Log\LoggerInterface::class, [], '', false);

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->controller = $objectManagerHelper->getObject(
            \Magento\Company\Controller\Adminhtml\Index\Edit::class,
            [
                'resultForwardFactory' => $resultForwardFactory,
                'resultPageFactory' => $this->resultPageFactory,
                'companyRepository' => $this->companyRepository,
                '_request' => $this->request,
                'resultRedirectFactory' => $resultRedirectFactory
            ]
        );
    }

    /**
     * Test for method execute.
     *
     * @return void
     */
    public function testExecute()
    {
        $company = $this->getMockForAbstractClass(
            \Magento\Company\Api\Data\CompanyInterface::class,
            [],
            '',
            false,
            false,
            true,
            ['getCompanyName']
        );
        $company->expects($this->once())->method('getCompanyName')->willReturn('Company Name');

        $this->companyRepository->expects($this->once())->method('get')
            ->with($this->companyId)->willReturn($company);

        $page = $this->createMock(\Magento\Backend\Model\View\Result\Page::class);
        $this->resultPage->expects($this->once())->method('setActiveMenu')
            ->with('Magento_Company::company_index')->willReturn($page);

        $config = $this->createMock(\Magento\Framework\View\Page\Config::class);
        $this->resultPage->expects($this->once())->method('getConfig')->willReturn($config);

        $title = $this->createMock(\Magento\Framework\View\Page\Title::class);
        $config->expects($this->once())->method('getTitle')->willReturn($title);
        $title->expects($this->once())->method('prepend')->with('Company Name');

        $this->assertSame($this->resultPage, $this->controller->execute());
    }

    /**
     * Test for method execute with exception.
     *
     * @return void
     */
    public function testExecuteException()
    {
        $exception = new \Exception('Exception message');
        $this->companyRepository->expects($this->once())
            ->method('get')
            ->with($this->companyId)
            ->willThrowException($exception);
        $this->messageManager->expects($this->any())
            ->method('addError')
            ->with('[Company ID: 1] was not found');

        $this->resultRedirect->expects($this->any())->method('setPath')->with('*/*/index')->willReturnSelf();

        $this->assertSame($this->resultRedirect, $this->controller->execute());
    }
}
