<?php

namespace Magento\Company\Test\Unit\Model;

use Magento\Company\Api\Data\CompanyCustomerInterface;

/**
 * Test for model CompanySuperUserSave.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CompanySuperUserSaveTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerRepositoryMock;

    /**
     * @var \Magento\Customer\Api\AccountManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerManagerMock;

    /**
     * @var \Magento\Company\Model\Email\Sender|\PHPUnit_Framework_MockObject_MockObject
     */
    private $companyEmailSenderMock;

    /**
     * @var \Magento\Company\Model\Company\Structure|\PHPUnit_Framework_MockObject_MockObject
     */
    private $companyStructureMock;

    /**
     * @var \Magento\Company\Model\Customer\CompanyAttributes|\PHPUnit_Framework_MockObject_MockObject
     */
    private $companyAttributesMock;

    /**
     * @var \Magento\Company\Model\CompanySuperUserSave
     */
    private $companySuperUserSave;

    /**
     * @var \Magento\Customer\Api\Data\CustomerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customer;

    /**
     * @var \Magento\Customer\Api\Data\CustomerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $oldCustomer;

    /**
     * @var \Magento\Company\Model\Action\Company\ReplaceSuperUser|\PHPUnit_Framework_MockObject_MockObject
     */
    private $replaceSuperUserMock;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->customerRepositoryMock = $this->getMockBuilder(\Magento\Customer\Api\CustomerRepositoryInterface::class)
            ->disableOriginalConstructor()->getMock();

        $this->customerManagerMock = $this->getMockBuilder(\Magento\Customer\Api\AccountManagementInterface::class)
            ->disableOriginalConstructor()->getMock();

        $this->companyEmailSenderMock = $this->getMockBuilder(\Magento\Company\Model\Email\Sender::class)
            ->disableOriginalConstructor()->getMock();

        $this->companyStructureMock = $this->getMockBuilder(\Magento\Company\Model\Company\Structure::class)
            ->disableOriginalConstructor()->getMock();

        $this->companyAttributesMock = $this->getMockBuilder(\Magento\Company\Model\Customer\CompanyAttributes::class)
            ->disableOriginalConstructor()->getMock();

        $this->replaceSuperUserMock =
            $this->getMockBuilder(\Magento\Company\Model\Action\Company\ReplaceSuperUser::class)
                ->disableOriginalConstructor()->getMock();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->companySuperUserSave = $objectManager->getObject(
            \Magento\Company\Model\CompanySuperUserSave::class,
            [
                'companyAttributes'  => $this->companyAttributesMock,
                'companyStructure'   => $this->companyStructureMock,
                'customerRepository' => $this->customerRepositoryMock,
                'customerManager'    => $this->customerManagerMock,
                'companyEmailSender' => $this->companyEmailSenderMock,
                'replaceSuperUser'   => $this->replaceSuperUserMock,
            ]
        );
    }

    /**
     * Test for saveCustomer() method.
     *
     * @param int $keepActive
     * @param int $companyStatus
     * @param string $callback
     * @dataProvider saveCustomerDataProvider
     * @return void
     */
    public function testSaveCustomer($keepActive, $companyStatus, $callback)
    {
        $customerId = 17;
        $oldSuperUserId = 18;

        $this->customer = $this->getMockBuilder(\Magento\Customer\Api\Data\CustomerInterface::class)
            ->disableOriginalConstructor()->getMock();

        $this->replaceSuperUserMock->expects($this->once())->method('execute')->with(
            $this->customer,
            $oldSuperUserId,
            $keepActive
        )->willReturnSelf();

        $this->customer->expects($this->atLeastOnce())->method('getId')->willReturn($customerId);
        $this->customerRepositoryMock->expects($this->atLeastOnce())->method('save')->willReturn($this->customer);
        $this->oldCustomer = $this->getMockBuilder(\Magento\Customer\Api\Data\CustomerInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->oldCustomer->expects($this->atLeastOnce())->method('getId')->willReturn($oldSuperUserId);

        switch ($callback) {
            case 'sendEmailsKeepActiveConfigure':
                $this->sendEmailsKeepActiveConfigure();
                break;
            case 'sendEmailsNotKeepActiveConfigure':
                $this->sendEmailsNotKeepActiveConfigure();
                break;
        }
        $this->companySuperUserSave->saveCustomer($this->customer, $this->oldCustomer, $companyStatus, $keepActive);
    }

    /**
     * Test for saveCustomer() method with NoSuchEntityException.
     *
     * @return void
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     */
    public function testSaveCustomerWithNoSuchEntityException()
    {
        $customerId = 17;
        $this->customer = $this->getMockBuilder(\Magento\Customer\Api\Data\CustomerInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->customer->expects($this->atLeastOnce())->method('getId')->willReturn($customerId);
        $this->customerRepositoryMock->expects($this->once())->method('save')->willReturn($this->customer);

        $this->companySuperUserSave->saveCustomer(
            $this->customer,
            null,
            \Magento\Company\Api\Data\CompanyInterface::STATUS_APPROVED
        );
    }

    /**
     * Data provider for saveCustomer method.
     *
     * @return array
     */
    public function saveCustomerDataProvider()
    {
        return [
            [1, null, 'simpleConfigure'],
            [1, \Magento\Company\Api\Data\CompanyInterface::STATUS_APPROVED, 'sendEmailsKeepActiveConfigure'],
            [0, \Magento\Company\Api\Data\CompanyInterface::STATUS_APPROVED, 'sendEmailsNotKeepActiveConfigure'],
        ];
    }

    /**
     * Additional configuration for Save with send emails.
     *
     * @return void
     */
    private function sendEmailsKeepActiveConfigure()
    {
        $companyId = 33;
        $customerAttributes = $this->getMockBuilder(\Magento\Company\Api\Data\CompanyCustomerInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->companyAttributesMock->expects($this->atLeastOnce())
            ->method('getCompanyAttributesByCustomer')->willReturn($customerAttributes);
        $customerAttributes->expects($this->atLeastOnce())->method('getCompanyId')->willReturn($companyId);
        $this->customerRepositoryMock->expects($this->atLeastOnce())
            ->method('getById')->willReturn($this->oldCustomer);

        $this->companyEmailSenderMock->expects($this->once())->method('sendRemoveSuperUserNotificationEmail');
    }

    /**
     * Additional configuration for Save with send emails.
     *
     * @return void
     */
    private function sendEmailsNotKeepActiveConfigure()
    {
        $companyId = 33;
        $customerAttributes = $this->getMockBuilder(\Magento\Company\Api\Data\CompanyCustomerInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->companyAttributesMock->expects($this->atLeastOnce())
            ->method('getCompanyAttributesByCustomer')->willReturn($customerAttributes);
        $customerAttributes->expects($this->atLeastOnce())->method('getCompanyId')->willReturn($companyId);
        $this->customerRepositoryMock->expects($this->atLeastOnce())
            ->method('getById')->willReturn($this->oldCustomer);

        $this->companyEmailSenderMock->expects($this->once())->method('sendInactivateSuperUserNotificationEmail');
    }
}
