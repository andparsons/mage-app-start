<?php

namespace Magento\Company\Test\Unit\Model\Action\Company;

use Magento\Company\Api\Data\CompanyCustomerInterface;

/**
 * Test for model \Magento\Company\Model\Action\Company\ReplaceSuperUser.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ReplaceSuperUserTest extends \PHPUnit\Framework\TestCase
{

    /**
     * @var \Magento\Company\Model\Action\Company\ReplaceSuperUser
     */
    private $replaceSuperUser;
    
    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerRepositoryMock;

    /**
     * @var \Magento\Company\Model\ResourceModel\Customer|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerResourceMock;

    /**
     * @var \Magento\Company\Model\Company\Structure|\PHPUnit_Framework_MockObject_MockObject
     */
    private $companyStructureMock;

    /**
     * @var \Magento\Company\Model\Customer\CompanyAttributes|\PHPUnit_Framework_MockObject_MockObject
     */
    private $companyAttributesMock;

    /**
     * @var \Magento\Company\Api\AclInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $userRoleManagementMock;

    /**
     * @var \Magento\Customer\Api\Data\CustomerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customer;

    /**
     * @var \Magento\Customer\Api\Data\CustomerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $oldCustomer;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->customerRepositoryMock = $this->getMockBuilder(\Magento\Customer\Api\CustomerRepositoryInterface::class)
            ->disableOriginalConstructor()->getMock();

        $this->customerResourceMock = $this->getMockBuilder(\Magento\Company\Model\ResourceModel\Customer::class)
            ->disableOriginalConstructor()->getMock();

        $this->companyStructureMock = $this->getMockBuilder(\Magento\Company\Model\Company\Structure::class)
            ->disableOriginalConstructor()->getMock();

        $this->companyAttributesMock = $this->getMockBuilder(\Magento\Company\Model\Customer\CompanyAttributes::class)
            ->disableOriginalConstructor()->getMock();

        $this->userRoleManagementMock = $this->getMockBuilder(\Magento\Company\Api\AclInterface::class)
            ->disableOriginalConstructor()->getMock();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->replaceSuperUser = $objectManager->getObject(
            \Magento\Company\Model\Action\Company\ReplaceSuperUser::class,
            [
                'companyAttributes'     => $this->companyAttributesMock,
                'companyStructure'      => $this->companyStructureMock,
                'customerRepository'    => $this->customerRepositoryMock,
                'customerResource'      => $this->customerResourceMock,
                'userRoleManagement'    => $this->userRoleManagementMock,
            ]
        );
    }

    /**
     * Test for method \Magento\Company\Model\Action\Company\ReplaceSuperUser::execute
     *
     * @return void
     */
    public function testExecute()
    {
        $customerId = 17;
        $oldSuperUserId = 18;
        $companyId = 33;
        $keepActive = false;

        $this->customer = $this->getMockBuilder(\Magento\Customer\Api\Data\CustomerInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->customer->expects($this->atLeastOnce())->method('getId')->willReturn($customerId);

        $this->oldCustomer = $this->getMockBuilder(\Magento\Customer\Api\Data\CustomerInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->customerRepositoryMock->expects($this->atLeastOnce())
            ->method('getById')->with($oldSuperUserId)->willReturn($this->oldCustomer);

        $customerAttributes = $this->getMockBuilder(CompanyCustomerInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->companyAttributesMock->expects($this->atLeastOnce())
            ->method('getCompanyAttributesByCustomer')->willReturn($customerAttributes);

        $customerAttributes->expects($this->atLeastOnce())->method('getCompanyId')->willReturn($companyId);
        $oldCustomerAttributes = $this->getMockBuilder(\Magento\Customer\Api\Data\CustomerExtensionInterface::class)
            ->setMethods(['getCompanyAttributes'])
            ->disableOriginalConstructor()->getMockForAbstractClass();
        $oldCompanyAttributes = $this->getMockBuilder(CompanyCustomerInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->oldCustomer->expects($this->once())
            ->method('getExtensionAttributes')
            ->willReturn($oldCustomerAttributes);
        $oldCustomerAttributes->expects($this->once())
            ->method('getCompanyAttributes')->willReturn($oldCompanyAttributes);

        $oldAddress = $this->getMockBuilder(\Magento\Customer\Api\Data\AddressInterface::class)
            ->disableOriginalConstructor()->getMock();
        $oldAddress->expects($this->once())->method('setCustomerId')->with($customerId)->willReturnSelf();

        $oldCompanyAttributes->method('setStatus')->with(CompanyCustomerInterface::STATUS_INACTIVE)->willReturnSelf();
        $this->customerResourceMock->expects($this->once())
            ->method('saveAdvancedCustomAttributes')->with($oldCompanyAttributes)->willReturnSelf();
        $this->userRoleManagementMock->expects($this->once())
            ->method('assignUserDefaultRole')->with($oldSuperUserId, $companyId);
        $this->companyStructureMock->expects($this->once())
            ->method('moveStructureChildrenToParent')->with($customerId)->willReturnSelf();
        $this->companyStructureMock->expects($this->once())
            ->method('removeCustomerNode')->with($customerId)->willReturnSelf();
        $this->companyStructureMock->expects($this->once())->method('moveCustomerStructure')
            ->with($oldSuperUserId, $customerId, $keepActive)->willReturnSelf();

        // @method copyAddressBook
        $address = $this->getMockBuilder(\Magento\Customer\Api\Data\AddressInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->oldCustomer->expects($this->once())->method('getAddresses')->willReturn([$oldAddress]);
        $this->customer->expects($this->once())->method('getAddresses')->willReturn([$address]);
        $oldAddress->expects($this->once())->method('setId')->with(0)->willReturnSelf();
        $this->customer->expects($this->once())->method('getDefaultBilling')->willReturn(true);
        $oldAddress->expects($this->once())->method('setIsDefaultBilling')->with(false)->willReturnSelf();
        $this->customer->expects($this->once())->method('getDefaultShipping')->willReturn(true);
        $oldAddress->expects($this->once())->method('setIsDefaultShipping')->with(false)->willReturnSelf();
        $this->customer->expects($this->once())
            ->method('setAddresses')
            ->with([$address, $oldAddress])
            ->willReturnSelf();
        $this->oldCustomer->expects($this->atLeastOnce())->method('getId')->willReturn($oldSuperUserId);
        
        $this->replaceSuperUser->execute($this->customer, $oldSuperUserId, $keepActive);
    }
}
