<?php

namespace Magento\SharedCatalog\Test\Unit\Plugin\Catalog\Api;

use Magento\SharedCatalog\Plugin\Catalog\Api\AdminProductRepositoryInterfacePlugin;

/**
 * Unit test for Magento\SharedCatalog\Plugin\Catalog\Api\AdminProductRepositoryInterfacePlugin class.
 */
class AdminProductRepositoryInterfacePluginTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $searchCriteriaBuilderMock;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productRepositoryMock;

    /**
     * @var \Magento\SharedCatalog\Api\ProductItemRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $sharedCatalogProductItemRepository;

    /**
     * @var \Magento\SharedCatalog\Api\Data\ProductItemInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productItem;

    /**
     * @var \Magento\Catalog\Api\Data\ProductInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $product;

    /**
     * @var AdminProductRepositoryInterfacePlugin
     */
    private $plugin;

    /**
     * @inheritdoc
     */
    public function setUp()
    {
        $this->searchCriteriaBuilderMock = $this->getMockBuilder(\Magento\Framework\Api\SearchCriteriaBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->productRepositoryMock = $this->getMockBuilder(\Magento\Catalog\Api\ProductRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->sharedCatalogProductItemRepository = $this->getMockBuilder(
            \Magento\SharedCatalog\Api\ProductItemRepositoryInterface::class
        )
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $searchCriteria = $this->getMockBuilder(\Magento\Framework\Api\SearchCriteria::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->product = $this->getMockBuilder(\Magento\Catalog\Api\Data\ProductInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $searchResult = $this->getMockBuilder(\Magento\SharedCatalog\Api\Data\ProductItemSearchResultsInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->productItem = $this->getMockBuilder(\Magento\SharedCatalog\Api\Data\ProductItemInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->product->expects($this->once())->method('getSku')->willReturn('sku');
        $this->searchCriteriaBuilderMock->expects($this->once())->method('create')->willReturn($searchCriteria);
        $this->searchCriteriaBuilderMock->expects($this->once())
            ->method('addFilter')
            ->with(\Magento\SharedCatalog\Api\Data\ProductItemInterface::SKU, 'sku');
        $this->sharedCatalogProductItemRepository->expects($this->once())
            ->method('getList')
            ->with($searchCriteria)
            ->willReturn($searchResult);
        $searchResult->expects($this->once())->method('getItems')->willReturn([$this->productItem]);
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->plugin = $objectManagerHelper->getObject(
            \Magento\SharedCatalog\Plugin\Catalog\Api\AdminProductRepositoryInterfacePlugin::class,
            [
                'searchCriteriaBuilder' => $this->searchCriteriaBuilderMock,
                'sharedCatalogProductItemRepository' => $this->sharedCatalogProductItemRepository
            ]
        );
    }

    /**
     * Test aroundDelete method.
     *
     * @return void
     */
    public function testAroundDelete()
    {
        $closure = function () {
            return;
        };
        $this->sharedCatalogProductItemRepository->expects($this->once())
            ->method('delete')->with($this->productItem)
            ->willReturn(true);

        $this->plugin->aroundDelete($this->productRepositoryMock, $closure, $this->product);
    }

    /**
     * Test aroundDelete method throws Magento\Framework\Exception\StateException exception.
     *
     * @return void
     * @expectedException \Magento\Framework\Exception\StateException
     * @expectedExceptionMessage Some internal exception message.
     */
    public function testAroundDeleteFailed()
    {
        $closure = function () {
            return;
        };
        $this->sharedCatalogProductItemRepository->expects($this->once())
            ->method('delete')
            ->willThrowException(
                new \Magento\Framework\Exception\StateException(__('Some internal exception message.'))
            );

        $this->plugin->aroundDelete($this->productRepositoryMock, $closure, $this->product);
    }
}
