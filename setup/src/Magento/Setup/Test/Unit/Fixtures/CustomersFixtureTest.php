<?php

namespace Magento\Setup\Test\Unit\Fixtures;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Setup\Fixtures\CustomersFixture;

class CustomersFixtureTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Setup\Fixtures\FixtureModel
     */
    private $fixtureModelMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Setup\Model\FixtureGenerator\CustomerGenerator
     */
    private $customerGeneratorMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Setup\Model\Customer\CustomerDataGeneratorFactory
     */
    private $customerDataGeneratorFactoryMock;

    /**
     * @var \Magento\Setup\Fixtures\CustomersFixture
     */
    private $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $collectionFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $collectionMock;

    public function setUp()
    {
        $this->fixtureModelMock = $this->createMock(\Magento\Setup\Fixtures\FixtureModel::class);

        $this->customerGeneratorMock =
            $this->createMock(\Magento\Setup\Model\FixtureGenerator\CustomerGenerator::class);

        $this->customerDataGeneratorFactoryMock =
            $this->createMock(\Magento\Setup\Model\Customer\CustomerDataGeneratorFactory::class);

        $this->collectionFactoryMock =
            $this->createPartialMock(
                \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory::class,
                ['create']
            );

        $this->collectionMock = $this->createMock(\Magento\Customer\Model\ResourceModel\Customer\Collection::class);

        $this->model = (new ObjectManager($this))->getObject(CustomersFixture::class, [
            'fixtureModel' => $this->fixtureModelMock,
            'customerGenerator' => $this->customerGeneratorMock,
            'customerDataGeneratorFactory' => $this->customerDataGeneratorFactoryMock,
            'collectionFactory' => $this->collectionFactoryMock
        ]);
    }

    public function testExecute()
    {
        $entitiesInDB = 20;
        $this->collectionFactoryMock->expects($this->once())->method('create')->willReturn($this->collectionMock);
        $this->collectionMock->expects($this->once())->method('getSize')->willReturn($entitiesInDB);

        $customersNumber = 100500;
        $customerConfig = [
            'some-key' => 'some value'
        ];

        $this->fixtureModelMock
            ->expects($this->exactly(2))
            ->method('getValue')
            ->will($this->onConsecutiveCalls($customersNumber, $customerConfig));

        $customerDataGeneratorMock = $this->createMock(\Magento\Setup\Model\Customer\CustomerDataGenerator::class);

        $this->customerDataGeneratorFactoryMock
            ->expects($this->once())
            ->method('create')
            ->with($customerConfig)
            ->willReturn($customerDataGeneratorMock);

        $this->customerGeneratorMock
            ->expects($this->once())
            ->method('generate')
            ->with(
                $customersNumber - $entitiesInDB,
                $this->arrayHasKey('customer_data')
            );

        $this->model->execute();
    }

    public function testDoNoExecuteIfCustomersAlreadyGenerated()
    {
        $this->collectionFactoryMock->expects($this->once())->method('create')->willReturn($this->collectionMock);
        $this->collectionMock->expects($this->once())->method('getSize')->willReturn(20);
        $this->fixtureModelMock
            ->expects($this->once())
            ->method('getValue')
            ->willReturn(20);
        $this->customerDataGeneratorFactoryMock->expects($this->never())->method('create');

        $this->model->execute();
    }

    public function testGetActionTitle()
    {
        $this->assertSame('Generating customers', $this->model->getActionTitle());
    }

    public function testIntroduceParamLabels()
    {
        $this->assertSame(
            [
                'customers' => 'Customers'
            ],
            $this->model->introduceParamLabels()
        );
    }
}
