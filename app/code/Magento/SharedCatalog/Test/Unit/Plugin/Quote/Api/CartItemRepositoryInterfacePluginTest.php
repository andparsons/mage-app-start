<?php

namespace Magento\SharedCatalog\Test\Unit\Plugin\Quote\Api;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use \Magento\SharedCatalog\Plugin\Quote\Api\CartItemRepositoryInterfacePlugin;

/**
 * Class CartItemRepositoryInterfacePluginTest
 */
class CartItemRepositoryInterfacePluginTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var CartItemRepositoryInterfacePlugin|\PHPUnit_Framework_MockObject_MockObject
     */
    private $cartItemRepositoryInterfacePlugin;

    /**
     * @var \Magento\Quote\Api\CartItemRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $cartItemRepository;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteRepositoryMock;

    /**
     * @var \Magento\Quote\Model\Quote|\PHPUnit_Framework_MockObject_MockObject
     */
    private $cart;

    /**
     * @var \Magento\Quote\Model\Quote\Item\CartItemOptionsProcessorFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $cartItemOptionsProcessorFactoryMock;

    /**
     * Set up
     */
    public function setUp()
    {
        $this->quoteRepositoryMock = $this->getMockBuilder(\Magento\Quote\Api\CartRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->cartItemOptionsProcessorFactoryMock =
            $this->createPartialMock(
                \Magento\Quote\Model\Quote\Item\CartItemOptionsProcessorFactory::class,
                ['create']
            );
        $this->cartItemRepository = $this->getMockBuilder(\Magento\Quote\Api\CartItemRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->cart = $this->getMockBuilder(\Magento\Quote\Model\Quote::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->cartItemRepositoryInterfacePlugin = $this->objectManagerHelper->getObject(
            \Magento\SharedCatalog\Plugin\Quote\Api\CartItemRepositoryInterfacePlugin::class,
            [
                'quoteRepository' => $this->quoteRepositoryMock,
                'cartItemOptionsProcessorFactory' => $this->cartItemOptionsProcessorFactoryMock
            ]
        );
    }

    /**
     * Test for method aroundGetList
     */
    public function testAroundGetList()
    {
        $quoteItem = $this->createMock(\Magento\Quote\Model\Quote\Item::class);
        $this->cart->method('getAllVisibleItems')->willReturn([$quoteItem]);
        $this->quoteRepositoryMock->method('get')->willReturn($this->cart);
        $closure = function () {
            return;
        };
        $processor = $this->getMockBuilder(\Magento\Quote\Model\Quote\Item\CartItemOptionsProcessor::class)
            ->disableOriginalConstructor()
            ->getMock();
        $processor->method('addProductOptions')->willReturn($quoteItem);
        $processor->method('applyCustomOptions')->willReturn($quoteItem);
        $this->cartItemOptionsProcessorFactoryMock->method('create')->willReturn($processor);
        $result =
            $this->cartItemRepositoryInterfacePlugin->aroundGetList($this->cartItemRepository, $closure, 1);
        $this->assertEquals([$quoteItem], $result);
    }

    /**
     * Test for method aroundGetList
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     */
    public function testAroundGetListQuoteNotFound()
    {
        $e = new \Magento\Framework\Exception\NoSuchEntityException();
        $this->quoteRepositoryMock->method('get')->willThrowException($e);
        $closure = function () {
            return;
        };
        $this->cartItemRepositoryInterfacePlugin->aroundGetList($this->cartItemRepository, $closure, -1);
    }
}
