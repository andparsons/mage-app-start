<?php

namespace Magento\Company\Test\Unit\Plugin\Customer\Api\CustomerRepository;

use Magento\Company\Api\CompanyCustomerRepositoryInterface;
use Magento\Company\Api\Data\CompanyCustomerInterface;
use Magento\Company\Api\Data\CompanyCustomerInterfaceFactory;
use Magento\Company\Model\Customer\CompanyAttributes;
use Magento\Company\Plugin\Customer\Api\CustomerRepository\Query;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerExtensionInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\Data\Customer;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\DataObject;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Unit tests for Magento\Company\Plugin\Customer\Api\CustomerRepository\Query class.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class QueryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var CompanyCustomerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerAttributes;

    /**
     * @var CompanyCustomerRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $companyCustomerRepository;

    /**
     * @var CustomerRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerRepository;

    /**
     * @var Query|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerSave;

    /**
     * @var CustomerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customer;

    /**
     * @var ExtensionAttributesFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $extensionFactory;

    /**
     * @var CustomerExtensionInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerExtension;

    /**
     * @var CompanyCustomerInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $companyCustomerAttributes;

    /**
     * @var CompanyAttributes|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerSaveAttributes;

    /**
     * @var DataObjectHelper|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dataObjectHelper;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->customerAttributes = $this->getMockBuilder(CompanyCustomerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->companyCustomerRepository = $this->getMockBuilder(CompanyCustomerRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->extensionFactory = $this->getMockBuilder(ExtensionAttributesFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->companyCustomerAttributes = $this->getMockBuilder(CompanyCustomerInterfaceFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->customerSaveAttributes = $this->getMockBuilder(CompanyAttributes::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->customerExtension = $this->getMockBuilder(CustomerExtensionInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->customer = $this->getMockBuilder(Customer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->customerRepository = $this->getMockBuilder(CustomerRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->dataObjectHelper = $this->getMockBuilder(DataObjectHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManagerHelper = new ObjectManager($this);
        $this->customerSave = $objectManagerHelper->getObject(
            Query::class,
            [
                'extensionFactory' => $this->extensionFactory,
                'companyCustomerAttributes' => $this->companyCustomerAttributes,
                'customerSaveAttributes' => $this->customerSaveAttributes,
                'dataObjectHelper' => $this->dataObjectHelper
            ]
        );
    }

    /**
     * Test afterGet with company attributes.
     *
     * @return void
     */
    public function testAfterGetWithCompanyAttributes()
    {
        $this->extensionFactory->expects($this->once())
            ->method('create')
            ->with(CustomerInterface::class)
            ->willReturn($this->customerExtension);

        $this->assertEquals(
            $this->customer,
            $this->customerSave->afterGet($this->customerRepository, $this->customer)
        );
    }

    /**
     * Test afterGet.
     *
     * @return void
     */
    public function testAfterGet()
    {
        $customerExtension = $this->getMockBuilder(CustomerExtensionInterface::class)
            ->setMethods(['getCompanyAttributes', 'setCompanyAttributes'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $customerExtension->expects($this->atLeastOnce())->method('getCompanyAttributes')->willReturn(false);
        $this->customer->expects($this->any())->method('getExtensionAttributes')->willReturn($customerExtension);

        $companyAttributes = ['customer_id' => 1];
        $this->customerSaveAttributes->expects($this->once())
            ->method('getCompanyAttributes')->willReturn($companyAttributes);
        $this->companyCustomerAttributes->expects($this->once())
            ->method('create')->willReturn($this->customerAttributes);
        $this->dataObjectHelper->expects($this->once())->method('populateWithArray')
            ->with(
                $this->customerAttributes,
                $companyAttributes,
                CompanyCustomerInterface::class
            )->willReturnSelf();

        $this->assertEquals(
            $this->customer,
            $this->customerSave->afterGet($this->customerRepository, $this->customer)
        );
    }

    /**
     * Test afterGet with Exception.
     *
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Something went wrong
     * @return void
     */
    public function testAfterGetWithException()
    {
        $this->extensionFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->customerExtension);
        $this->customerSaveAttributes->expects($this->once())
            ->method('getCompanyAttributes')
            ->willThrowException(new \Exception());
        $this->customerSave->afterGet($this->customerRepository, $this->customer);
    }

    /**
     * Test 'getCustomer' method.
     *
     * @return void
     */
    public function testGetCustomer()
    {
        $dataObject = $this->getMockBuilder(DataObject::class)
            ->setMethods(['getCompanyAttributes'])
            ->disableOriginalConstructor()
            ->getMock();
        $dataObject->expects($this->atLeastOnce())->method('getCompanyAttributes')->willReturn(true);
        $this->customer->expects($this->atLeastOnce())->method('getExtensionAttributes')->willReturn($dataObject);
        $this->customerSave->afterGet($this->customerRepository, $this->customer);
    }

    /**
     * Test for method afterGetById.
     *
     * @return void
     */
    public function testAfterGetById()
    {
        $dataObject = $this->getMockBuilder(DataObject::class)->disableOriginalConstructor()->getMock();
        $this->customer->expects($this->atLeastOnce())->method('getExtensionAttributes')->willReturn($dataObject);
        $companyAttributes = ['customer_id' => 1];
        $this->customerSaveAttributes->expects($this->once())
            ->method('getCompanyAttributes')->willReturn($companyAttributes);
        $this->companyCustomerAttributes->expects($this->once())
            ->method('create')->willReturn($this->customerAttributes);
        $this->dataObjectHelper->expects($this->once())
            ->method('populateWithArray')
            ->with($this->customerAttributes, $companyAttributes, CompanyCustomerInterface::class)
            ->willReturnSelf();

        $this->assertEquals(
            $this->customer,
            $this->customerSave->afterGetById($this->customerRepository, $this->customer)
        );
    }
}
