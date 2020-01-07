<?php
namespace Magento\SharedCatalog\Test\Unit\Plugin\Catalog\Model\Product;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\SharedCatalog\Plugin\Catalog\Model\Product\AssignSharedCatalogOnDuplicateProductPlugin;

/**
 * Unit tests for AssignSharedCatalogOnDuplicateProductPlugin plugin.
 */
class AssignSharedCatalogOnDuplicateProductPluginTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var AssignSharedCatalogOnDuplicateProductPlugin|\PHPUnit_Framework_MockObject_MockObject
     */
    private $assignSharedCatalogOnDuplicateProductPlugin;

    /**
     * @var \Magento\SharedCatalog\Model\ProductSharedCatalogsLoader|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productSharedCatalogsLoaderMock;

    /**
     * @var \Magento\SharedCatalog\Api\ProductItemManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productItemManagementMock;

    /**
     * @var \Psr\Log\LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $loggerMock;

    /**
     * Set up.
     *
     * @return void
     */
    public function setUp()
    {
        $this->productSharedCatalogsLoaderMock = $this->getMockBuilder(
            \Magento\SharedCatalog\Model\ProductSharedCatalogsLoader::class
        )
            ->disableOriginalConstructor()
            ->getMock();
        $this->productItemManagementMock = $this->getMockBuilder(
            \Magento\SharedCatalog\Api\ProductItemManagementInterface::class
        )
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->loggerMock = $this->getMockBuilder(\Psr\Log\LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->assignSharedCatalogOnDuplicateProductPlugin = $this->objectManagerHelper->getObject(
            AssignSharedCatalogOnDuplicateProductPlugin::class,
            [
                'productSharedCatalogsLoader' => $this->productSharedCatalogsLoaderMock,
                'productItemManagement' => $this->productItemManagementMock,
                'logger' => $this->loggerMock
            ]
        );
    }

    /**
     * Test for aroundCopy() method.
     *
     * @return void
     */
    public function testAfterCopy()
    {
        $sku = 'sku';
        $customerGroupId = 1;

        $productMock = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->disableOriginalConstructor()
            ->getMock();
        $productMock->expects($this->atLeastOnce())->method('getSku')->willReturn($sku);
        $sharedCatalogMock = $this->getMockBuilder(\Magento\SharedCatalog\Api\Data\SharedCatalogInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->productSharedCatalogsLoaderMock->expects($this->once())->method('getAssignedSharedCatalogs')
            ->willReturn([$sharedCatalogMock]);
        $sharedCatalogMock->expects($this->atLeastOnce())->method('getCustomerGroupId')->willReturn($customerGroupId);

        $exception = new \Magento\Framework\Exception\LocalizedException(__('test'));
        $this->productItemManagementMock->expects($this->once())->method('addItems')->with($customerGroupId, [$sku])
            ->willThrowException($exception);
        $this->loggerMock->expects($this->once())->method('critical')->with($exception);

        $subject = $this->getMockBuilder(\Magento\Catalog\Model\Product\Copier::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->assertEquals(
            $productMock,
            $this->assignSharedCatalogOnDuplicateProductPlugin->afterCopy($subject, $productMock, $productMock)
        );
    }
}
