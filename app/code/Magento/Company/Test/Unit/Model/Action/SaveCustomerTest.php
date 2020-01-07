<?php
namespace Magento\Company\Test\Unit\Model\Action;

use Magento\Authorization\Model\UserContextInterface;

use Magento\Company\Api\AclInterface;
use Magento\Company\Api\CompanyRepositoryInterface;
use Magento\Company\Api\Data\CompanyCustomerInterface;
use Magento\Company\Api\Data\CompanyInterface;
use Magento\Company\Api\RoleRepositoryInterface;
use Magento\Company\Model\Action\Customer\Populator;
use Magento\Company\Model\Action\SaveCustomer;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\Data\CustomerExtensionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Unit test for Magento\Company\Model\Action\SaveCustomer class.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SaveCustomerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Populator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerPopulator;

    /**
     * @var CustomerRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerRepository;

    /**
     * @var AclInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $acl;

    /**
     * @var RoleRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $roleRepository;

    /**
     * @var UserContextInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $userContext;

    /**
     * @var CompanyRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $companyRepository;

    /**
     * @var SaveCustomer
     */
    private $saveCustomer;

    /**
     * @var \Magento\Company\Model\Action\Customer\Create|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerCreator;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->customerPopulator = $this->getMockBuilder(Populator::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->customerRepository = $this->getMockBuilder(CustomerRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->acl = $this->getMockBuilder(AclInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->roleRepository = $this->getMockBuilder(RoleRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->userContext = $this->getMockBuilder(UserContextInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->companyRepository = $this->getMockBuilder(CompanyRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->customerCreator = $this->getMockBuilder(\Magento\Company\Model\Action\Customer\Create::class)
            ->disableOriginalConstructor()
            ->setMethods(['execute'])
            ->getMock();

        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->saveCustomer = $objectManagerHelper->getObject(
            SaveCustomer::class,
            [
                'customerPopulator' => $this->customerPopulator,
                'customerRepository' => $this->customerRepository,
                'companyRepository' => $this->companyRepository,
                'customerCreator' => $this->customerCreator,
                'acl' => $this->acl,
                'roleRepository' => $this->roleRepository,
                'userContext' => $this->userContext,
            ]
        );
    }

    /**
     * Test create method.
     *
     * @return void
     */
    public function testCreate()
    {
        $email = 'sample@example.com';
        $targetId = 1;
        $role = 'user';
        $params = [
            'email' => $email,
            'target_id' => $targetId,
            'role' => $role
        ];
        $request = $this->getMockBuilder(RequestInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $request->expects($this->atLeastOnce())->method('getParam')
            ->withConsecutive(['email'], ['target_id'], ['role'])
            ->willReturnOnConsecutiveCalls($email, $targetId, $role);
        $companyAttributes = $this->getMockBuilder(CompanyCustomerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $companyAttributes->expects($this->atLeastOnce())->method('getCompanyId')->willReturn(null);
        $extensionAttributes = $this
            ->getMockBuilder(CustomerExtensionInterface::class)
            ->setMethods(['getCompanyAttributes'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $extensionAttributes->expects($this->atLeastOnce())->method('getCompanyAttributes')
            ->willReturn($companyAttributes);
        $customer = $this->getMockBuilder(CustomerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $customer->expects($this->atLeastOnce())
            ->method('getExtensionAttributes')
            ->willReturn($extensionAttributes);

        $this->customerCreator->expects($this->atLeastOnce())->method('execute')
            ->willReturn($customer);
        $this->customerRepository->expects($this->atLeastOnce())
            ->method('get')
            ->willReturn($customer);
        $request->expects($this->once())->method('getParams')->willReturn($params);
        $this->customerPopulator->expects($this->once())
            ->method('populate')
            ->with($params, $customer)
            ->willReturn($customer);

        $this->assertInstanceOf(CustomerInterface::class, $this->saveCustomer->create($request));
    }

    /**
     * Test create method with InputMismatchException.
     *
     * @return void
     *
     * @expectedException \Magento\Framework\Exception\State\InputMismatchException
     */
    public function testCreateWithInputMismatchException()
    {
        $email = 'sample@example.com';
        $request = $this->getMockBuilder(RequestInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $request->expects($this->atLeastOnce())->method('getParam')->with('email')->willReturn($email);
        $customer = $this->getMockBuilder(CustomerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $companyAttributes = $this->getMockBuilder(CompanyCustomerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $companyAttributes->expects($this->atLeastOnce())->method('getCompanyId')->willReturn(1);
        $extensionAttributes = $this
            ->getMockBuilder(CustomerExtensionInterface::class)
            ->setMethods(['getCompanyAttributes'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $extensionAttributes->expects($this->atLeastOnce())->method('getCompanyAttributes')
            ->willReturn($companyAttributes);
        $customer->expects($this->atLeastOnce())
            ->method('getExtensionAttributes')
            ->willReturn($extensionAttributes);
        $this->customerRepository->expects($this->once())
            ->method('get')
            ->with($email)
            ->willReturn($customer);
        $this->assertInstanceOf(CustomerInterface::class, $this->saveCustomer->create($request));
    }

    /**
     * Test update method.
     *
     * @return void.
     */
    public function testUpdate()
    {
        $customerId = 1;
        $roleId = 'user';
        $companyId = 2;
        $params = [
            'email' => $customerId,
            'role' => $roleId
        ];
        $request = $this->getMockBuilder(RequestInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $company = $this->getMockBuilder(CompanyInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $request->expects($this->atLeastOnce())->method('getParam')
            ->withConsecutive(['customer_id'], ['role'])
            ->willReturnOnConsecutiveCalls($customerId, $roleId);
        $request->expects($this->once())->method('getParams')->willReturn($params);
        $companyAttributes = $this->getMockBuilder(CompanyCustomerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $companyAttributes->expects($this->atLeastOnce())->method('getCompanyId')->willReturn($companyId);
        $this->companyRepository->expects($this->once())->method('get')->with($companyId)->willReturn($company);
        $company->expects($this->once())->method('getSuperUserId')->willReturn(2);
        $extensionAttributes = $this
            ->getMockBuilder(CustomerExtensionInterface::class)
            ->setMethods(['getCompanyAttributes'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $extensionAttributes->expects($this->atLeastOnce())
            ->method('getCompanyAttributes')
            ->willReturn($companyAttributes);
        $customer = $this->getMockBuilder(CustomerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $customer->expects($this->atLeastOnce())
            ->method('getExtensionAttributes')
            ->willReturn($extensionAttributes);
        $extensionAttributes->expects($this->atLeastOnce())
            ->method('getCompanyAttributes')
            ->willReturn($companyAttributes);
        $this->customerRepository->expects($this->once())
            ->method('getById')
            ->with($customerId)
            ->willReturn($customer);
        $this->customerPopulator->expects($this->once())
            ->method('populate')
            ->with($params, $customer)
            ->willReturn($customer);
        $this->customerRepository->expects($this->once())->method('save');
        $this->assertInstanceOf(CustomerInterface::class, $this->saveCustomer->update($request));
    }
}
