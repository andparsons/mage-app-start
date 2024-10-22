<?php
namespace Magento\CatalogStaging\Test\Unit\Model\Plugin\ResourceModel\Product\Collection;

use Magento\Catalog\Model\ResourceModel\Product\Collection\ProductLimitation;
use Magento\CatalogStaging\Model\Plugin\ResourceModel\Product\Collection\ProductLimitation as ProductLimitationPlugin;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Staging\Model\VersionManager;

/**
 * Class ProductLimitationTest
 */
class ProductLimitationTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ProductLimitationPlugin
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $versionManagerMock;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);

        $this->versionManagerMock = $this->getMockBuilder(VersionManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->model = $objectManager->getObject(
            ProductLimitationPlugin::class,
            [
                'versionManager' => $this->versionManagerMock
            ]
        );
    }

    public function testAfterIsUsingPriceIndex()
    {
        $collectionMock = $this->getMockBuilder(ProductLimitation::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->versionManagerMock->expects($this->once())->method('isPreviewVersion')->willReturn(true);
        $this->assertFalse(false, $this->model->afterIsUsingPriceIndex($collectionMock, true));
    }
}
