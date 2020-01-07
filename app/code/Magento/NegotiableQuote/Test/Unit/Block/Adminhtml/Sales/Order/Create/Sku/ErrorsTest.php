<?php

namespace Magento\NegotiableQuote\Test\Unit\Block\Adminhtml\Sales\Order\Create\Sku;

/**
 * Class ErrorsTest
 */
class ErrorsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\NegotiableQuote\Block\Adminhtml\Sales\Order\Create\Sku\Errors
     */
    private $block;

    /**
     * @var \Magento\Store\Api\Data\StoreInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $store;

    /**
     * @var \Magento\AdvancedCheckout\Model\Cart|\PHPUnit_Framework_MockObject_MockObject
     */
    private $cart;

    /**
     * @var \Magento\AdvancedCheckout\Model\CartFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $cartFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManager;

    /**
     * @var \Magento\Framework\Session\SessionManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $session;

    /**
     * Set up
     *
     * @return void
     */
    protected function setUp()
    {
        $this->session = $this->getMockForAbstractClass(
            \Magento\Framework\Session\SessionManagerInterface::class,
            [],
            '',
            false,
            false,
            true,
            ['getStoreId']
        );
        $this->cart = $this->createPartialMock(
            \Magento\AdvancedCheckout\Model\Cart::class,
            ['setSession', 'getSession', 'getAffectedItems']
        );
        $this->cart->expects($this->once())
            ->method('setSession')
            ->with($this->session)
            ->willReturnSelf();
        $this->cart->expects($this->any())
            ->method('getSession')
            ->willReturn($this->session);
        $this->cartFactory =
            $this->createPartialMock(\Magento\AdvancedCheckout\Model\CartFactory::class, ['create']);
        $this->cartFactory->expects($this->any())->method('create')->willReturn($this->cart);
        $this->storeManager = $this->getMockForAbstractClass(
            \Magento\Store\Model\StoreManagerInterface::class,
            [],
            '',
            false,
            false,
            true,
            ['getStore']
        );
        $this->store = $this->getMockForAbstractClass(
            \Magento\Store\Api\Data\StoreInterface::class,
            [],
            '',
            false,
            false,
            true,
            []
        );
        $this->storeManager->expects($this->any())
            ->method('getStore')
            ->willReturn($this->store);
    }

    /**
     * Create test object instance
     *
     * @return void
     */
    private function createInstance()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->block = $objectManager->getObject(
            \Magento\NegotiableQuote\Block\Adminhtml\Sales\Order\Create\Sku\Errors::class,
            [
                'cartFactory' => $this->cartFactory,
                '_storeManager' => $this->storeManager,
                '_backendSession' => $this->session
            ]
        );

        $layout = $this->createMock(\Magento\Framework\View\LayoutInterface::class);
        $this->block->setLayout($layout);
    }

    /**
     * Test for getStore() method
     *
     * @return void
     */
    public function testGetStore()
    {
        $this->createInstance();
        $this->assertSame($this->store, $this->block->getStore());
    }

    /**
     * Test for getCart() method
     *
     * @return void
     */
    public function testGetCart()
    {
        $this->createInstance();
        $this->assertSame($this->cart, $this->block->getCart());
    }

    /**
     * Test for getFailedItems() method
     *
     * @return void
     */
    public function testGetFailedItems()
    {
        $successfulItem = [
            'code' => \Magento\AdvancedCheckout\Helper\Data::ADD_ITEM_STATUS_SUCCESS,
            'item' => ['item_data_2'],
        ];
        $this->createInstance();
        $this->cart->expects($this->once())->method('getAffectedItems')->willReturn(
            [
                $this->getFailedItem(),
                $successfulItem,
            ]
        );
        $this->assertEquals([$this->getFailedItem()], $this->block->getFailedItems());
    }

    /**
     * Test for getNumberOfFailed() method
     *
     * @return void
     */
    public function testGetNumberOfFailed()
    {
        $this->createInstance();
        $this->cart->expects($this->once())->method('getAffectedItems')->willReturn([$this->getFailedItem()]);
        $this->assertEquals(1, $this->block->getNumberOfFailed());
    }

    /**
     * Test for toHtml() method
     *
     * @return void
     */
    public function testToHtml()
    {
        $this->createInstance();
        $this->cart->expects($this->once())->method('getAffectedItems')->willReturn([$this->getFailedItem()]);
        $this->assertEquals('', $this->block->toHtml());
    }

    /**
     * Get failed item mock
     *
     * @return array
     */
    private function getFailedItem()
    {
        return [
            'code' => \Magento\AdvancedCheckout\Helper\Data::ADD_ITEM_STATUS_FAILED_SKU,
            'item' => ['item_data_1'],
        ];
    }

    /**
     * Test for toHtml() method without failed items
     *
     * @return void
     */
    public function testToHtmlWithoutFailedItems()
    {
        $this->createInstance();
        $this->cart->expects($this->once())->method('getAffectedItems')->willReturn([]);
        $this->assertEquals('', $this->block->toHtml());
    }
}
