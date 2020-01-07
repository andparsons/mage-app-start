<?php
namespace Magento\CompanyCredit\Test\Unit\Gateway\Config;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Unit tests for \Magento\CompanyCredit\Gateway\Config\ActiveHandler.
 */
class ActiveHandlerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var \Magento\CompanyCredit\Gateway\Config\ActiveHandler
     */
    private $activeHandler;

    /**
     * @var \Magento\Payment\Gateway\ConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configInterfaceMock;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerRepositoryMock;

    /**
     * @var \Magento\Company\Model\CompanyContext|\PHPUnit_Framework_MockObject_MockObject
     */
    private $companyContextMock;

    /**
     * @var \Magento\Authorization\Model\UserContextInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $userContextMock;

    /**
     * @var \Magento\Payment\Gateway\Helper\SubjectReader
     */
    private $subjectReader;

    /**
     * @inheritdoc
     */
    public function setUp()
    {
        $this->configInterfaceMock = $this->getMockBuilder(\Magento\Payment\Gateway\ConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->customerRepositoryMock = $this->getMockBuilder(\Magento\Customer\Api\CustomerRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getById'])
            ->getMockForAbstractClass();

        $this->userContextMock = $this->getMockBuilder(\Magento\Authorization\Model\UserContextInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->companyContextMock = $this->getMockBuilder(\Magento\Company\Model\CompanyContext::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCustomerId'])
            ->getMock();

        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $this->subjectReader = $this->objectManagerHelper->getObject(
            \Magento\Payment\Gateway\Helper\SubjectReader::class
        );
        $this->activeHandler = $this->objectManagerHelper->getObject(
            \Magento\CompanyCredit\Gateway\Config\ActiveHandler::class,
            [
                'configInterface'    => $this->configInterfaceMock,
                'customerRepository' => $this->customerRepositoryMock,
                'companyContext'     => $this->companyContextMock,
                'subjectReader'      => $this->subjectReader,
                'userContext' => $this->userContextMock
            ]
        );
    }

    /**
     * Test for handle() method if current user is Admin.
     *
     * @return void
     */
    public function testHandleIfUserAdmin()
    {
        $subject = ['field' => 'active'];
        $field = $subject['field'];
        $storeId = 1;

        $this->configInterfaceMock->expects($this->once())->method('getValue')->with(
            $field,
            $storeId
        )->willReturn("1");
        $this->userContextMock->expects($this->once())->method('getUserType')
            ->willReturn(\Magento\Authorization\Model\UserContextInterface::USER_TYPE_ADMIN);

        $this->assertEquals(true, $this->activeHandler->handle($subject, $storeId));
    }

    /**
     * Test for handle() method of current user is Customer.
     *
     * @return void
     */
    public function testHandleIfUserCustomer()
    {
        $subject = ['field' => 'active'];
        $field = $subject['field'];
        $storeId = 1;
        $customerId = 1;

        $this->configInterfaceMock->expects($this->once())->method('getValue')->with(
            $field,
            $storeId
        )->willReturn("1");
        $this->userContextMock->expects($this->once())->method('getUserType')
            ->willReturn(\Magento\Authorization\Model\UserContextInterface::USER_TYPE_CUSTOMER);

        $this->companyContextMock->expects($this->once())->method('getCustomerId')->willReturn($customerId);
        $customerMock = $this->getMockBuilder(\Magento\Customer\Api\Data\CustomerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $customerExtensionAttributes = $this->getMockBuilder(
            \Magento\Customer\Api\Data\CustomerExtensionInterface::class
        )
            ->disableOriginalConstructor()
            ->setMethods(['getCompanyAttributes'])
            ->getMockForAbstractClass();
        $customerAttributesMock = $this->getMockBuilder(\Magento\Company\Api\Data\CompanyCustomerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $customerExtensionAttributes->expects($this->exactly(2))->method('getCompanyAttributes')
            ->willReturn($customerAttributesMock);
        $customerAttributesMock->expects($this->once())->method('getStatus')->willReturn(1);
        $customerMock->expects($this->exactly(3))->method('getExtensionAttributes')
            ->willReturn($customerExtensionAttributes);
        $this->customerRepositoryMock->expects($this->once())->method('getById')->with($customerId)
            ->willReturn($customerMock);

        $this->assertEquals(true, $this->activeHandler->handle($subject, $storeId));
    }

    /**
     * Test for handle() method if there is no configured value for it.
     *
     * @return void
     */
    public function testHandleNoConfigValue()
    {
        $subject = ['field' => 'active'];
        $field = $subject['field'];
        $storeId = 1;

        $this->configInterfaceMock->expects($this->once())->method('getValue')->with(
            $field,
            $storeId
        )->willReturn(null);
        $this->userContextMock->expects($this->once())->method('getUserType')
            ->willReturn(\Magento\Authorization\Model\UserContextInterface::USER_TYPE_CUSTOMER);

        $this->assertEquals(false, $this->activeHandler->handle($subject, $storeId));
    }

    /**
     * Test for handle() method with NoSuchEntityException.
     *
     * @return void
     */
    public function testHandleWithNoSuchEntityException()
    {
        $subject = ['field' => 'active'];
        $field = $subject['field'];
        $storeId = 1;
        $customerId = 1;

        $this->configInterfaceMock->expects($this->once())->method('getValue')->with(
            $field,
            $storeId
        )->willReturn("1");
        $this->userContextMock->expects($this->once())->method('getUserType')
            ->willReturn(\Magento\Authorization\Model\UserContextInterface::USER_TYPE_CUSTOMER);

        $this->companyContextMock->expects($this->once())->method('getCustomerId')->willReturn($customerId);
        $exception = new \Magento\Framework\Exception\NoSuchEntityException();
        $this->customerRepositoryMock->expects($this->once())->method('getById')->with($customerId)
            ->willThrowException($exception);

        $this->assertEquals(false, $this->activeHandler->handle($subject, $storeId));
    }
}
