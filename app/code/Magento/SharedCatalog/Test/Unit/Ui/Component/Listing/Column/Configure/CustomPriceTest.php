<?php

namespace Magento\SharedCatalog\Test\Unit\Ui\Component\Listing\Column\Configure;

use \Magento\SharedCatalog\Ui\Component\Listing\Column\Configure\CustomPrice;

/**
 * Class CustomPriceTest
 */
class CustomPriceTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\View\Element\UiComponent\ContextInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $context;

    /**
     * @var \Magento\Framework\View\Element\UiComponentFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $uiComponentFactory;

    /**
     * @var \Magento\SharedCatalog\Model\Form\Storage\UrlBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $urlHelper;

    /**
     * @var \Magento\Directory\Model\Currency|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $priceCurrency;

    /**
     * @var \Magento\Directory\Model\Currency|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $currency;

    /**
     * @var CustomPrice|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customPrice;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \Magento\Framework\View\Element\UiComponent\Processor|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $processor;

    /**
     * Set up
     */
    protected function setUp()
    {
        $this->processor = $this->createPartialMock(
            \Magento\Framework\View\Element\UiComponent\Processor::class,
            ['register', 'notify']
        );
        $this->context = $this->getMockForAbstractClass(
            \Magento\Framework\View\Element\UiComponent\ContextInterface::class,
            [],
            '',
            false,
            false,
            true,
            ['getProcessor']
        );
        $this->uiComponentFactory = $this->createMock(
            \Magento\Framework\View\Element\UiComponentFactory::class
        );
        $this->priceCurrency = $this->getMockForAbstractClass(
            \Magento\Framework\Pricing\PriceCurrencyInterface::class,
            [],
            '',
            false,
            false,
            true,
            ['getCurrency']
        );
        $this->currency = $this->createPartialMock(
            \Magento\Directory\Model\Currency::class,
            ['getCurrencySymbol', 'format']
        );
        $this->priceCurrency->expects($this->once())
            ->method('getCurrency')
            ->willReturn($this->currency);
        $this->urlHelper =
            $this->createMock(\Magento\SharedCatalog\Model\Form\Storage\UrlBuilder::class);
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
    }

    /**
     * Test prepareDataSource() method
     *
     * @dataProvider prepareDataSourceDataProvider
     * @param array $dataSource
     * @param int $formatCalls
     */
    public function testPrepareDataSource($dataSource, $formatCalls)
    {
        $this->context->expects($this->never())->method('getProcessor');
        $data = [];
        $fieldName = 'name';
        $data[$fieldName] = 'field_name';
        $this->customPrice = $this->objectManager->getObject(
            \Magento\SharedCatalog\Ui\Component\Listing\Column\Configure\CustomPrice::class,
            [
                'context' => $this->context,
                'uiComponentFactory' => $this->uiComponentFactory,
                'urlBuilder' => $this->urlHelper,
                'priceCurrency' => $this->priceCurrency,
                'components' => [],
                'data' => $data,
            ]
        );
        $this->currency->expects($this->exactly($formatCalls))
            ->method('format')
            ->with('field_value', ['display' => ''], false)
            ->willReturn(true);
        $this->customPrice->prepareDataSource($dataSource);
    }

    /**
     * @return array
     */
    public function prepareDataSourceDataProvider()
    {
        return [
            'datasource_set_items_set' => [
                'datasource' => [
                    'data' => [
                        'items' => [
                            'item1' => [
                                'field_name' => 'field_value'
                            ],
                            'item2' => [
                                'field_name' => 'field_value'
                            ],
                        ],
                    ]
                ],
                'format_calls' => 2
            ],
            'datasource_not_set' => [
                'datasource' => [],
                'format_calls' => 0
                ],

        ];
    }

    /**
     * Test prepare() method
     */
    public function testPrepare()
    {
        $data = ['config' => []];
        $currencySymbol = 'test currency symbol';
        $this->currency->expects($this->once())
            ->method('getCurrencySymbol')
            ->willReturn($currencySymbol);
        $this->urlHelper =
            $this->createPartialMock(\Magento\SharedCatalog\Model\Form\Storage\UrlBuilder::class, ['getUrl']);
        $this->context->expects($this->atLeastOnce())->method('getProcessor')->willReturn($this->processor);
        $this->customPrice = $this->objectManager->getObject(
            \Magento\SharedCatalog\Ui\Component\Listing\Column\Configure\CustomPrice::class,
            [
                'context' => $this->context,
                'uiComponentFactory' => $this->uiComponentFactory,
                'urlBuilder' => $this->urlHelper,
                'priceCurrency' => $this->priceCurrency,
                'components' => [],
                'data' => $data,
            ]
        );
        $this->customPrice->prepare();
    }
}
