<?php

namespace Magento\Company\Test\Unit\Model\SaveHandler;

use Magento\Company\Api\Data\CompanyInterface;

/**
 * Unit test for Magento\Company\Model\SaveHandler\CompanyStatus class.
 */
class CompanyStatusTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Company\Model\Email\Sender|\PHPUnit_Framework_MockObject_MockObject
     */
    private $companyEmailSender;

    /**
     * @var \Magento\Company\Model\CompanyManagement|\PHPUnit_Framework_MockObject_MockObject
     */
    private $companyManagement;

    /**
     * @var \Magento\Company\Model\ResourceModel\Company|\PHPUnit_Framework_MockObject_MockObject
     */
    private $companyResource;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime|\PHPUnit_Framework_MockObject_MockObject
     */
    private $date;

    /**
     * @var \Magento\Company\Model\SaveHandler\CompanyStatus
     */
    private $model;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->companyEmailSender = $this->getMockBuilder(\Magento\Company\Model\Email\Sender::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->companyManagement = $this->getMockBuilder(\Magento\Company\Model\CompanyManagement::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->companyResource = $this->getMockBuilder(\Magento\Company\Model\ResourceModel\Company::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->date = $this->getMockBuilder(\Magento\Framework\Stdlib\DateTime\DateTime::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $objectManager->getObject(
            \Magento\Company\Model\SaveHandler\CompanyStatus::class,
            [
                'companyEmailSender' => $this->companyEmailSender,
                'companyManagement' => $this->companyManagement,
                'companyResource' => $this->companyResource,
                'date' => $this->date
            ]
        );
    }

    /**
     * Test execute method.
     *
     * @param int $oldStatus
     * @param int $newStatus
     * @param string $template
     * @return void
     * @dataProvider dataProviderExecute
     */
    public function testExecute($oldStatus, $newStatus, $template)
    {
        $date = '2016-07-08 17:03:43';
        $company = $this->getMockBuilder(\Magento\Company\Model\Company::class)
            ->disableOriginalConstructor()
            ->getMock();
        $initialCompany = $this->getMockBuilder(\Magento\Company\Model\Company::class)
            ->disableOriginalConstructor()
            ->getMock();
        $customer = $this->getMockBuilder(\Magento\Customer\Api\Data\CustomerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $initialCompany->expects($this->atLeastOnce())->method('getStatus')->willReturn($oldStatus);
        $company->expects($this->atLeastOnce())->method('getStatus')->willReturn($newStatus);
        $company->expects($this->atLeastOnce())->method('getId')->willReturn(1);
        $this->companyManagement->expects($this->once())->method('getAdminByCompanyId')->willReturn($customer);
        $this->companyEmailSender->expects($this->once())
            ->method('sendCompanyStatusChangeNotificationEmail')
            ->with($customer, 1, $template)
            ->willReturnSelf();
        if ($newStatus == CompanyInterface::STATUS_REJECTED && $oldStatus != CompanyInterface::STATUS_REJECTED) {
            $this->date->expects($this->once())->method('gmtDate')->willReturn($date);
            $company->expects($this->once())->method('setRejectedAt')->with($date)->willReturnSelf();
            $this->companyResource->expects($this->once())->method('save')->with($company)->willReturn($company);
        }

        $this->model->execute($company, $initialCompany);
    }

    /**
     * Test execute method with non-existing status.
     *
     * @return void
     */
    public function testExecuteWithNonExistingStatus()
    {
        $company = $this->getMockBuilder(\Magento\Company\Model\Company::class)
            ->disableOriginalConstructor()
            ->getMock();
        $initialCompany = $this->getMockBuilder(\Magento\Company\Model\Company::class)
            ->disableOriginalConstructor()
            ->getMock();
        $initialCompany->expects($this->atLeastOnce())->method('getStatus')->willReturn(-1);
        $company->expects($this->atLeastOnce())->method('getStatus')->willReturn(-2);
        $this->companyEmailSender->expects($this->never())->method('sendCompanyStatusChangeNotificationEmail');
        $this->model->execute($company, $initialCompany);
    }

    /**
     * DataProvider for execute method.
     *
     * @return array
     */
    public function dataProviderExecute()
    {
        return [
            [
                CompanyInterface::STATUS_PENDING,
                CompanyInterface::STATUS_APPROVED,
                'company/email/company_status_pending_approval_to_active_template'
            ],
            [
                CompanyInterface::STATUS_REJECTED,
                CompanyInterface::STATUS_APPROVED,
                'company/email/company_status_rejected_blocked_to_active_template'
            ],
            [
                CompanyInterface::STATUS_BLOCKED,
                CompanyInterface::STATUS_APPROVED,
                'company/email/company_status_rejected_blocked_to_active_template'
            ],
            [
                CompanyInterface::STATUS_BLOCKED,
                CompanyInterface::STATUS_PENDING,
                'company/email/company_status_pending_approval_template'
            ],
            [
                CompanyInterface::STATUS_BLOCKED,
                CompanyInterface::STATUS_REJECTED,
                'company/email/company_status_rejected_template'
            ],
            [
                CompanyInterface::STATUS_PENDING,
                CompanyInterface::STATUS_BLOCKED,
                'company/email/company_status_blocked_template'
            ]
        ];
    }
}
