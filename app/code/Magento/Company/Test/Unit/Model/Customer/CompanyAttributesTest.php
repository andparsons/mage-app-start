<?php

namespace Magento\Company\Test\Unit\Model\Customer;

use Magento\Company\Model\Customer\AttributesSaver;

/**
 * Class for test CompanyAttributesTest.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CompanyAttributesTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Company\Model\ResourceModel\Customer|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerResource;

    /**
     * @var \Magento\Framework\Api\DataObjectHelper|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dataObjectHelper;

    /**
     * @var \Magento\Authorization\Model\UserContextInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $userContext;

    /**
     * @var \Magento\Company\Model\Company\Structure|\PHPUnit_Framework_MockObject_MockObject
     */
    private $companyStructure;

    /**
     * @var \Magento\Framework\Api\ExtensionAttributesFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $extensionFactory;

    /**
     * @var AttributesSaver|\PHPUnit_Framework_MockObject_MockObject
     */
    private $attributesSaver;

    /**
     * @var \Magento\Company\Api\Data\CompanyCustomerInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $companyCustomerAttributesFactory;

    /**
     * @var \Magento\Company\Api\CompanyManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $companyManagement;

    /**
     * @inheritdoc
     */
    public function setUp()
    {
        $this->companyCustomerAttributesFactory =
            $this->getMockBuilder(\Magento\Company\Api\Data\CompanyCustomerInterfaceFactory::class)
                ->disableOriginalConstructor()
                ->setMethods(['create'])
                ->getMock();
        $this->companyManagement = $this->getMockBuilder(\Magento\Company\Api\CompanyManagementInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->customerResource = $this->getMockBuilder(\Magento\Company\Model\ResourceModel\Customer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->dataObjectHelper = $this->getMockBuilder(\Magento\Framework\Api\DataObjectHelper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->userContext = $this->getMockBuilder(\Magento\Authorization\Model\UserContextInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->companyStructure = $this->getMockBuilder(\Magento\Company\Model\Company\Structure::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->extensionFactory = $this->getMockBuilder(
            \Magento\Framework\Api\ExtensionAttributesFactory::class
        )
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->attributesSaver = $this->getMockBuilder(\Magento\Company\Model\Customer\AttributesSaver::class)
            ->disableOriginalConstructor()
            ->setMethods(['saveAttributes'])
            ->getMock();
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        /**
         * @var \Magento\Company\Model\Customer\CompanyAttributes
         */
        $this->companyAttributes = $objectManagerHelper->getObject(
            \Magento\Company\Model\Customer\CompanyAttributes::class,
            [
                'customerResource' => $this->customerResource,
                'companyCustomerAttributes' => $this->companyCustomerAttributesFactory,
                'dataObjectHelper' => $this->dataObjectHelper,
                'userContext' => $this->userContext,
                'companyManagement' => $this->companyManagement,
                'extensionFactory' => $this->extensionFactory,
                'attributesSaver' => $this->attributesSaver
            ]
        );
    }

    /**
     * Test for updateCompanyAttributes method.
     *
     * @param array $companyAttributesArray
     * @return void
     * @dataProvider updateCompanyAttributesDataProvider
     */
    public function testUpdateCompanyAttributes(
        array $companyAttributesArray
    ) {
        $customerId = 1;
        $companyStatus = \Magento\Company\Api\Data\CompanyInterface::STATUS_APPROVED;
        $customer = $this->getMockBuilder(\Magento\Customer\Api\Data\CustomerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $customerExtensionAttributes = $this->getMockBuilder(
            \Magento\Customer\Api\Data\CustomerExtensionInterface::class
        )
            ->disableOriginalConstructor()
            ->setMethods(['getCompanyAttributes', 'setCompanyAttributes'])
            ->getMockForAbstractClass();
        $companyAttributes = $this->getMockBuilder(\Magento\Company\Api\Data\CompanyCustomerInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCompanyAttributes', 'setCompanyAttributes'])
            ->getMockForAbstractClass();
        $customer->expects($this->atLeastOnce())->method('getId')->willReturn($customerId);
        $customer->expects($this->atLeastOnce())
            ->method('getExtensionAttributes')
            ->willReturnOnConsecutiveCalls(
                null,
                $customerExtensionAttributes,
                $customerExtensionAttributes,
                $customerExtensionAttributes
            );
        $this->extensionFactory->expects($this->once())
            ->method('create')
            ->with(\Magento\Customer\Api\Data\CustomerInterface::class)
            ->willReturn($customerExtensionAttributes);
        $customer->expects($this->once())
            ->method('setExtensionAttributes')->with($customerExtensionAttributes)->willReturnSelf();
        $customerExtensionAttributes->expects($this->exactly(2))
            ->method('getCompanyAttributes')->willReturnOnConsecutiveCalls(null, $companyAttributes);
        $this->companyCustomerAttributesFactory->expects($this->exactly(3))
            ->method('create')->willReturn($companyAttributes);
        $this->customerResource->expects($this->exactly(4))
            ->method('getCustomerExtensionAttributes')->with($customerId)->willReturn($companyAttributesArray);
        $this->dataObjectHelper->expects($this->exactly(3))->method('populateWithArray')->with(
            $companyAttributes,
            $companyAttributesArray,
            \Magento\Company\Api\Data\CompanyCustomerInterface::class
        )->willReturnSelf();
        $customerExtensionAttributes->expects($this->once())
            ->method('setCompanyAttributes')->with($companyAttributes)->willReturnSelf();
        $companyAttributes->expects($this->exactly(2))->method('getStatus')->willReturn($companyStatus);
        $companyAttributes->expects($this->exactly(3))->method('getCompanyId')->willReturn(null);
        $this->userContext->expects($this->exactly(1))->method('getUserId')->willReturn($customerId);
        $this->userContext->expects($this->exactly(1))->method('getUserType')
            ->willReturn(\Magento\Authorization\Model\UserContextInterface::USER_TYPE_CUSTOMER);
        $companyAttributes->expects($this->once())
            ->method('setCompanyId')
            ->with($companyAttributesArray['company_id'])
            ->willReturnSelf();
        $companyAttributes->expects($this->once())->method('setCustomerId')->with($customerId)->willReturnSelf();
        $companyAttributes->expects($this->once())->method('getCustomerId')->willReturn($customerId);
        $this->companyAttributes->updateCompanyAttributes($customer);
    }

    /**
     * Test for updateCompanyAttributes method with exception.
     *
     * @return void
     * @expectedException \Magento\Framework\Exception\CouldNotSaveException
     */
    public function testUpdateCompanyAttributesWithException()
    {
        $companyId = 2;
        $customerId = 1;
        $oldCompanyId = 3;

        $customer = $this->prepareCustomerForUpdateCompanyAttributesWithCompanyChange(
            $companyId,
            $oldCompanyId,
            $customerId,
            $customerId
        );

        $this->companyAttributes->updateCompanyAttributes($customer);
    }

    /**
     * Prepare Customer mock for updateCompanyAttributes() method test when company was changed.
     *
     * @param int $companyId
     * @param int $oldCompanyId
     * @param int $customerId
     * @param int $superUserId
     * @return \Magento\Customer\Api\Data\CustomerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function prepareCustomerForUpdateCompanyAttributesWithCompanyChange(
        $companyId,
        $oldCompanyId,
        $customerId,
        $superUserId
    ) {
        $companyStatus = \Magento\Company\Api\Data\CompanyInterface::STATUS_APPROVED;
        $companyAttributesArray = ['company_id' => $oldCompanyId];
        $customer = $this->getMockBuilder(\Magento\Customer\Api\Data\CustomerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $customerExtensionAttributes = $this->getMockBuilder(
            \Magento\Customer\Api\Data\CustomerExtensionInterface::class
        )
            ->disableOriginalConstructor()
            ->setMethods(['getCompanyAttributes'])
            ->getMockForAbstractClass();
        $companyAttributes = $this->getMockBuilder(\Magento\Company\Api\Data\CompanyCustomerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $originalCompanyAttributes = $this->getMockBuilder(\Magento\Company\Api\Data\CompanyCustomerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $customer->expects($this->atLeastOnce())->method('getId')->willReturn($customerId);
        $customer->expects($this->atLeastOnce())
            ->method('getExtensionAttributes')->willReturn($customerExtensionAttributes);
        $customerExtensionAttributes->expects($this->exactly(2))
            ->method('getCompanyAttributes')->willReturn($companyAttributes);
        $this->companyCustomerAttributesFactory->expects($this->exactly(2))
            ->method('create')->willReturn($originalCompanyAttributes);
        $this->customerResource->expects($this->exactly(2))
            ->method('getCustomerExtensionAttributes')->with($customerId)->willReturn($companyAttributesArray);
        $this->dataObjectHelper->expects($this->exactly(2))->method('populateWithArray')->with(
            $originalCompanyAttributes,
            $companyAttributesArray,
            \Magento\Company\Api\Data\CompanyCustomerInterface::class
        )->willReturnSelf();
        $originalCompanyAttributes->expects($this->once())->method('getStatus')->willReturn($companyStatus);
        $companyAttributes->expects($this->atLeastOnce())->method('getStatus')->willReturn(null);
        $companyAttributes->expects($this->once())->method('setStatus')->with($companyStatus)->willReturnSelf();
        $companyAttributes->expects($this->atLeastOnce())->method('getCompanyId')->willReturn($companyId);
        $originalCompanyAttributes->expects($this->once())->method('getCompanyId')->willReturn($oldCompanyId);
        $companyAttributes->expects($this->once())->method('setCompanyId')->with($companyId)->willReturnSelf();
        $companyAttributes->expects($this->once())->method('setCustomerId')->with($customerId)->willReturnSelf();
        $companyAttributes->expects($this->once())->method('getCustomerId')->willReturn($customerId);
        $company = $this->getMockBuilder(\Magento\Company\Model\Company::class)
            ->setMethods(['getSuperUserId'])
            ->disableOriginalConstructor()
            ->getMock();
        $company->expects($this->once())->method('getSuperUserId')->willReturn($superUserId);
        $this->companyManagement->expects($this->once())->method('getByCustomerId')->willReturn($company);

        return $customer;
    }

    /**
     * Test for updateCompanyAttributes method with empty customer id.
     *
     * @return void
     */
    public function testUpdateCompanyAttributesWithEmptyCustomerId()
    {
        $customerId = null;
        $companyId = 2;
        $oldCompanyId = 3;
        $customer = $this->getMockBuilder(\Magento\Customer\Api\Data\CustomerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $customerExtensionAttributes = $this->getMockBuilder(
            \Magento\Customer\Api\Data\CustomerExtensionInterface::class
        )
            ->disableOriginalConstructor()
            ->setMethods(['getCompanyAttributes'])
            ->getMockForAbstractClass();
        $companyAttributes = $this->getMockBuilder(
            \Magento\Company\Api\Data\CompanyCustomerInterface::class
        )
            ->disableOriginalConstructor()
            ->getMock();
        $originalCompanyAttributes = $this->getMockBuilder(
            \Magento\Company\Api\Data\CompanyCustomerInterface::class
        )
            ->disableOriginalConstructor()
            ->getMock();
        $customer->expects($this->exactly(4))->method('getId')->willReturn($customerId);
        $customer->expects($this->atLeastOnce())
            ->method('getExtensionAttributes')->willReturn($customerExtensionAttributes);
        $customerExtensionAttributes->expects($this->exactly(2))
            ->method('getCompanyAttributes')->willReturn($companyAttributes);
        $this->companyCustomerAttributesFactory->expects($this->once())
            ->method('create')->willReturn($originalCompanyAttributes);
        $companyAttributes->expects($this->exactly(3))->method('getCompanyId')->willReturn($companyId);
        $originalCompanyAttributes->expects($this->once())->method('getCompanyId')->willReturn($oldCompanyId);
        $companyAttributes->expects($this->once())->method('setCompanyId')->with($companyId)->willReturnSelf();
        $companyAttributes->expects($this->once())->method('setCustomerId')->with($customerId)->willReturnSelf();
        $companyAttributes->expects($this->once())->method('getCustomerId')->willReturn($customerId);
        $this->companyAttributes->updateCompanyAttributes($customer);
    }

    /**
     * Test for getCompanyAttributes method.
     *
     * @return void
     */
    public function testGetCompanyAttributes()
    {
        $customerId = 1;

        $customer = $this->getMockBuilder(\Magento\Customer\Api\Data\CustomerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $companyAttributes = $this->getMockBuilder(\Magento\Company\Api\Data\CompanyCustomerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $customer->expects($this->once())->method('getId')->willReturn($customerId);
        $this->customerResource->expects($this->once())
            ->method('getCustomerExtensionAttributes')->with($customerId)->willReturn($companyAttributes);
        $this->assertEquals($companyAttributes, $this->companyAttributes->getCompanyAttributes($customer));
    }

    /**
     * Test for getCompanyId method.
     *
     * @return void
     */
    public function testGetCompanyId()
    {
        $customerId = 1;
        $companyId = 2;
        $customer = $this->getMockBuilder(\Magento\Customer\Api\Data\CustomerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $customerExtensionAttributes = $this->getMockBuilder(
            \Magento\Customer\Api\Data\CustomerExtensionInterface::class
        )
            ->disableOriginalConstructor()
            ->setMethods(['getCompanyAttributes'])
            ->getMockForAbstractClass();
        $companyAttributes = $this->getMockBuilder(\Magento\Company\Api\Data\CompanyCustomerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $customer->expects($this->atLeastOnce())->method('getId')->willReturn($customerId);
        $customer->expects($this->atLeastOnce())
            ->method('getExtensionAttributes')->willReturn($customerExtensionAttributes);
        $customerExtensionAttributes->expects($this->atLeastOnce())
            ->method('getCompanyAttributes')->willReturn($companyAttributes);
        $this->companyCustomerAttributesFactory->expects($this->exactly(2))
            ->method('create')->willReturn($companyAttributes);
        $this->customerResource->expects($this->once())
            ->method('getCustomerExtensionAttributes')->with($customerId)->willReturn([]);
        $this->dataObjectHelper->expects($this->once())->method('populateWithArray')->with(
            $companyAttributes,
            [],
            \Magento\Company\Api\Data\CompanyCustomerInterface::class
        )->willReturnSelf();
        $companyAttributes->expects($this->exactly(5))->method('getCompanyId')->willReturn($companyId);
        $companyAttributes->expects($this->once())->method('setCompanyId')->with($companyId)->willReturnSelf();
        $companyAttributes->expects($this->once())->method('setCustomerId')->with($customerId)->willReturnSelf();
        $this->companyAttributes->updateCompanyAttributes($customer);
        $this->assertEquals($companyId, $this->companyAttributes->getCompanyId());
    }

    /**
     * Test for saveCompanyAttributes method.
     *
     * @return void
     */
    public function testSaveCompanyAttributes()
    {
        $companyAttributes = $this->getMockBuilder(\Magento\Company\Api\Data\CompanyCustomerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $reflectionClass = new \ReflectionClass(\Magento\Company\Model\Customer\CompanyAttributes::class);
        $reflectionCompanyAttributesProperty = $reflectionClass->getProperty('companyAttributes');
        $reflectionCompanyAttributesProperty->setAccessible(true);
        $reflectionCompanyAttributesProperty->setValue($this->companyAttributes, $companyAttributes);
        $reflectionCompanyChangeProperty = $reflectionClass->getProperty('companyChange');
        $reflectionCompanyChangeProperty->setAccessible(true);
        $reflectionCompanyChangeProperty->setValue($this->companyAttributes, true);
        $customer = $this->getMockBuilder(\Magento\Customer\Api\Data\CustomerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->attributesSaver->expects($this->once())->method('saveAttributes');
        $this->assertInstanceOf(
            \Magento\Company\Model\Customer\CompanyAttributes::class,
            $this->companyAttributes->saveCompanyAttributes($customer)
        );
    }

    /**
     * Test for saveCompanyAttributes() method with customer assignment.
     *
     * @return void
     */
    public function testSaveCompanyAttributesWithCustomerAssign()
    {
        $companyId = 2;
        $customerId = 1;
        $oldCompanyId = 3;
        $superUserId = 100;

        $customer = $this->prepareCustomerForUpdateCompanyAttributesWithCompanyChange(
            $companyId,
            $oldCompanyId,
            $customerId,
            $superUserId
        );
        $this->companyAttributes->updateCompanyAttributes($customer);

        $this->attributesSaver->expects($this->once())->method('saveAttributes');
        $this->companyManagement->expects($this->once())->method('assignCustomer')
            ->with($companyId, $customerId);

        $this->assertInstanceOf(
            \Magento\Company\Model\Customer\CompanyAttributes::class,
            $this->companyAttributes->saveCompanyAttributes($customer)
        );
    }

    /**
     * Test for updateCompanyAttributes method when trying to change company for a company admin.
     *
     * @return void
     * @expectedException \Magento\Framework\Exception\CouldNotSaveException
     * @expectedExceptionMessage Invalid attribute value. Cannot change company for a company admin.
     */
    public function testUpdateCompanyAttributesChangeCompanyForCompanyAdmin()
    {
        $customerId = 1;
        $companyId = 2;
        $oldCompanyId = 3;
        $companyStatus = \Magento\Company\Api\Data\CompanyInterface::STATUS_APPROVED;
        $customer = $this->getMockBuilder(\Magento\Customer\Api\Data\CustomerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $customerExtensionAttributes = $this->getMockBuilder(
            \Magento\Customer\Api\Data\CustomerExtensionInterface::class
        )
            ->disableOriginalConstructor()
            ->setMethods(['getCompanyAttributes', 'setCompanyAttributes'])
            ->getMockForAbstractClass();
        $companyAttributes = $this->getMockBuilder(\Magento\Company\Api\Data\CompanyCustomerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $originalCompanyAttributes = $this->getMockBuilder(\Magento\Company\Api\Data\CompanyCustomerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $customer->expects($this->atLeastOnce())->method('getId')->willReturn($customerId);
        $customer->expects($this->atLeastOnce())
            ->method('getExtensionAttributes')->willReturn($customerExtensionAttributes);
        $customerExtensionAttributes->expects($this->exactly(2))
            ->method('getCompanyAttributes')->willReturn($companyAttributes);
        $this->companyCustomerAttributesFactory->expects($this->exactly(2))
            ->method('create')->willReturn($originalCompanyAttributes);
        $this->customerResource->expects($this->exactly(2))
            ->method('getCustomerExtensionAttributes')->with($customerId)->willReturn([]);
        $this->dataObjectHelper->expects($this->exactly(2))->method('populateWithArray')->with(
            $companyAttributes,
            [],
            \Magento\Company\Api\Data\CompanyCustomerInterface::class
        )->willReturnSelf();
        $companyAttributes->expects($this->atLeastOnce())->method('getStatus')->willReturn(null);
        $originalCompanyAttributes->expects($this->once())->method('getStatus')->willReturn($companyStatus);
        $companyAttributes->expects($this->once())->method('setStatus')->with($companyStatus)->willReturnSelf();
        $companyAttributes->expects($this->atLeastOnce())->method('getCompanyId')->willReturn($companyId);
        $originalCompanyAttributes->expects($this->once())->method('getCompanyId')->willReturn($oldCompanyId);
        $companyAttributes->expects($this->once())->method('setCompanyId')->with($companyId)->willReturnSelf();
        $companyAttributes->expects($this->atLeastOnce())->method('setCustomerId')->with($customerId)->willReturnSelf();
        $companyAttributes->expects($this->once())->method('getCustomerId')->willReturn($customerId);
        $company = $this->getMockBuilder(\Magento\Company\Model\Company::class)
            ->setMethods(['getSuperUserId'])
            ->disableOriginalConstructor()
            ->getMock();
        $company->expects($this->once())->method('getSuperUserId')->willReturn($customerId);
        $this->companyManagement->expects($this->atLeastOnce())->method('getByCustomerId')->willReturn($company);
        $this->companyAttributes->updateCompanyAttributes($customer);
    }

    /**
     * Data provider for testUpdateCompanyAttributes.
     *
     * @return array
     */
    public function updateCompanyAttributesDataProvider()
    {
        return [
            [
                ['company_id' => 0],
                [],
            ],
            [
                ['company_id' => 1],
                [],
            ],
            [
                ['company_id' => 1],
                ['customer_id' => 1],
            ],
        ];
    }

    /**
     * Test updateCompanyAttributes with invalid company id.
     *
     * @return void
     * @expectedException \Magento\Framework\Exception\CouldNotSaveException
     * @expectedExceptionMessage You cannot update the requested attribute. Row ID: companyId = 0.
     */
    public function testUpdateCompanyAttributesWithInvalidCompanyId()
    {
        $customerId = 1;
        $customer = $this->getMockBuilder(\Magento\Customer\Api\Data\CustomerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $customerExtensionAttributes = $this->getMockBuilder(
            \Magento\Customer\Api\Data\CustomerExtensionInterface::class
        )
            ->disableOriginalConstructor()
            ->setMethods(['getCompanyAttributes'])
            ->getMockForAbstractClass();
        $companyAttributes = $this->getMockBuilder(\Magento\Company\Api\Data\CompanyCustomerInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCompanyAttributes'])
            ->getMockForAbstractClass();
        $company = $this->getMockBuilder(\Magento\Company\Model\Company::class)
            ->disableOriginalConstructor()
            ->getMock();
        $customer->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturnOnConsecutiveCalls(null, $customerId, $customerId);
        $customer->expects($this->atLeastOnce())
            ->method('getExtensionAttributes')
            ->willReturnOnConsecutiveCalls(
                $customerExtensionAttributes,
                $customerExtensionAttributes,
                $customerExtensionAttributes
            );
        $customerExtensionAttributes->expects($this->atLeastOnce())
            ->method('getCompanyAttributes')->willReturnOnConsecutiveCalls($companyAttributes, $companyAttributes);

        $this->companyManagement->expects($this->once())->method('getByCustomerId')->willReturn($company);
        $companyAttributes->expects($this->once())->method('getCompanyId')->willReturn(null);
        $companyAttributes->expects($this->once())->method('getCompanyId')->willReturn(null);
        $this->userContext->expects($this->once())->method('getUserId')->willReturn(null);

        $this->companyAttributes->updateCompanyAttributes($customer);
    }
}
