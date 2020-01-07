<?php

namespace Magento\CompanyCredit\Test\Unit\Model;

/**
 * Unit test for CreditLimitManagement model.
 */
class CreditLimitManagementTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\CompanyCredit\Model\CreditLimitFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $creditLimitFactory;

    /**
     * @var \Magento\CompanyCredit\Model\ResourceModel\CreditLimit|\PHPUnit_Framework_MockObject_MockObject
     */
    private $creditLimitResource;

    /**
     * @var \Magento\CompanyCredit\Model\CreditLimitManagement
     */
    private $creditLimitManagement;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->creditLimitFactory = $this->getMockBuilder(\Magento\CompanyCredit\Model\CreditLimitFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()->getMock();
        $this->creditLimitResource = $this
            ->getMockBuilder(\Magento\CompanyCredit\Model\ResourceModel\CreditLimit::class)
            ->disableOriginalConstructor()->getMock();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->creditLimitManagement = $objectManager->getObject(
            \Magento\CompanyCredit\Model\CreditLimitManagement::class,
            [
                'creditLimitFactory' => $this->creditLimitFactory,
                'creditLimitResource' => $this->creditLimitResource,
            ]
        );
    }

    /**
     * Test for method getCreditByCompanyId.
     *
     * @return void
     */
    public function testGetCreditByCompanyId()
    {
        $creditLimitId = 1;
        $companyId = 2;
        $creditLimit = $this->getMockBuilder(\Magento\CompanyCredit\Model\CreditLimit::class)
            ->disableOriginalConstructor()->getMock();
        $this->creditLimitFactory->expects($this->once())->method('create')->willReturn($creditLimit);
        $this->creditLimitResource->expects($this->once())->method('load')
            ->with($creditLimit, $companyId, \Magento\CompanyCredit\Api\Data\CreditLimitInterface::COMPANY_ID)
            ->willReturnSelf();
        $creditLimit->expects($this->once())->method('getId')->willReturn($creditLimitId);
        $this->assertEquals($creditLimit, $this->creditLimitManagement->getCreditByCompanyId($companyId));
    }

    /**
     * Test for method getCreditByCompanyId with exception.
     *
     * @return void
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage Requested company is not found. Row ID: CompanyID = 2.
     */
    public function testGetCreditByCompanyIdWithException()
    {
        $companyId = 2;
        $creditLimit = $this->getMockBuilder(\Magento\CompanyCredit\Model\CreditLimit::class)
            ->disableOriginalConstructor()->getMock();
        $this->creditLimitFactory->expects($this->once())->method('create')->willReturn($creditLimit);
        $this->creditLimitResource->expects($this->once())->method('load')
            ->with($creditLimit, $companyId, \Magento\CompanyCredit\Api\Data\CreditLimitInterface::COMPANY_ID)
            ->willReturnSelf();
        $creditLimit->expects($this->once())->method('getId')->willReturn(null);
        $this->creditLimitManagement->getCreditByCompanyId($companyId);
    }
}
