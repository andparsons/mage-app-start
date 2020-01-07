<?php

namespace Magento\Company\Test\Unit\Plugin\Checkout\Controller\Index;

/**
 * Class IndexPluginTest.
 */
class IndexPluginTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Authorization\Model\UserContextInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $userContext;

    /**
     * @var \Magento\Framework\Controller\Result\RedirectFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultRedirectFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManager;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerRepository;

    /**
     * @var \Magento\Company\Model\Customer\PermissionInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $permission;

    /**
     * @var \Magento\Company\Model\CompanyUserPermission|\PHPUnit_Framework_MockObject_MockObject
     */
    private $companyUserPermission;

    /**
     * @var \Magento\Customer\Api\Data\CustomerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customer;

    /**
     * @var \Magento\Checkout\Controller\Index\Index|\PHPUnit_Framework_MockObject_MockObject
     */
    private $controller;

    /**
     * @var \Magento\Company\Plugin\Checkout\Controller\Index\IndexPlugin
     */
    private $plugin;

    /**
     * Set up
     */
    protected function setUp()
    {
        $this->userContext = $this->createMock(
            \Magento\Authorization\Model\UserContextInterface::class
        );
        $this->customerRepository = $this
            ->getMockBuilder(\Magento\Customer\Api\CustomerRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getById'])
            ->getMockForAbstractClass();
        $this->storeManager = $this
            ->getMockBuilder(\Magento\Store\Model\StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getStore'])
            ->getMockForAbstractClass();
        $this->permission = $this
            ->getMockBuilder(\Magento\Company\Model\Customer\PermissionInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['isCheckoutAllowed'])
            ->getMockForAbstractClass();
        $this->resultRedirectFactory = $this->createPartialMock(
            \Magento\Framework\Controller\Result\RedirectFactory::class,
            ['create']
        );

        $this->companyUserPermission = $this->getMockBuilder(\Magento\Company\Model\CompanyUserPermission::class)
            ->setMethods(['isCurrentUserCompanyUser'])
            ->disableOriginalConstructor()->getMock();

        $this->customer = $this->getMockBuilder(\Magento\Customer\Api\Data\CustomerInterface::class)
            ->disableOriginalConstructor()->getMockForAbstractClass();

        $this->controller = $this->getMockBuilder(\Magento\Checkout\Controller\Index\Index::class)
            ->disableOriginalConstructor()->getMock();

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->plugin = $objectManagerHelper->getObject(
            \Magento\Company\Plugin\Checkout\Controller\Index\IndexPlugin::class,
            [
                'customerRepository' => $this->customerRepository,
                'userContext' => $this->userContext,
                'resultRedirectFactory' => $this->resultRedirectFactory,
                'storeManager' => $this->storeManager,
                'permission' => $this->permission,
                'companyUserPermission' => $this->companyUserPermission
            ]
        );
    }

    /**
     * Test aroundExecute() method.
     *
     * @return void
     */
    public function testAroundExecute()
    {
        $closure = function () {
            return;
        };

        $this->userContext->expects($this->exactly(1))->method('getUserId')->willReturn(1);

        $this->customerRepository->expects($this->exactly(1))->method('getById')->with(1)->willReturn($this->customer);

        $isCheckoutAllowed = true;
        $this->permission->expects($this->once())->method('isCheckoutAllowed')->with($this->customer)
            ->willReturn($isCheckoutAllowed);

        $this->assertEquals($closure(), $this->plugin->aroundExecute($this->controller, $closure));
    }

    /**
     * Test aroundExecute() method when Redirect expected.
     *
     * @param bool $isCurrentUserCompanyUser
     * @param string|null $redirectPath
     * @dataProvider aroundExecuteWhenRedirectExpectedDataProvider
     * @return void
     */
    public function testAroundExecuteWhenRedirectExpected($isCurrentUserCompanyUser, $redirectPath)
    {
        $closure = function () {
            return;
        };

        $this->userContext->expects($this->exactly(1))->method('getUserId')->willReturn(1);

        $this->customerRepository->expects($this->exactly(1))->method('getById')->with(1)->willReturn($this->customer);

        $isCheckoutAllowed = false;
        $this->permission->expects($this->once())->method('isCheckoutAllowed')->with($this->customer)
            ->willReturn($isCheckoutAllowed);

        $resultRedirect = $this->getMockBuilder(\Magento\Framework\Controller\Result\Redirect::class)
            ->setMethods(['setPath'])
            ->disableOriginalConstructor()->getMockForAbstractClass();
        $resultRedirect->expects($this->exactly(1))->method('setPath')
            ->with($redirectPath)->willReturnSelf();

        $this->resultRedirectFactory->expects($this->exactly(1))->method('create')
            ->willReturn($resultRedirect);

        $this->companyUserPermission->expects($this->exactly(1))
            ->method('isCurrentUserCompanyUser')->willReturn($isCurrentUserCompanyUser);

        $this->assertEquals($resultRedirect, $this->plugin->aroundExecute($this->controller, $closure));
    }

    /**
     * Data provider for aroundExecute() method when Redirect expected.
     *
     * @return array
     */
    public function aroundExecuteWhenRedirectExpectedDataProvider()
    {
        return [
            [true, 'company/accessdenied'],
            [false,'noroute']
        ];
    }
}
