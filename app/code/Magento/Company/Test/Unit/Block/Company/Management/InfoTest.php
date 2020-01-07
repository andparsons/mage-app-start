<?php

namespace Magento\Company\Test\Unit\Block\Company\Management;

/**
 * Class InfoTest
 */
class InfoTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\UrlInterface|\PHPUnit\Framework\MockObject_MockObject
     */
    private $urlBuilder;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface|\PHPUnit\Framework\MockObject_MockObject
     */
    private $customerRepository;

    /**
     * @var \Magento\Authorization\Model\UserContextInterface|\PHPUnit\Framework\MockObject_MockObject
     */
    private $userContext;

    /**
     * @var \Magento\Company\Api\CompanyManagementInterface|\PHPUnit\Framework\MockObject_MockObject
     */
    private $companyManagement;

    /**
     * @var \Magento\Company\Block\Company\Management\Info
     */
    private $info;

    /**
     * Set up
     */
    protected function setUp()
    {
        $this->urlBuilder = $this->createMock(\Magento\Framework\UrlInterface::class);
        $this->customerRepository = $this->createMock(\Magento\Customer\Api\CustomerRepositoryInterface::class);
        $this->userContext = $this->createMock(\Magento\Authorization\Model\UserContextInterface::class);
        $this->companyManagement  = $this
            ->getMockBuilder(\Magento\Company\Api\CompanyManagementInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getByCustomerId'])
            ->getMockForAbstractClass();
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->info = $objectManager->getObject(
            \Magento\Company\Block\Company\Management\Info::class,
            [
                'customerRepository' => $this->customerRepository,
                '_urlBuilder' => $this->urlBuilder,
                'userContext' => $this->userContext,
                'companyManagement' => $this->companyManagement,
                'data' => []
            ]
        );
    }

    /**
     * @param \Magento\Company\Api\Data\CompanyInterface|null $company
     * @param bool $result
     * @dataProvider dataProviderHasCustomerCompany
     */
    public function testHasCustomerCompany($company, $result)
    {
        $this->userContext->expects($this->exactly(1))->method('getUserId')->willReturn(1);
        $this->companyManagement->expects($this->once())->method('getByCustomerId')->with(1)->willReturn($company);
        $this->assertEquals($result, $this->info->hasCustomerCompany());
    }

    /**
     * Test method for getCreateCompanyAccountUrl
     */
    public function testGetCreateCompanyAccountUrl()
    {
        $value = '*/account/createPost';
        $this->urlBuilder->expects($this->any())->method('getUrl')->willReturn($value);
        $this->assertEquals($value, $this->info->getCreateCompanyAccountUrl());
    }

    /**
     * Data provider isCurrentUserCompanyAdmin
     *
     * @return array
     */
    public function dataProviderHasCustomerCompany()
    {
        $company = $this->createMock(\Magento\Company\Api\Data\CompanyInterface::class);
        return [
            [$company, true],
            [null, false]
        ];
    }
}
