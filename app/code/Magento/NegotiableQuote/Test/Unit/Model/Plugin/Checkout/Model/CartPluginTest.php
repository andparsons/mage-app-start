<?php

namespace Magento\NegotiableQuote\Test\Unit\Model\Plugin\Checkout\Model;

use Magento\Catalog\Api\ProductRepositoryInterface;

/**
 * Test for \Magento\NegotiableQuote\Model\Plugin\Checkout\Model\CartPlugin class.
 */
class CartPluginTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\Message\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $messageManager;

    /**
     * @var ProductRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $productRepository;

    /**
     * @var \Magento\NegotiableQuote\Model\Plugin\Checkout\Model\CartPlugin
     */
    protected $plugin;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->storeManager = $this->getMockForAbstractClass(\Magento\Store\Model\StoreManagerInterface::class);
        $store = new \Magento\Framework\DataObject(['id' => 1]);
        $this->storeManager->expects($this->any())->method('getStore')->will($this->returnValue($store));
        $this->productRepository = $this->getMockForAbstractClass(ProductRepositoryInterface::class);
        $this->messageManager = $this->getMockForAbstractClass(\Magento\Framework\Message\ManagerInterface::class);

        $this->plugin = new \Magento\NegotiableQuote\Model\Plugin\Checkout\Model\CartPlugin(
            $this->storeManager,
            $this->productRepository,
            $this->messageManager
        );
    }

    /**
     * Test for aroundAddOrderItem.
     *
     * @param mixed $productData
     * @param mixed $orderItemData
     * @param boolean $qtyFlag
     * @param int $expectedResult
     * @return void
     * @dataProvider invisibleItems
     */
    public function testAroundAddOrderItem($productData, $orderItemData, $qtyFlag, $expectedResult)
    {
        $cart = $this->createPartialMock(\Magento\Checkout\Model\Cart::class, ['addProduct']);
        $orderItem = $this->createPartialMock(\Magento\Sales\Model\Order\Item::class, []);
        $orderItem->setData($orderItemData);
        $product = $this->createPartialMock(\Magento\Catalog\Model\Product::class, []);
        $product->setData($productData);

        $getByIdMethod = $this->productRepository->expects($this->any())->method('getById');
        if ($product->getId() == null) {
            $getByIdMethod->willThrowException(new \Magento\Framework\Exception\NoSuchEntityException());
        } else {
            $getByIdMethod->will($this->returnValue($product));
        }

        $messages = new \Magento\Framework\DataObject(['messages_count' => 0]);
        $addErrorCallback = $this->returnCallback(function () use ($messages) {
            $messages->setMessagesCount($messages->getMessagesCount() + 1);
            return $this;
        });

        $proceed = function () {
            return;
        };
        $this->messageManager->expects($this->any())->method('addError')->will($addErrorCallback);

        $this->plugin->aroundAddOrderItem($cart, $proceed, $orderItem, $qtyFlag);

        $this->assertEquals($expectedResult, $messages->getMessagesCount());
    }

    /**
     * Data provider for testAroundAddOrderItem.
     *
     * @return array
     */
    public function invisibleItems()
    {
        return [
            [
                [
                    'entity_id' => 5,
                    'status' => \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_DISABLED
                ],
                [
                    'product_id' => 5,
                    'product_options' => [ 'info_buyRequest' => [] ],
                    'qty_ordered' => 2,
                    'sku' => 'test'
                ],
                false,
                1
            ],
            [
                [
                    'entity_id' => 5,
                    'status' => \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED
                ],
                [
                    'product_id' => 5,
                    'product_options' => [ 'info_buyRequest' => [] ],
                    'qty_ordered' => 2,
                    'sku' => 'test'
                ],
                true,
                0
            ],
            [
                null,
                [
                    'product_id' => 5,
                    'product_options' => [ 'info_buyRequest' => [] ],
                    'qty_ordered' => 2,
                    'sku' => 'test'
                ],
                true,
                1
            ],
            [
                [
                    'entity_id' => 5,
                    'status' => \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED
                ],
                [
                    'product_id' => 5,
                    'product_options' => [ 'info_buyRequest' => [] ],
                    'qty_ordered' => 2,
                    'sku' => 'test'
                ],
                null,
                0
            ]
        ];
    }
}
