<?php
namespace Magento\SharedCatalog\Test\Unit\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\SharedCatalog\Api\Data\SharedCatalogInterface;
use Magento\SharedCatalog\Model\ProductSharedCatalogsLoader;
use Magento\SharedCatalog\Ui\DataProvider\Product\Form\Modifier\SharedCatalog;

class SharedCatalogTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var LocatorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $locator;

    /**
     * @var ProductSharedCatalogsLoader|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productSharedCatalogsLoader;

    /**
     * @var SharedCatalog
     */
    private $sharedCatalog;

    protected function setUp()
    {
        $this->locator = $this->createMock(LocatorInterface::class);
        $this->productSharedCatalogsLoader = $this->createMock(ProductSharedCatalogsLoader::class);

        $this->sharedCatalog = (new ObjectManager($this))->getObject(SharedCatalog::class, [
            'locator' => $this->locator,
            'productSharedCatalogsLoader' => $this->productSharedCatalogsLoader,
        ]);
    }

    public function testModifyData()
    {
        $productId = 2;
        $sku = 'sku';
        $sharedCatalogIdFirst = 3;
        $sharedCatalogIdSecond = 4;

        $product = $this->createMock(ProductInterface::class);
        $product->expects($this->atLeastOnce())->method('getId')->willReturn($productId);
        $product->expects($this->atLeastOnce())->method('getSku')->willReturn($sku);

        $sharedCatalogFirst = $this->createMock(SharedCatalogInterface::class);
        $sharedCatalogFirst->expects($this->atLeastOnce())->method('getId')->willReturn($sharedCatalogIdFirst);
        $sharedCatalogSecond = $this->createMock(SharedCatalogInterface::class);
        $sharedCatalogSecond->expects($this->atLeastOnce())->method('getId')->willReturn($sharedCatalogIdSecond);
        $sharedCatalogs = [$sharedCatalogFirst, $sharedCatalogSecond];

        $this->locator->expects($this->once())->method('getProduct')->willReturn($product);
        $this->productSharedCatalogsLoader
            ->expects($this->once())
            ->method('getAssignedSharedCatalogs')
            ->with($sku)
            ->willReturn($sharedCatalogs);

        $expectedResult = [
            $productId => [
                SharedCatalog::DATA_SOURCE_DEFAULT => [
                    'shared_catalog' => [
                        $sharedCatalogIdFirst,
                        $sharedCatalogIdSecond,
                    ],
                ],
            ],
        ];
        $this->assertEquals($expectedResult, $this->sharedCatalog->modifyData([]));
    }

    public function testModifyMeta()
    {
        $data = ['data'];
        $this->assertEquals($data, $this->sharedCatalog->modifyMeta($data));
    }
}
