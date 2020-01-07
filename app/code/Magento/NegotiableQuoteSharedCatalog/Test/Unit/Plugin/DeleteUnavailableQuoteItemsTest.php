<?php

namespace Magento\NegotiableQuoteSharedCatalog\Test\Unit\Plugin;

/**
 * Unit test for DeleteUnavailableQuoteItems plugin.
 */
class DeleteUnavailableQuoteItemsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\NegotiableQuoteSharedCatalog\Model\QuoteManagement|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteManagement;

    /**
     * @var \Magento\SharedCatalog\Model\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $config;

    /**
     * @var \Magento\NegotiableQuoteSharedCatalog\Plugin\DeleteUnavailableQuoteItems
     */
    private $plugin;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->quoteManagement = $this
            ->getMockBuilder(\Magento\NegotiableQuoteSharedCatalog\Model\QuoteManagement::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->config = $this->getMockBuilder(\Magento\SharedCatalog\Model\Config::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->plugin = $objectManager->getObject(
            \Magento\NegotiableQuoteSharedCatalog\Plugin\DeleteUnavailableQuoteItems::class,
            [
                'quoteManagement' => $this->quoteManagement,
                'config' => $this->config,
            ]
        );
    }

    /**
     * Test for afterDelete method.
     *
     * @return void
     */
    public function testAfterDelete()
    {
        $productId = '3';
        $customerGroupId = 1;
        $storeIds = [1, 2];
        $productItemRepository = $this
            ->getMockBuilder(\Magento\SharedCatalog\Api\ProductItemRepositoryInterface::class)
            ->disableOriginalConstructor()->getMock();
        $productItem = $this->getMockBuilder(\Magento\SharedCatalog\Api\Data\ProductItemInterface::class)
            ->disableOriginalConstructor()->getMock();
        $productItem->expects($this->once())->method('getId')->willReturn($productId);
        $productItem->expects($this->once())->method('getCustomerGroupId')->willReturn($customerGroupId);
        $this->config->expects($this->atLeastOnce())->method('getActiveSharedCatalogStoreIds')->willReturn($storeIds);
        $this->quoteManagement->expects($this->once())
            ->method('deleteItems')
            ->with([$productId], $customerGroupId, $storeIds);

        $this->assertTrue($this->plugin->afterDelete($productItemRepository, true, $productItem));
    }

    /**
     * Test for afterDeleteItems method.
     *
     * @return void
     */
    public function testAfterDeleteItems()
    {
        $productIds = [3, 4];
        $customerGroupIds = [1, 2];
        $storeIds = [1, 2];
        $productItemRepository = $this
            ->getMockBuilder(\Magento\SharedCatalog\Api\ProductItemRepositoryInterface::class)
            ->disableOriginalConstructor()->getMock();
        $productItem = $this->getMockBuilder(\Magento\SharedCatalog\Api\Data\ProductItemInterface::class)
            ->disableOriginalConstructor()->getMock();
        $productItem->expects($this->atLeastOnce())->method('getId')
            ->willReturnOnConsecutiveCalls($productIds[0], $productIds[1]);
        $productItem->expects($this->atLeastOnce())->method('getCustomerGroupId')
            ->willReturnOnConsecutiveCalls($customerGroupIds[0], $customerGroupIds[1]);
        $this->config->expects($this->atLeastOnce())->method('getActiveSharedCatalogStoreIds')->willReturn($storeIds);
        $this->quoteManagement->expects($this->atLeastOnce())->method('deleteItems')
            ->withConsecutive(
                [[$productIds[0]], $customerGroupIds[0], $storeIds],
                [[$productIds[1]], $customerGroupIds[1], $storeIds]
            );

        $this->assertTrue(
            $this->plugin->afterDeleteItems($productItemRepository, true, [$productItem, $productItem])
        );
    }
}
