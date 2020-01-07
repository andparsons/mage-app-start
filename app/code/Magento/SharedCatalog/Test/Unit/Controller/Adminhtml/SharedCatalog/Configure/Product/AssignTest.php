<?php

namespace Magento\SharedCatalog\Test\Unit\Controller\Adminhtml\SharedCatalog\Configure\Product;

/**
 * Unit test for product assign controller.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AssignTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\SharedCatalog\Model\Form\Storage\WizardFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $wizardStorageFactory;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productRepository;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $request;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultJsonFactory;

    /**
     * @var \Magento\SharedCatalog\Model\Price\ProductTierPriceLoader|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productTierPriceLoader;

    /**
     * @var \Magento\SharedCatalog\Controller\Adminhtml\SharedCatalog\Configure\Product\Assign
     */
    private $assign;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->wizardStorageFactory = $this->getMockBuilder(
            \Magento\SharedCatalog\Model\Form\Storage\WizardFactory::class
        )
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->productRepository = $this->getMockBuilder(
            \Magento\Catalog\Api\ProductRepositoryInterface::class
        )
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->productTierPriceLoader = $this
            ->getMockBuilder(\Magento\SharedCatalog\Model\Price\ProductTierPriceLoader::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->request = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->resultJsonFactory = $this->getMockBuilder(
            \Magento\Framework\Controller\Result\JsonFactory::class
        )
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->assign = $objectManager->getObject(
            \Magento\SharedCatalog\Controller\Adminhtml\SharedCatalog\Configure\Product\Assign::class,
            [
                'wizardStorageFactory' => $this->wizardStorageFactory,
                'productRepository' => $this->productRepository,
                'productTierPriceLoader' => $this->productTierPriceLoader,
                'request' => $this->request,
                'resultJsonFactory' => $this->resultJsonFactory,
            ]
        );
    }

    /**
     * Test for method execute.
     *
     * @param bool $isAssign
     * @param int $assignInvocationsCount
     * @param int $unassignInvocationsCount
     * @return void
     * @dataProvider executeDataProvider
     */
    public function testExecute(
        $isAssign,
        $assignInvocationsCount,
        $unassignInvocationsCount
    ) {
        $configurationKey = 'configuration_key';
        $productId = 1;
        $productSku = 'ProductSKU';
        $categoryIds = [2, 3];
        $sharedCatalogId = 4;
        $this->request->expects($this->atLeastOnce())
            ->method('getParam')
            ->withConsecutive(['configure_key'], ['product_id'], ['is_assign'], ['shared_catalog_id'])
            ->willReturnOnConsecutiveCalls($configurationKey, $productId, $isAssign, $sharedCatalogId);
        $storage = $this->getMockBuilder(\Magento\SharedCatalog\Model\Form\Storage\Wizard::class)
            ->disableOriginalConstructor()
            ->getMock();
        $result = $this->getMockBuilder(\Magento\Framework\Controller\Result\Json::class)
            ->disableOriginalConstructor()
            ->getMock();
        $product = $this->getMockBuilder(\Magento\Catalog\Api\Data\ProductInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCategoryIds'])
            ->getMockForAbstractClass();
        $this->wizardStorageFactory->expects($this->once())
            ->method('create')
            ->with(['key' => $configurationKey])
            ->willReturn($storage);
        $this->productRepository->expects($this->once())
            ->method('getById')->with($productId)->willReturn($product);
        $product->expects($this->exactly($assignInvocationsCount))->method('getCategoryIds')->willReturn($categoryIds);
        $product->expects($this->atLeastOnce())->method('getSku')->willReturn($productSku);
        $storage->expects($this->exactly($assignInvocationsCount))
            ->method('assignProducts')->with([$productSku])->willReturnSelf();
        $storage->expects($this->exactly($assignInvocationsCount))
            ->method('assignCategories')->with($categoryIds)->willReturnSelf();
        $storage->expects($this->exactly($unassignInvocationsCount))
            ->method('unassignProducts')->with([$productSku])->willReturnSelf();
        $this->productTierPriceLoader->expects($this->once())
            ->method('populateProductTierPrices')
            ->with([$product], $sharedCatalogId, $storage)
            ->willReturnSelf();

        $this->resultJsonFactory->expects($this->once())->method('create')->willReturn($result);
        $storage->expects($this->once())->method('isProductAssigned')->with($productSku)->willReturn($isAssign);
        $result->expects($this->once())->method('setJsonData')->with(
            json_encode(
                [
                    'data'  => [
                        'status' => 1,
                        'product' => $productId,
                        'is_assign' => $isAssign
                    ]
                ]
            )
        )->willReturnSelf();
        $this->assertEquals($result, $this->assign->execute());
    }

    /**
     * Data provider for testExecute.
     *
     * @return array
     */
    public function executeDataProvider()
    {
        return [
            [true, 1, 0],
            [false, 0, 1]
        ];
    }
}
