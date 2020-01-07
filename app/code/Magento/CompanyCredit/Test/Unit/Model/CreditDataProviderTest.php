<?php

namespace Magento\CompanyCredit\Test\Unit\Model;

/**
 * Class CreditDataProviderTest.
 */
class CreditDataProviderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\CompanyCredit\Api\CreditLimitManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $creditLimitManagement;

    /**
     * @var \Magento\CompanyCredit\Model\CreditDataFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $creditDataFactory;

    /**
     * @var \Magento\CompanyCredit\Model\CreditDataProvider
     */
    private $creditDataProvider;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->creditLimitManagement = $this->createMock(
            \Magento\CompanyCredit\Api\CreditLimitManagementInterface::class
        );
        $this->creditDataFactory = $this->createPartialMock(
            \Magento\CompanyCredit\Model\CreditDataFactory::class,
            ['create']
        );

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->creditDataProvider = $objectManager->getObject(
            \Magento\CompanyCredit\Model\CreditDataProvider::class,
            [
                'creditLimitManagement' => $this->creditLimitManagement,
                'creditDataFactory' => $this->creditDataFactory,
            ]
        );
    }

    /**
     * Test for get method.
     *
     * @return void
     */
    public function testGet()
    {
        $companyId = 1;
        $creditLimit = $this->createMock(\Magento\CompanyCredit\Api\Data\CreditLimitInterface::class);
        $this->creditLimitManagement->expects($this->once())
            ->method('getCreditByCompanyId')->with($companyId)->willReturn($creditLimit);
        $creditData = $this->createMock(\Magento\CompanyCredit\Api\Data\CreditDataInterface::class);
        $this->creditDataFactory->expects($this->once())
            ->method('create')->with(['credit' => $creditLimit])->willReturn($creditData);
        $this->assertEquals($creditData, $this->creditDataProvider->get($companyId));
    }
}
