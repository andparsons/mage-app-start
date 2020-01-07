<?php

namespace Magento\Company\Test\Unit\Controller\Profile;

/**
 * Class EditTest.
 */
class EditTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Company\Api\StatusServiceInterface|\PHPUnit\Framework\MockObject_MockObject
     */
    private $moduleConfig;

    /**
     * @var \Magento\Authorization\Model\UserContextInterface|\PHPUnit\Framework\MockObject_MockObject
     */
    private $userContext;

    /**
     * @var \Psr\Log\LoggerInterface|\PHPUnit\Framework\MockObject_MockObject
     */
    private $logger;

    /**
     * @var \Magento\Company\Model\CompanyAdminPermission|\PHPUnit\Framework\MockObject_MockObject
     */
    private $companyAdminPermission;

    /**
     * @var \Magento\Framework\Controller\ResultFactory|\PHPUnit\Framework\MockObject_MockObject
     */
    private $resultFactory;

    /**
     * @var \Magento\Framework\Controller\Result\RedirectFactory|\PHPUnit\Framework\MockObject_MockObject
     */
    private $resultRedirectFactory;

    /**
     * @var \Magento\Company\Controller\Profile\Edit|\PHPUnit\Framework\MockObject_MockObject
     */
    private $edit;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->moduleConfig = $this->getMockBuilder(\Magento\Company\Api\StatusServiceInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['isActive'])
            ->getMockForAbstractClass();
        $this->userContext = $this->createMock(
            \Magento\Authorization\Model\UserContextInterface::class
        );
        $this->structureManager = $this->createMock(\Magento\Company\Model\Company\Structure::class);
        $this->logger = $this->createMock(\Psr\Log\LoggerInterface::class);
        $this->companyAdminPermission = $this->createMock(
            \Magento\Company\Model\CompanyAdminPermission::class
        );
        $this->resultRedirectFactory = $this->createPartialMock(
            \Magento\Framework\Controller\Result\RedirectFactory::class,
            ['create']
        );
        $this->resultFactory = $this->createPartialMock(
            \Magento\Framework\Controller\ResultFactory::class,
            ['create']
        );
        $resultPage = $this->createPartialMock(
            \Magento\Framework\View\Result\Page::class,
            ['getConfig', 'getTitle', 'set', 'getLayout', 'getBlock', 'setData']
        );
        $resultPage->expects($this->any())->method('getConfig')->willReturnSelf();
        $resultPage->expects($this->any())->method('getTitle')->willReturnSelf();
        $resultPage->expects($this->any())->method('set')->willReturnSelf();
        $resultPage->expects($this->any())->method('getLayout')->willReturnSelf();
        $resultPage->expects($this->any())->method('getBlock')->willReturnSelf();
        $resultPage->expects($this->any())->method('setData')->willReturnSelf();
        $this->resultFactory->expects($this->any())->method('create')->willReturn($resultPage);

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->edit = $objectManager->getObject(
            \Magento\Company\Controller\Profile\Edit::class,
            [
                'resultFactory' => $this->resultFactory,
                'resultRedirectFactory' => $this->resultRedirectFactory,
                'moduleConfig' => $this->moduleConfig,
                'customerContext' => $this->userContext,
                'logger' => $this->logger,
                'companyAdminPermission' => $this->companyAdminPermission
            ]
        );
    }

    /**
     * Test execute.
     *
     * @param bool $isCurrentUserCompanyAdmin
     * @return void
     * @dataProvider executeDataProvider
     */
    public function testExecute($isCurrentUserCompanyAdmin)
    {
        $this->prepareResultRedirect();
        $this->companyAdminPermission->expects($this->any())
            ->method('isCurrentUserCompanyAdmin')
            ->willReturn($isCurrentUserCompanyAdmin);

        $this->assertInstanceOf(\Magento\Framework\View\Result\Page::class, $this->edit->execute());
    }

    /**
     * Prepare resultRedirect.
     *
     * @return void
     */
    private function prepareResultRedirect()
    {
        $resultRedirect = $this->createMock(\Magento\Framework\Controller\Result\Redirect::class);
        $resultRedirect->expects($this->any())->method('setPath')->willReturnSelf();
        $this->resultRedirectFactory->expects($this->any())->method('create')->willReturn($resultRedirect);
    }

    /**
     * DataProvider execute.
     *
     * @return array
     */
    public function executeDataProvider()
    {
        return [
            [true]
        ];
    }
}
