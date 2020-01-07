<?php

namespace Magento\CompanyCredit\Test\Unit\Ui\Component\History\Listing\Column;

use Magento\Authorization\Model\UserContextInterface;

/**
 * Class UpdatedByTest.
 */
class UpdatedByTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\CompanyCredit\Ui\Component\History\Listing\Column\UpdatedBy
     */
    private $updatedByColumn;

    /**
     * @var \Magento\User\Model\UserFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $userFactory;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerRepository;

    /**
     * @var \Magento\Customer\Api\CustomerNameGenerationInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $nameGeneration;

    /**
     * Set up.
     */
    protected function setUp()
    {
        $this->userFactory = $this->createPartialMock(
            \Magento\User\Model\UserFactory::class,
            ['create']
        );
        $this->customerRepository = $this->createMock(
            \Magento\Customer\Api\CustomerRepositoryInterface::class
        );
        $this->nameGeneration = $this->createMock(
            \Magento\Customer\Api\CustomerNameGenerationInterface::class
        );
        $context = $this->createMock(
            \Magento\Framework\View\Element\UiComponent\ContextInterface::class
        );
        $processor = $this->createMock(
            \Magento\Framework\View\Element\UiComponent\Processor::class
        );
        $context->expects($this->never())->method('getProcessor')->willReturn($processor);

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->updatedByColumn = $objectManager->getObject(
            \Magento\CompanyCredit\Ui\Component\History\Listing\Column\UpdatedBy::class,
            [
                'context' => $context,
                'userFactory' => $this->userFactory,
                'customerRepository' => $this->customerRepository,
                'nameGeneration' => $this->nameGeneration,
            ]
        );
        $this->updatedByColumn->setData('name', 'updated_by');
    }

    /**
     * Test method for prepareDataSource.
     */
    public function testPrepareDataSourceWithUser()
    {
        $dataSource = [
            'data' => [
                'items' => [
                    ['updated_by' => 1, 'user_type' => UserContextInterface::USER_TYPE_CUSTOMER],
                ]
            ]
        ];

        $expected = [
            'data' => [
                'items' => [
                    ['updated_by' => 'user user', 'user_type' => UserContextInterface::USER_TYPE_CUSTOMER],
                ]
            ]
        ];

        $user = $this->createMock(
            \Magento\Customer\Api\Data\CustomerInterface::class
        );
        $this->customerRepository->expects($this->once())->method('getById')->with(1)->willReturn($user);
        $this->nameGeneration->expects($this->once())->method('getCustomerName')->with($user)->willReturn('user user');

        $this->assertEquals($expected, $this->updatedByColumn->prepareDataSource($dataSource));
    }

    /**
     * Test method for prepareDataSource.
     */
    public function testPrepareDataSourceWithAdmin()
    {
        $dataSource = [
            'data' => [
                'items' => [
                    ['updated_by' => 1, 'user_type' => UserContextInterface::USER_TYPE_ADMIN],
                ]
            ]
        ];

        $expected = [
            'data' => [
                'items' => [
                    ['updated_by' => 'admin admin', 'user_type' => UserContextInterface::USER_TYPE_ADMIN],
                ]
            ]
        ];

        $user = $this->createMock(
            \Magento\User\Model\User::class
        );
        $this->userFactory->expects($this->once())->method('create')->willReturn($user);
        $user->expects($this->once())->method('load')->with(1)->willReturnSelf();
        $user->expects($this->once())->method('getName')->willReturn('admin admin');

        $this->assertEquals($expected, $this->updatedByColumn->prepareDataSource($dataSource));
    }
}
