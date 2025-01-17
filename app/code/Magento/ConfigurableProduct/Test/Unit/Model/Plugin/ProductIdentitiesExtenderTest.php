<?php
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Test\Unit\Model\Plugin;

use Magento\ConfigurableProduct\Model\Plugin\ProductIdentitiesExtender;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;

/**
 * Class ProductIdentitiesExtenderTest
 */
class ProductIdentitiesExtenderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|Configurable
     */
    private $configurableTypeMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ProductRepositoryInterface
     */
    private $productRepositoryMock;

    /**
     * @var ProductIdentitiesExtender
     */
    private $plugin;

    protected function setUp()
    {
        $this->configurableTypeMock = $this->getMockBuilder(Configurable::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->productRepositoryMock = $this->getMockBuilder(ProductRepositoryInterface::class)
            ->getMock();

        $this->plugin = new ProductIdentitiesExtender($this->configurableTypeMock, $this->productRepositoryMock);
    }

    public function testAfterGetIdentities()
    {
        $productId = 1;
        $productIdentity = 'cache_tag_1';
        $productMock = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();
        $parentProductId = 2;
        $parentProductIdentity = 'cache_tag_2';
        $parentProductMock = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();

        $productMock->expects($this->once())
            ->method('getId')
            ->willReturn($productId);
        $this->configurableTypeMock->expects($this->once())
            ->method('getParentIdsByChild')
            ->with($productId)
            ->willReturn([$parentProductId]);
        $this->productRepositoryMock->expects($this->once())
            ->method('getById')
            ->with($parentProductId)
            ->willReturn($parentProductMock);
        $parentProductMock->expects($this->once())
            ->method('getIdentities')
            ->willReturn([$parentProductIdentity]);

        $productIdentities = $this->plugin->afterGetIdentities($productMock, [$productIdentity]);
        $this->assertEquals([$productIdentity, $parentProductIdentity], $productIdentities);
    }
}
