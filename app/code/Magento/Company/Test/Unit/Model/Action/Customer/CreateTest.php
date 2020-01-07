<?php

namespace Magento\Company\Test\Unit\Model\Action\Customer;

use Magento\Company\Model\Company\Structure;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Company\Api\Data\StructureInterface;

/**
 * Unit test for Magento\Company\Model\Action\Customer\Create class.
 */
class CreateTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Structure|\PHPUnit_Framework_MockObject_MockObject
     */
    private $structureManager;
    
    /**
     * @var AccountManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerManager;
    
    /**
     * @var CustomerRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerRepository;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {

        $this->structureManager = $this->getMockBuilder(Structure::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->customerManager = $this->getMockBuilder(AccountManagementInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->customerRepository = $this->getMockBuilder(CustomerRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $objectManager->getObject(
            \Magento\Company\Model\Action\Customer\Create::class,
            [
                'customerRepository' => $this->customerRepository,
                'structureManager' => $this->structureManager,
                'customerManager' => $this->customerManager,
                'objectHelper' => $objectManager,
            ]
        );
    }

    /**
     * Test method \Magento\Company\Model\Action\Customer\Create::execute.
     *
     * @param int|null $customerId
     * @param int $customerRepositorySaveCallsAmount
     * @param int $customerManagerCreateAccountCallsAmount
     * @return void
     *
     * @dataProvider createDataProvider
     */
    public function testExecute(
        $customerId,
        $customerRepositorySaveCallsAmount,
        $customerManagerCreateAccountCallsAmount
    ) {
        $email = 'sample@example.com';

        $customer = $this->getMockBuilder(\Magento\Customer\Api\Data\CustomerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $structure = $this->getMockBuilder(StructureInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $structure->expects($this->once())->method('getId')->willReturn(1);

        $this->customerManager->expects($this->exactly($customerManagerCreateAccountCallsAmount))
            ->method('createAccount');

        $this->customerRepository->expects($this->exactly($customerRepositorySaveCallsAmount))->method('save');
        $customer->expects($this->atLeastOnce())->method('getId')->willReturn($customerId);
        $customer->expects($this->once())->method('getEmail')->willReturn($email);
        $this->customerRepository->expects($this->atLeastOnce())
            ->method('get')
            ->willReturn($customer);

        $this->structureManager->expects($this->once())->method('getStructureByCustomerId')->willReturn($structure);
        $this->structureManager->expects($this->once())->method('removeCustomerNode');

        $this->model->execute($customer, 1);
    }

    /**
     * Data provider for "testExecute" method.
     *
     * @return array
     */
    public function createDataProvider()
    {
        return [
            [1, 1, 0],
            [null, 0, 1]
        ];
    }
}
