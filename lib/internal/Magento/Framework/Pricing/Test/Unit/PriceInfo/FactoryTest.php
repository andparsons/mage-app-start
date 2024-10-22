<?php
namespace Magento\Framework\Pricing\Test\Unit\PriceInfo;

use Magento\Framework\Pricing\PriceInfo\Factory;

/**
 * Test class for \Magento\Framework\Pricing\PriceInfo\Factory
 */
class FactoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\ObjectManager\ObjectManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectManagerMock;

    /**
     * @var array
     */
    protected $types;

    /**
     * @var \Magento\Framework\Pricing\PriceInfo\Factory
     */
    protected $factory;

    /**
     * @var \Magento\Framework\Pricing\Price\Collection|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $pricesMock;

    /**
     * @var \Magento\Framework\Pricing\SaleableInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $saleableItemMock;

    /**
     * @var \Magento\Framework\Pricing\PriceInfo\Base|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $priceInfoMock;

    /**
     * SetUp test
     */
    protected function setUp()
    {
        $this->objectManagerMock = $this->createMock(\Magento\Framework\ObjectManager\ObjectManager::class);
        $this->pricesMock = $this->createMock(\Magento\Framework\Pricing\Price\Collection::class);
        $this->saleableItemMock = $this->getMockForAbstractClass(
            \Magento\Framework\Pricing\SaleableInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['getQty']
        );
        $this->priceInfoMock = $this->getMockForAbstractClass(
            \Magento\Framework\Pricing\PriceInfoInterface::class,
            [],
            '',
            false,
            true,
            true,
            []
        );
        $this->types = [
            'default' => [
                'infoClass' => 'Price\PriceInfo\Default',
                'prices' => 'Price\Collection\Default',
            ],
            'configurable' => [
                'infoClass' => 'Price\PriceInfo\Configurable',
                'prices' => 'Price\Collection\Configurable',
            ],
        ];
        $this->factory = new Factory($this->types, $this->objectManagerMock);
    }

    /**
     * @return array
     */
    public function createPriceInfoDataProvider()
    {
        return [
            [
                'simple',
                1,
                'Price\PriceInfo\Default',
                'Price\Collection\Default',
            ],
            [
                'configurable',
                2,
                'Price\PriceInfo\Configurable',
                'Price\Collection\Configurable'
            ]
        ];
    }

    /**
     * @param $typeId
     * @param $quantity
     * @param $infoClass
     * @param $prices
     * @dataProvider createPriceInfoDataProvider
     */
    public function testCreate($typeId, $quantity, $infoClass, $prices)
    {
        $this->saleableItemMock->expects($this->once())
            ->method('getTypeId')
            ->will($this->returnValue($typeId));
        $this->saleableItemMock->expects($this->once())
            ->method('getQty')
            ->will($this->returnValue($quantity));

        $this->objectManagerMock->expects($this->exactly(2))
            ->method('create')
            ->will($this->returnValueMap(
                [
                    [
                        $prices,
                        [
                            'saleableItem' => $this->saleableItemMock,
                            'quantity' => $quantity
                        ],
                        $this->pricesMock,
                    ],
                    [
                        $infoClass,
                        [
                            'saleableItem' => $this->saleableItemMock,
                            'quantity' => $quantity,
                            'prices' => $this->pricesMock
                        ],
                        $this->priceInfoMock
                    ],
                ]
            ));
        $this->assertEquals($this->priceInfoMock, $this->factory->create($this->saleableItemMock, []));
    }
}
