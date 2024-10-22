<?php
namespace Magento\CatalogStaging\Test\Unit\Plugin\Catalog\Pricing\Render;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\CatalogStaging\Plugin\Catalog\Pricing\Render\PriceBox;
use Magento\Framework\EntityManager\EntityMetadata;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Pricing\Render\PriceBox as PriceBoxSubject;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Unit test for PriceBox plugin
 */
class PriceBoxTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var MetadataPool|\PHPUnit_Framework_MockObject_MockObject
     */
    private $metadataPool;

    /**
     * @var PriceBox
     */
    private $plugin;

    public function setUp()
    {
        $this->metadataPool = $this->getMockBuilder(MetadataPool::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->plugin = (new ObjectManager($this))->getObject(PriceBox::class, ['metadataPool' => $this->metadataPool]);
    }

    public function testAfterGetCacheKey()
    {
        $linkId = 2;
        $linkField = 'row_id';
        $saleableItem = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();
        $saleableItem->expects($this->once())
            ->method('getData')
            ->withConsecutive([$linkField])
            ->willReturn($linkId);

        /** @var PriceBoxSubject|\PHPUnit_Framework_MockObject_MockObject $subject */
        $subject = $this->getMockBuilder(PriceBoxSubject::class)
            ->disableOriginalConstructor()
            ->getMock();
        $subject->expects($this->once())
            ->method('getSaleableItem')
            ->willReturn($saleableItem);

        $entityMetadata = $this->getMockBuilder(EntityMetadata::class)
            ->setMethods(['getLinkField'])
            ->disableOriginalConstructor()
            ->getMock();
        $entityMetadata->expects($this->once())
            ->method('getLinkField')
            ->willReturn($linkField);

        $this->metadataPool->expects($this->once())
            ->method('getMetadata')
            ->withConsecutive([ProductInterface::class])
            ->willReturn($entityMetadata);

        $argumentResult = 'price-box-3';
        $expectedResult = "{$argumentResult}-{$linkId}";

        $result = $this->plugin->afterGetCacheKey($subject, $argumentResult);

        $this->assertEquals($expectedResult, $result);
    }
}
