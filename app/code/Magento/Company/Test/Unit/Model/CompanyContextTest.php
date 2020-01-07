<?php

namespace Magento\Company\Test\Unit\Model;

/**
 * Unit test for Magento\Company\Model\CompanyContext class.
 */
class CompanyContextTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Company\Api\StatusServiceInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $moduleConfig;

    /**
     * @var \Magento\Authorization\Model\UserContextInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $userContext;

    /**
     * @var \Magento\Company\Api\AuthorizationInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $authorization;

    /**
     * @var \Magento\Company\Model\CompanyUserPermission|\PHPUnit_Framework_MockObject_MockObject
     */
    private $companyUserPermission;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerRepository;

    /**
     * @var \Magento\Framework\App\Http\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    private $httpContext;

    /**
     * @var \Magento\Company\Model\CompanyContext
     */
    private $model;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->moduleConfig = $this->getMockBuilder(\Magento\Company\Api\StatusServiceInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->userContext = $this->getMockBuilder(\Magento\Authorization\Model\UserContextInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->authorization = $this->getMockBuilder(\Magento\Company\Api\AuthorizationInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->companyUserPermission = $this->getMockBuilder(\Magento\Company\Model\CompanyUserPermission::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->customerRepository = $this->getMockBuilder(\Magento\Customer\Api\CustomerRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->httpContext = $this->getMockBuilder(\Magento\Framework\App\Http\Context::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $objectManager->getObject(
            \Magento\Company\Model\CompanyContext::class,
            [
                'moduleConfig' => $this->moduleConfig,
                'userContext' => $this->userContext,
                'authorization' => $this->authorization,
                'companyUserPermission' => $this->companyUserPermission,
                'customerRepository' => $this->customerRepository,
                'httpContext' => $this->httpContext,
            ]
        );
    }

    /**
     * Test isModuleActive method.
     *
     * @return void
     */
    public function testIsModuleActive()
    {
        $this->moduleConfig->expects($this->once())->method('isActive')->willReturn(true);

        $this->assertTrue($this->model->isModuleActive());
    }

    /**
     * Test isStorefrontRegistrationAllowed method.
     *
     * @return void
     */
    public function testIsStorefrontRegistrationAllowed()
    {
        $this->moduleConfig->expects($this->once())->method('isStorefrontRegistrationAllowed')->willReturn(true);

        $this->assertTrue($this->model->isStorefrontRegistrationAllowed());
    }

    /**
     * Test isCustomerLoggedIn method.
     *
     * @return void
     */
    public function testIsCustomerLoggedIn()
    {
        $userId = 1;
        $this->userContext->expects($this->once())->method('getUserId')->willReturn($userId);

        $this->assertTrue($this->model->isCustomerLoggedIn());
    }

    /**
     * Test isResourceAllowed method.
     *
     * @return void
     */
    public function testIsResourceAllowed()
    {
        $resource = 'Magento_Company::users_view';
        $this->authorization->expects($this->once())->method('isAllowed')->with($resource, null)->willReturn(true);

        $this->assertTrue($this->model->isResourceAllowed($resource));
    }

    /**
     * Test getCustomerId method.
     *
     * @return void
     */
    public function testGetCustomerId()
    {
        $userId = 1;
        $this->userContext->expects($this->once())->method('getUserId')->willReturn($userId);

        $this->assertEquals($userId, $this->model->getCustomerId());
    }

    /**
     * Test isCurrentUserCompanyUser method.
     *
     * @return void
     */
    public function testIsCurrentUserCompanyUser()
    {
        $userId = 1;
        $this->userContext->expects($this->once())->method('getUserId')->willReturn($userId);
        $this->companyUserPermission->expects($this->once())->method('isCurrentUserCompanyUser')->willReturn(true);

        $this->assertTrue($this->model->isCurrentUserCompanyUser());
    }

    /**
     * Test getCustomerGroupId method.
     *
     * @return void
     */
    public function testGetCustomerGroupId()
    {
        $userId = 1;
        $customerGroupId = 3;
        $customer = $this->getMockBuilder(\Magento\Customer\Api\Data\CustomerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->userContext->expects($this->once())->method('getUserId')->willReturn($userId);
        $this->customerRepository->expects($this->once())->method('getById')->willReturn($customer);
        $customer->expects($this->once())->method('getGroupId')->willReturn($customerGroupId);

        $this->assertEquals($customerGroupId, $this->model->getCustomerGroupId());
    }

    /**
     * Test getCustomerGroupId method without customer id.
     *
     * @return void
     */
    public function testGetCustomerGroupIdWithoutCustomerId()
    {
        $customerGroupId = 3;
        $this->userContext->expects($this->once())->method('getUserId')->willReturn(null);
        $this->httpContext->expects($this->once())
            ->method('getValue')
            ->with(\Magento\Customer\Model\Context::CONTEXT_GROUP)
            ->willReturn($customerGroupId);

        $this->assertEquals($customerGroupId, $this->model->getCustomerGroupId());
    }

    /**
     * Test getCustomerGroupId method with NoSuchEntityException exception.
     *
     * @return void
     */
    public function testGetCustomerGroupIdWithNoSuchEntityException()
    {
        $userId = 1;
        $exception = new \Magento\Framework\Exception\NoSuchEntityException(__('No such entity.'));
        $this->userContext->expects($this->once())->method('getUserId')->willReturn($userId);
        $this->customerRepository->expects($this->once())->method('getById')->willThrowException($exception);

        $this->assertEquals(
            \Magento\Customer\Api\Data\GroupInterface::NOT_LOGGED_IN_ID,
            $this->model->getCustomerGroupId()
        );
    }
}
