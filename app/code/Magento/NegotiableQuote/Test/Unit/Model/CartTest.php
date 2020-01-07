<?php

namespace Magento\NegotiableQuote\Test\Unit\Model;

/**
 * Test for Magento\NegotiableQuote\Model\Cart class.
 */
class CartTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Quote\Model\Quote|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $quote;

    /**
     * @var \Magento\AdvancedCheckout\Model\Cart|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $cartMock;

    /**
     * @var \Magento\Backend\Model\Session|\PHPUnit_Framework_MockObject_MockObject
     */
    private $sessionQuote;

    /**
     * @var \Magento\AdvancedCheckout\Model\CartFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $cartFactory;

    /**
     * @var \Magento\NegotiableQuote\Model\Cart
     */
    private $cart;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->cartFactory = $this->getMockBuilder(\Magento\AdvancedCheckout\Model\CartFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->sessionQuote = $this->getMockBuilder(\Magento\Backend\Model\Session::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->cartMock = $this->getMockBuilder(\Magento\AdvancedCheckout\Model\Cart::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'setQuote',
                    'prepareAddProductsBySku',
                    'prepareAddProductBySku',
                    'saveAffectedProducts',
                    'setSession',
                    'removeAllAffectedItems',
                    'removeAffectedItem',
                    'getFailedItems',
                    'setContext'
                ]
            )
            ->getMock();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->cart = $objectManager->getObject(
            \Magento\NegotiableQuote\Model\Cart::class,
            [
                'cartFactory' => $this->cartFactory,
                'sessionQuote' => $this->sessionQuote
            ]
        );
    }

    /**
     * Test for removeFailedSku method.
     *
     * @return void
     */
    public function testRemoveFailedSku()
    {
        $this->cartFactory->expects($this->once())->method('create')->willReturn($this->cartMock);
        $this->cartMock->expects($this->once())->method('setSession')->willReturnSelf();
        $this->cartMock->expects($this->once())->method('removeAffectedItem')
            ->with('test')
            ->willReturn(true);

        $this->cart->removeFailedSku('test');
    }

    /**
     * Test for removeAllFailed method.
     *
     * @return void
     */
    public function testRemoveAllFailed()
    {
        $this->cartFactory->expects($this->once())->method('create')->willReturn($this->cartMock);
        $this->cartMock->expects($this->once())->method('setSession')->willReturnSelf();
        $this->cartMock->expects($this->once())->method('removeAllAffectedItems')->willReturn(true);

        $this->cart->removeAllFailed();
    }

    /**
     * Test for addItems method.
     *
     * @return void
     */
    public function testAddItems()
    {
        $addItems = [];
        $addItems[] = [
            'sku' => 'test'
        ];
        $failedItems[] = [
            'item' => [
                'sku' => 'dummy'
            ]
        ];
        $quote = $this->getMockBuilder(\Magento\Quote\Model\Quote::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->cartFactory->expects($this->once())->method('create')->willReturn($this->cartMock);
        $this->cartMock->expects($this->once())->method('setQuote')->with($quote)->willReturnSelf();
        $this->cartMock->expects($this->once())
            ->method('setContext')
            ->with(\Magento\AdvancedCheckout\Model\Cart::CONTEXT_ADMIN_CHECKOUT)
            ->willReturnSelf();
        $this->cartMock->expects($this->once())->method('getFailedItems')->willReturn($failedItems);
        $this->cartMock->expects($this->once())->method('prepareAddProductsBySku')->with($addItems)->willReturnSelf();
        $this->cartMock->expects($this->once())
            ->method('saveAffectedProducts')
            ->with($this->cartMock, false)
            ->willReturnSelf();

        $this->assertTrue($this->cart->addItems($quote, $addItems));
    }

    /**
     * Test for addItems method when all items failed to be added.
     *
     * @return void
     */
    public function testAddItemsFailed()
    {
        $addItems = [];
        $addItems[] = [
            'sku' => 'dummy'
        ];
        $failedItems[] = [
            'item' => [
                'sku' => 'dummy'
            ]
        ];
        $quote = $this->getMockBuilder(\Magento\Quote\Model\Quote::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->cartFactory->expects($this->once())->method('create')->willReturn($this->cartMock);
        $this->cartMock->expects($this->once())->method('setQuote')->with($quote)->willReturnSelf();
        $this->cartMock->expects($this->once())
            ->method('setContext')
            ->with(\Magento\AdvancedCheckout\Model\Cart::CONTEXT_ADMIN_CHECKOUT)
            ->willReturnSelf();
        $this->cartMock->expects($this->once())->method('getFailedItems')->willReturn($failedItems);
        $this->cartMock->expects($this->once())->method('prepareAddProductsBySku')->with($addItems)->willReturnSelf();
        $this->cartMock->expects($this->once())
            ->method('saveAffectedProducts')
            ->with($this->cartMock, false)
            ->willReturnSelf();

        $this->assertFalse($this->cart->addItems($quote, $addItems));
    }

    /**
     * Test for addConfiguredItems method.
     *
     * @return void
     */
    public function testAddConfiguredItems()
    {
        $configuredItems = [
            1 => [
                'productSku' => 'testSku',
                'qty' => 1,
                'config' => 'config'
            ]
        ];
        $quote = $this->getMockBuilder(\Magento\Quote\Model\Quote::class)->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->cartFactory->expects($this->once())->method('create')->willReturn($this->cartMock);
        $this->cartMock->expects($this->once())->method('setQuote')->with($quote)->willReturnSelf();
        $this->cartMock->expects($this->once())
            ->method('setContext')
            ->with(\Magento\AdvancedCheckout\Model\Cart::CONTEXT_ADMIN_CHECKOUT)
            ->willReturnSelf();
        $this->cartMock->expects($this->once())->method('removeAffectedItem')->with('testSku')->willReturn(true);
        $this->cartMock->expects($this->once())
            ->method('prepareAddProductBySku')
            ->with('testSku', 1, 'config')
            ->willReturn([]);
        $this->cartMock->expects($this->atLeastOnce())
            ->method('saveAffectedProducts')
            ->with($this->cartMock, false)
            ->willReturnSelf();

        $this->assertTrue($this->cart->addConfiguredItems($quote, $configuredItems));
    }

    /**
     * Test for addConfiguredItems method.
     *
     * @return void
     */
    public function testAddConfiguredItemsEmpty()
    {
        $configuredItems = [];
        $quote = $this->getMockBuilder(\Magento\Quote\Model\Quote::class)->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->cartFactory->expects($this->once())->method('create')->willReturn($this->cartMock);
        $this->cartMock->expects($this->once())->method('setQuote')->with($quote)->willReturnSelf();
        $this->cartMock->expects($this->once())
            ->method('setContext')
            ->with(\Magento\AdvancedCheckout\Model\Cart::CONTEXT_ADMIN_CHECKOUT)
            ->willReturnSelf();

        $this->assertFalse($this->cart->addConfiguredItems($quote, $configuredItems));
    }

    /**
     * Test for getDeletedItemsSku method.
     *
     * @return void
     */
    public function testGetDeletedItemsSku()
    {
        $this->cartFactory->expects($this->once())->method('create')->willReturn($this->cartMock);
        $this->cartMock->expects($this->once())->method('setSession')->with($this->sessionQuote)->willReturnSelf();
        $failedItems[] = [
            'item' => [
                'sku' => 'dummy'
            ],
            'code' => \Magento\AdvancedCheckout\Helper\Data::ADD_ITEM_STATUS_FAILED_SKU
        ];
        $this->cartMock->expects($this->once())->method('getFailedItems')->willReturn($failedItems);

        $this->assertEquals(['dummy'], $this->cart->getDeletedItemsSku());
    }
}
