<?php

namespace Magento\Company\Test\Unit\Model\SaveHandler;

/**
 * Unit tests for Company/Model/SaveHandler/SalesRepresentative model.
 */
class SalesRepresentativeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Company\Model\Email\Sender|\PHPUnit_Framework_MockObject_MockObject
     */
    private $companyEmailSender;

    /**
     * @var \Magento\Company\Model\SaveHandler\SalesRepresentative
     */
    private $model;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->companyEmailSender = $this->createMock(
            \Magento\Company\Model\Email\Sender::class
        );
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $objectManager->getObject(
            \Magento\Company\Model\SaveHandler\SalesRepresentative::class,
            [
                'companyEmailSender' => $this->companyEmailSender,
            ]
        );
    }

    /**
     * Test for execute() method.
     *
     * @return void
     */
    public function testExecute()
    {
        $company = $this
            ->getMockBuilder(\Magento\Company\Api\Data\CompanyInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getSalesRepresentativeId'])
            ->getMockForAbstractClass();
        $initialCompany = $this
            ->getMockBuilder(\Magento\Company\Api\Data\CompanyInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getSalesRepresentativeId'])
            ->getMockForAbstractClass();
        $initialCompany->expects($this->once())->method('getSalesRepresentativeId')->willReturn(1);
        $company->expects($this->atLeastOnce())->method('getSalesRepresentativeId')->willReturn(2);
        $this->companyEmailSender->expects($this->once())
            ->method('sendSalesRepresentativeNotificationEmail')
            ->willReturnSelf();
        $this->model->execute($company, $initialCompany);
    }

    /**
     * Test for execute() method if sales representatives IDs of company and initial company are equal.
     *
     * @return void
     */
    public function testExecuteIfSalesRepresentativesIdsEqual()
    {
        $salesRepresentativeId = 1;
        $company = $this
            ->getMockBuilder(\Magento\Company\Api\Data\CompanyInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getSalesRepresentativeId'])
            ->getMockForAbstractClass();
        $initialCompany = $this
            ->getMockBuilder(\Magento\Company\Api\Data\CompanyInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getSalesRepresentativeId'])
            ->getMockForAbstractClass();
        $initialCompany->expects($this->atLeastOnce())->method('getSalesRepresentativeId')
            ->willReturn($salesRepresentativeId);
        $company->expects($this->atLeastOnce())->method('getSalesRepresentativeId')
            ->willReturn($salesRepresentativeId);
        $this->companyEmailSender->expects($this->never())
            ->method('sendSalesRepresentativeNotificationEmail')
            ->willReturnSelf();
        $this->model->execute($company, $initialCompany);
    }
}
