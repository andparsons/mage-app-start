<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SharedCatalog\Test\Unit\Ui\Component\Listing\Column\Configure;

use Magento\Bundle\Model\Product\Type as BundleType;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as ConfigurableType;

/**
 * Test price column component.
 */
class PriceTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\SharedCatalog\Ui\Component\Listing\Column\Configure\Price
     */
    private $column;

    /**
     * @var \Magento\Framework\Locale\CurrencyInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $localeCurrency;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManager;

    /**
     * Set up for test.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->localeCurrency = $this->getMockBuilder(\Magento\Framework\Locale\CurrencyInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->storeManager = $this->getMockBuilder(\Magento\Store\Model\StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $context = $this->getMockBuilder(\Magento\Framework\View\Element\UiComponent\ContextInterface::class)
            ->setMethods(['getProcessor'])->disableOriginalConstructor()->getMockForAbstractClass();
        $processor = $this->getMockBuilder(\Magento\Framework\View\Element\UiComponent\Processor::class)
            ->setMethods(['register', 'notify'])->disableOriginalConstructor()->getMock();
        $context->expects($this->never())->method('getProcessor')->willReturn($processor);
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->column = $objectManager->getObject(
            \Magento\SharedCatalog\Ui\Component\Listing\Column\Configure\Price::class,
            [
                'context' => $context,
                'localeCurrency' => $this->localeCurrency,
                'storeManager' => $this->storeManager,
            ]
        );

        $this->column->setData('name', 'price');
    }

    /**
     * Test prepareDataSource.
     *
     * @return void
     */
    public function testPrepareDataSource()
    {
        $dataSource['data'] = [
            'items' => [
                [
                    'type_id' => BundleType::TYPE_CODE,
                    'price' => 100,
                    'max_price' => 200,
                    'price_type' => 1
                ],
                [
                    'type_id' => BundleType::TYPE_CODE,
                    'price' => 100,
                    'max_price' => 200,
                    'price_view' => 1,
                    'price_type' => 0
                ],
                [
                    'type_id' => BundleType::TYPE_CODE,
                    'price' => 100,
                    'max_price' => 200,
                    'price_type' => 0
                ],
                [
                    'type_id' => ConfigurableType::TYPE_CODE,
                    'price' => 100,
                ],
                [
                    'type_id' => 'simple',
                    'price' => 100,
                ],
            ]
        ];
        $expect['data'] = [
            'items' => [
                [
                    'type_id' => BundleType::TYPE_CODE,
                    'price' => '100',
                    'max_price' => 200,
                    'price_type' => 1
                ],
                [
                    'type_id' => BundleType::TYPE_CODE,
                    'price' => '100.000000',
                    'max_price' => '200.000000',
                    'price_view' => 1,
                    'price_type' => 0
                ],
                [
                    'type_id' => BundleType::TYPE_CODE,
                    'price' => '100.000000',
                    'max_price' => '200.000000',
                    'price_type' => 0
                ],
                [
                    'type_id' => ConfigurableType::TYPE_CODE,
                    'price' => '100.000000',
                ],
                [
                    'type_id' => 'simple',
                    'price' => '100',
                ],
            ]
        ];
        $store = $this->getMockBuilder(\Magento\Store\Model\Store::class)
            ->disableOriginalConstructor()
            ->getMock();
        $currency = $this->getMockBuilder(\Magento\Framework\Currency::class)
            ->disableOriginalConstructor()
            ->getMock();
        $store->expects($this->once())->method('getBaseCurrencyCode')->willReturn('USD');
        $this->storeManager->expects($this->once())->method('getStore')->willReturn($store);
        $currency->expects($this->atLeastOnce())->method('toCurrency')->willReturnArgument(0);
        $this->localeCurrency->expects($this->once())->method('getCurrency')->with('USD')->willReturn($currency);
        $this->assertEquals($expect, $this->column->prepareDataSource($dataSource));
    }
}
