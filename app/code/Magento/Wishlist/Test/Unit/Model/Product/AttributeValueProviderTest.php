<?php
declare(strict_types=1);

namespace Magento\Wishlist\Test\Unit\Model\Product;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Wishlist\Model\Product\AttributeValueProvider;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject;

/**
 * AttributeValueProviderTest
 */
class AttributeValueProviderTest extends TestCase
{
    /**
     * @var AttributeValueProvider|PHPUnit_Framework_MockObject_MockObject
     */
    private $attributeValueProvider;

    /**
     * @var CollectionFactory|PHPUnit_Framework_MockObject_MockObject
     */
    private $productCollectionFactoryMock;

    /**
     * @var Product|PHPUnit_Framework_MockObject_MockObject
     */
    private $productMock;

    /**
     * @var AdapterInterface|PHPUnit_Framework_MockObject_MockObject
     */
    private $connectionMock;

    /**
     * Set Up
     *
     * @return void
     */
    protected function setUp()
    {
        $this->productCollectionFactoryMock = $this->createPartialMock(
            CollectionFactory::class,
            ['create']
        );
        $this->attributeValueProvider = new AttributeValueProvider(
            $this->productCollectionFactoryMock
        );
    }

    /**
     * Get attribute text when the flat table is disabled
     *
     * @param int $productId
     * @param string $attributeCode
     * @param string $attributeText
     * @return void
     * @dataProvider attributeDataProvider
     */
    public function testGetAttributeTextWhenFlatIsDisabled(int $productId, string $attributeCode, string $attributeText)
    {
        $this->productMock = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->setMethods(['getData'])
            ->getMock();

        $this->productMock->expects($this->any())
            ->method('getData')
            ->with($attributeCode)
            ->willReturn($attributeText);

        $productCollection = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'addIdFilter', 'addStoreFilter', 'addAttributeToSelect', 'isEnabledFlat', 'getFirstItem'
            ])->getMock();

        $productCollection->expects($this->any())
            ->method('addIdFilter')
            ->willReturnSelf();
        $productCollection->expects($this->any())
            ->method('addStoreFilter')
            ->willReturnSelf();
        $productCollection->expects($this->any())
            ->method('addAttributeToSelect')
            ->willReturnSelf();
        $productCollection->expects($this->any())
            ->method('isEnabledFlat')
            ->willReturn(false);
        $productCollection->expects($this->any())
            ->method('getFirstItem')
            ->willReturn($this->productMock);

        $this->productCollectionFactoryMock->expects($this->atLeastOnce())
            ->method('create')
            ->willReturn($productCollection);

        $actual = $this->attributeValueProvider->getRawAttributeValue($productId, $attributeCode);

        $this->assertEquals($attributeText, $actual);
    }

    /**
     * Get attribute text when the flat table is enabled
     *
     * @dataProvider attributeDataProvider
     * @param int $productId
     * @param string $attributeCode
     * @param string $attributeText
     * @return void
     */
    public function testGetAttributeTextWhenFlatIsEnabled(int $productId, string $attributeCode, string $attributeText)
    {
        $this->connectionMock = $this->getMockBuilder(AdapterInterface::class)->getMockForAbstractClass();
        $this->connectionMock->expects($this->any())
            ->method('fetchRow')
            ->willReturn([
                $attributeCode => $attributeText
            ]);
        $this->productMock = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->setMethods(['getData'])
            ->getMock();
        $this->productMock->expects($this->any())
            ->method('getData')
            ->with($attributeCode)
            ->willReturn($attributeText);

        $productCollection = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'addIdFilter', 'addStoreFilter', 'addAttributeToSelect', 'isEnabledFlat', 'getConnection'
            ])->getMock();

        $productCollection->expects($this->any())
            ->method('addIdFilter')
            ->willReturnSelf();
        $productCollection->expects($this->any())
            ->method('addStoreFilter')
            ->willReturnSelf();
        $productCollection->expects($this->any())
            ->method('addAttributeToSelect')
            ->willReturnSelf();
        $productCollection->expects($this->any())
            ->method('isEnabledFlat')
            ->willReturn(true);
        $productCollection->expects($this->any())
            ->method('getConnection')
            ->willReturn($this->connectionMock);

        $this->productCollectionFactoryMock->expects($this->atLeastOnce())
            ->method('create')
            ->willReturn($productCollection);

        $actual = $this->attributeValueProvider->getRawAttributeValue($productId, $attributeCode);

        $this->assertEquals($attributeText, $actual);
    }

    /**
     * @return array
     */
    public function attributeDataProvider(): array
    {
        return [
            [1, 'attribute_code', 'Attribute Text']
        ];
    }
}
