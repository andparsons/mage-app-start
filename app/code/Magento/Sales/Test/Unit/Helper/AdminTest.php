<?php
namespace Magento\Sales\Test\Unit\Helper;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AdminTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\App\Helper\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $contextMock;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManagerMock;

    /**
     * @var \Magento\Sales\Model\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $salesConfigMock;

    /**
     * @var \Magento\Framework\DataObject|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $magentoObjectMock;

    /**
     * @var \Magento\Sales\Model\Order|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderMock;

    /**
     * @var \Magento\Sales\Helper\Admin
     */
    protected $adminHelper;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $priceCurrency;

    /**
     * @var \Magento\Framework\Escaper|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $escaperMock;

    protected function setUp()
    {
        $this->contextMock = $this->getMockBuilder(\Magento\Framework\App\Helper\Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeManagerMock = $this->getMockBuilder(\Magento\Store\Model\StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->salesConfigMock = $this->getMockBuilder(\Magento\Sales\Model\Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->priceCurrency = $this->getMockBuilder(
            \Magento\Framework\Pricing\PriceCurrencyInterface::class
        )->getMock();

        $this->escaperMock = $this->getMockBuilder(\Magento\Framework\Escaper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->adminHelper = (new ObjectManager($this))->getObject(
            \Magento\Sales\Helper\Admin::class,
            [
                'context' => $this->contextMock,
                'storeManager' => $this->storeManagerMock,
                'salesConfig' => $this->salesConfigMock,
                'priceCurrency' => $this->priceCurrency,
                'escaper' => $this->escaperMock
            ]
        );

        $this->magentoObjectMock = $this->getMockBuilder(\Magento\Framework\DataObject::class)
            ->disableOriginalConstructor()
            ->setMethods(['getOrder', 'getData'])
            ->getMock();

        $this->orderMock = $this->getMockBuilder(\Magento\Sales\Model\Order::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->orderMock->expects($this->any())
            ->method('formatBasePrice')
            ->will($this->returnValue('formattedBasePrice'));
        $this->orderMock->expects($this->any())
            ->method('formatPrice')
            ->will($this->returnValue('formattedPrice'));
        $this->orderMock->expects($this->any())
            ->method('getData')
            ->will($this->returnValue('data'));
    }

    /**
     * @param string $expected
     * @param bool $dataObjectIsOrder
     * @param bool $isCurrencyDifferent
     * @param bool $magentoDataObjectHasOrder
     * @param bool $strong
     * @param string $separator
     * @dataProvider displayPricesDataProvider
     */
    public function testDisplayPrices(
        $expected,
        $dataObjectIsOrder,
        $isCurrencyDifferent = true,
        $magentoDataObjectHasOrder = true,
        $strong = false,
        $separator = '<br/>'
    ) {
        $this->orderMock->expects($this->any())
            ->method('isCurrencyDifferent')
            ->will($this->returnValue($isCurrencyDifferent));
        $storeMock = $this->getMockBuilder(\Magento\Store\Model\Store::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeManagerMock->expects($this->any())
            ->method('getStore')
            ->will($this->returnValue($storeMock));
        $this->priceCurrency->expects($this->any())
            ->method('format')
            ->will($this->returnValue('storeFormattedPrice'));
        $dataObject = $this->orderMock;
        if (!$dataObjectIsOrder) {
            $returnRes = false;
            if ($magentoDataObjectHasOrder) {
                $returnRes = $this->orderMock;
            }
            $this->magentoObjectMock->expects($this->once())
                ->method('getOrder')
                ->will($this->returnValue($returnRes));
            $dataObject = $this->magentoObjectMock;
        }
        $basePrice = 10.00;
        $price = 15.00;
        $this->assertEquals(
            $expected,
            $this->adminHelper->displayPrices($dataObject, $basePrice, $price, $strong, $separator)
        );
    }

    /**
     * @param string $expected
     * @param bool $dataObjectIsOrder
     * @param bool $isCurrencyDifferent
     * @param bool $magentoDataObjectHasOrder
     * @param bool $strong
     * @param string $separator
     * @dataProvider displayPricesDataProvider
     */
    public function testDisplayPriceAttribute(
        $expected,
        $dataObjectIsOrder,
        $isCurrencyDifferent = true,
        $magentoDataObjectHasOrder = true,
        $strong = false,
        $separator = '<br/>'
    ) {
        $this->orderMock->expects($this->any())
            ->method('isCurrencyDifferent')
            ->will($this->returnValue($isCurrencyDifferent));
        $storeMock = $this->getMockBuilder(\Magento\Store\Model\Store::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeManagerMock->expects($this->any())
            ->method('getStore')
            ->will($this->returnValue($storeMock));
        $this->priceCurrency->expects($this->any())
            ->method('format')
            ->will($this->returnValue('storeFormattedPrice'));
        $dataObject = $this->orderMock;
        if (!$dataObjectIsOrder) {
            $returnRes = false;
            if ($magentoDataObjectHasOrder) {
                $returnRes = $this->orderMock;
            }
            $this->magentoObjectMock->expects($this->once())
                ->method('getOrder')
                ->will($this->returnValue($returnRes));
            $this->magentoObjectMock->expects($this->any())
                ->method('getData')
                ->will($this->returnValue('data'));
            $dataObject = $this->magentoObjectMock;
        }
        $this->assertEquals(
            $expected,
            $this->adminHelper->displayPriceAttribute($dataObject, 'code', $strong, $separator)
        );
    }

    /**
     * @return array
     */
    public function displayPricesDataProvider()
    {
        return [
            [
                '<strong>formattedBasePrice</strong><br/>[formattedPrice]',
                true,
            ],
            [
                '<strong>formattedBasePrice</strong><br/>[formattedPrice]',
                false,
            ],
            [
                'formattedPrice',
                true,
                false,
            ],
            [
                'formattedPrice',
                false,
                false,
            ],
            [
                '<strong>formattedPrice</strong>',
                true,
                false,
                true,
                true,
            ],
            [
                '<strong>formattedPrice</strong>',
                true,
                false,
                true,
                true,
                'seperator',
            ],
            [
                '<strong>formattedBasePrice</strong>seperator[formattedPrice]',
                true,
                true,
                true,
                true,
                'seperator',
            ],
            [
                'storeFormattedPrice',
                false,
                false,
                false,
                false,
                'seperator',
            ],
            [
                '<strong>storeFormattedPrice</strong>',
                false,
                false,
                false,
                true,
                'seperator',
            ],

        ];
    }

    /**
     * @param string $itemKey
     * @param string $type
     * @param int $calledTimes
     * @dataProvider applySalableProductTypesFilterDataProvider
     */
    public function testApplySalableProductTypesFilter($itemKey, $type, $calledTimes)
    {
        $productMock = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->disableOriginalConstructor()
            ->getMock();
        $productMock->expects($this->any())
            ->method('getTypeId')
            ->will($this->returnValue($type));
        $orderMock = $this->getMockBuilder(\Magento\Sales\Model\Order\Item::class)
            ->disableOriginalConstructor()
            ->setMethods(['__wakeup', 'getProductType'])
            ->getMock();
        $orderMock->expects($this->any())
            ->method('getProductType')
            ->will($this->returnValue($type));
        $quoteMock = $this->getMockBuilder(\Magento\Quote\Model\Quote\Item::class)
            ->disableOriginalConstructor()
            ->getMock();
        $quoteMock->expects($this->any())
            ->method('getProductType')
            ->will($this->returnValue($type));
        $items = [
            'product' => $productMock,
            'order' => $orderMock,
            'quote' => $quoteMock,
            'other' => 'other',
        ];
        $collectionClassName = \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection::class;
        $collectionMock = $this->getMockBuilder($collectionClassName)
            ->disableOriginalConstructor()
            ->getMock();
        $collectionMock->expects($this->any())
            ->method('getItems')
            ->will($this->returnValue([$items[$itemKey]]));
        $collectionMock->expects($this->exactly($calledTimes))
            ->method('removeItemByKey');
        $this->salesConfigMock->expects($this->any())
            ->method('getAvailableProductTypes')
            ->will($this->returnValue(['validProductType']));
        $this->adminHelper->applySalableProductTypesFilter($collectionMock);
    }

    /**
     * @return array
     */
    public function applySalableProductTypesFilterDataProvider()
    {
        return [
            ['product', 'validProductType', 0],
            ['product', 'invalidProductType', 1],
            ['order', 'validProductType', 0],
            ['order', 'invalidProductType', 1],
            ['quote', 'validProductType', 0],
            ['quote', 'invalidProductType', 1],
            ['other', 'validProductType', 1],
        ];
    }

    /**
     * @return void
     */
    public function testEscapeHtmlWithLinks(): void
    {
        $expected = '&lt;a&gt;some text in tags&lt;/a&gt;';
        $this->escaperMock
            ->expects($this->any())
            ->method('escapeHtml')
            ->will($this->returnValue($expected));
        $actual = $this->adminHelper->escapeHtmlWithLinks('<a>some text in tags</a>');
        $this->assertEquals($expected, $actual);
    }
}
