<?php

namespace Magento\Company\Test\Unit\Model\Customer;

use Magento\Company\Api\Data\CompanyCustomerInterface;
use Magento\Company\Model\Customer\AttributesSaver;

/**
 * Unit tests for Company/Model/Customer/AttributesSaver model.
 */
class AttributesSaverTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Company\Model\ResourceModel\Customer|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerResource;

    /**
     * @var \Magento\Company\Api\Data\CompanyCustomerInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $companyCustomerAttributesFactory;

    /**
     * @var \Magento\Company\Api\CompanyManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $companyManagement;

    /**
     * @var \Magento\Company\Model\Email\Sender|\PHPUnit_Framework_MockObject_MockObject
     */
    private $companyEmailSender;

    /**
     * @var \Magento\Company\Model\Company\Structure|\PHPUnit_Framework_MockObject_MockObject
     */
    private $companyStructure;

    /**
     * @var \Magento\Company\Model\Company|\PHPUnit_Framework_MockObject_MockObject
     */
    private $company;

    /**
     * @var \Magento\Company\Api\AclInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $userRoleManagement;

    /**
     * @var \Magento\Customer\Api\Data\CustomerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customer;

    /**
     * @var \Magento\Company\Api\Data\CompanyCustomerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $companyAttributes;

    /**
     * @var AttributesSaver
     */
    private $attributesSaver;

    /**
     * Set up.
     *
     * @return void.
     */
    public function setUp()
    {
        $this->customerResource = $this->getMockBuilder(\Magento\Company\Model\ResourceModel\Customer::class)
            ->disableOriginalConstructor()
            ->setMethods(['saveAdvancedCustomAttributes'])
            ->getMock();
        $this->companyCustomerAttributesFactory =
            $this->getMockBuilder(\Magento\Company\Api\Data\CompanyCustomerInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->companyManagement = $this->getMockBuilder(\Magento\Company\Api\CompanyManagementInterface::class)
            ->setMethods(['getByCustomerId'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->companyEmailSender = $this->getMockBuilder(\Magento\Company\Model\Email\Sender::class)
            ->disableOriginalConstructor()
            ->setMethods(['saveAttributes', 'sendUserStatusChangeNotificationEmail'])
            ->getMock();
        $this->userRoleManagement = $this->getMockBuilder(\Magento\Company\Api\AclInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['deleteRoles'])
            ->getMockForAbstractClass();
        $this->companyStructure = $this->getMockBuilder(\Magento\Company\Model\Company\Structure::class)
            ->disableOriginalConstructor()
            ->setMethods(['moveStructureChildrenToParent', 'removeCustomerNode', 'getStructureByCustomerId', 'addNode'])
            ->getMock();
        $this->company = $this->getMockBuilder(\Magento\Company\Model\Company::class)
            ->setMethods(['getSuperUserId', 'getId'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->customer = $this->getMockBuilder(\Magento\Customer\Api\Data\CustomerInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId'])
            ->getMockForAbstractClass();
        $this->companyAttributes = $this->getMockBuilder(\Magento\Company\Api\Data\CompanyCustomerInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCompanyId', 'setCustomerId', 'getStatus'])
            ->getMockForAbstractClass();
        $customerExtensionAttributes = $this
            ->getMockBuilder(\Magento\Customer\Api\Data\CustomerExtensionInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCompanyAttributes', 'setCompanyAttributes'])
            ->getMockForAbstractClass();
        $this->customer->method('getExtensionAttributes')->willReturn($customerExtensionAttributes);
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->attributesSaver = $objectManagerHelper->getObject(AttributesSaver::class, [
            'customerResource' => $this->customerResource,
            'companyStructure' => $this->companyStructure,
            'companyManagement' => $this->companyManagement,
            'companyEmailSender' => $this->companyEmailSender,
            'userRoleManagement' => $this->userRoleManagement
        ]);
    }

    /**
     * Test for saveAttributes method.
     *
     * @param int|string $companyCustomerStatus
     * @dataProvider saveAttributesDataProvider
     * @return void
     */
    public function testSaveAttributes($companyCustomerStatus)
    {
        $adminId = 4;
        $companyId = 1;

        $this->companyAttributes->expects($this->atLeastOnce())->method('getCompanyId')->willReturn(1);
        $this->companyAttributes->expects($this->once())->method('setCustomerId');
        $this->companyAttributes
            ->expects($this->exactly(3))
            ->method('getStatus')
            ->willReturn($companyCustomerStatus);
        $this->companyManagement->expects($this->once())->method('getByCustomerId')->willReturn($this->company);

        $admin = $this->getMockBuilder(\Magento\Customer\Api\Data\CustomerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $admin->expects($this->once())->method('getId')->willReturn($adminId);

        $this->companyManagement->expects($this->atLeastOnce())
            ->method('getAdminByCompanyId')
            ->with($companyId)
            ->willReturn($admin);

        $this->company->expects($this->once())->method('getId')->willReturn(1);
        $this->company->expects($this->atLeastOnce())->method('getSuperUserId')->willReturn(25);
        $this->customer->expects($this->atLeastOnce())->method('getId')->willReturn(25);
        $this->companyEmailSender->expects($this->once())->method('sendUserStatusChangeNotificationEmail');
        $this->userRoleManagement->expects($this->once())->method('deleteRoles');
        $this->customerResource->expects($this->once())->method('saveAdvancedCustomAttributes');
        $this->companyStructure->expects($this->once())->method('moveStructureChildrenToParent')->willReturnSelf();
        $this->companyStructure->expects($this->once())->method('removeCustomerNode');

        $adminCompanyStructure = $this->getMockBuilder(\Magento\Company\Api\Data\CompanyCustomerInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId'])
            ->getMockForAbstractClass();
        $adminCompanyStructure->expects($this->once())->method('getId')->willReturn(33);
        $this->companyStructure->expects($this->once())->method('getStructureByCustomerId')
            ->with($adminId)->willReturn($adminCompanyStructure);

        $this->attributesSaver->saveAttributes(
            $this->customer,
            $this->companyAttributes,
            $companyId,
            true,
            CompanyCustomerInterface::STATUS_INACTIVE
        );
    }

    /**
     * Data Provider for testSaveAttributes() method.
     *
     * @return array
     */
    public function saveAttributesDataProvider()
    {
        return [
            [CompanyCustomerInterface::STATUS_ACTIVE],
            ['1']
        ];
    }

    /**
     * Test for exception in saveAttributes method.
     *
     * @expectedException \Magento\Framework\Exception\CouldNotSaveException
     * @return void
     */
    public function testSaveAttributesWithException()
    {
        $this->companyAttributes->expects($this->once())->method('getCompanyId')->willReturn(1);
        $this->companyAttributes
            ->expects($this->once())
            ->method('getStatus')
            ->willReturn(CompanyCustomerInterface::STATUS_INACTIVE);
        $this->companyManagement->expects($this->once())->method('getByCustomerId')->willReturn($this->company);
        $this->company->expects($this->once())->method('getId')->willReturn(1);
        $this->company->expects($this->atLeastOnce())->method('getSuperUserId')->willReturn(25);
        $this->customer->expects($this->exactly(3))->method('getId')->willReturn(25);
        $this->attributesSaver->saveAttributes(
            $this->customer,
            $this->companyAttributes,
            1,
            true,
            1
        );
    }
}
