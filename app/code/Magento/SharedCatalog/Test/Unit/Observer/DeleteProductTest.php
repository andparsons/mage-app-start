<?php

namespace Magento\SharedCatalog\Test\Unit\Observer;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Class DeleteProductTest
 */
class DeleteProductTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var \Magento\SharedCatalog\Observer\DeleteProduct|\PHPUnit_Framework_MockObject_MockObject
     */
    private $deleteProduct;

    /**
     * @var \Magento\SharedCatalog\Api\ProductItemRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $itemRepositoryMock;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $searchCriteriaBuilderMock;

    /**
     * @return void
     */
    public function setUp()
    {
        $this->itemRepositoryMock =
            $this->getMockBuilder(\Magento\SharedCatalog\Api\ProductItemRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->itemRepositoryMock = $this->getMockForAbstractClass(
            \Magento\SharedCatalog\Api\ProductItemRepositoryInterface::class,
            [],
            '',
            false,
            false,
            false,
            ['getList', 'getItems', 'delete']
        );
        $this->itemRepositoryMock->method('getList')->willReturn($this->itemRepositoryMock);
        $productItem = $this->createMock(\Magento\SharedCatalog\Api\Data\ProductItemInterface::class);
        $this->itemRepositoryMock->method('getItems')->willReturn([$productItem]);
        $this->searchCriteriaBuilderMock = $this->createPartialMock(
            \Magento\Framework\Api\SearchCriteriaBuilder::class,
            ['create', 'addFilter']
        );
        $searchCriteria = $this->createMock(\Magento\Framework\Api\SearchCriteria::class);
        $this->searchCriteriaBuilderMock->method('create')->willReturn($searchCriteria);
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->deleteProduct = $this->objectManagerHelper->getObject(
            \Magento\SharedCatalog\Observer\DeleteProduct::class,
            [
                'itemRepository' => $this->itemRepositoryMock,
                'searchCriteriaBuilder' => $this->searchCriteriaBuilderMock
            ]
        );
    }

    /**
     * @return void
     */
    public function testExecute()
    {
        $observer = $this->getMockBuilder(\Magento\Framework\Event\Observer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $product = $this->createPartialMock(\Magento\Catalog\Model\Product::class, ['getSku']);
        $product->method('getSku')->willReturn('sku1');
        $event = $this->createPartialMock(\Magento\Framework\Event::class, ['getProduct']);
        $event->method('getProduct')->willReturn($product);
        $observer->method('getEvent')->willReturn($event);
        $result = $this->deleteProduct->execute($observer);
        $this->assertEquals($this->deleteProduct, $result);
    }
}
