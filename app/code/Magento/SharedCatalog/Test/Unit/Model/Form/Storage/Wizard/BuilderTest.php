<?php

namespace Magento\SharedCatalog\Test\Unit\Model\Form\Storage\Wizard;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Test for model Form\Storage\Wizard\Builder.
 */
class BuilderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var \Magento\SharedCatalog\Model\SharedCatalogProductsLoader|\PHPUnit_Framework_MockObject_MockObject
     */
    private $sharedCatalogProductsLoader;

    /**
     * @var \Magento\SharedCatalog\Model\Price\ProductTierPriceLoader|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productTierPriceLoader;

    /**
     * @var \Magento\SharedCatalog\Api\CategoryManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $sharedCatalogCategoryManagement;

    /**
     * @var \Magento\SharedCatalog\Model\Form\Storage\Wizard\Builder
     */
    private $builder;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->sharedCatalogProductsLoader = $this
            ->getMockBuilder(\Magento\SharedCatalog\Model\SharedCatalogProductsLoader::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->productTierPriceLoader = $this
            ->getMockBuilder(\Magento\SharedCatalog\Model\Price\ProductTierPriceLoader::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->sharedCatalogCategoryManagement = $this
            ->getMockBuilder(\Magento\SharedCatalog\Api\CategoryManagementInterface::class)
            ->setMethods(['getCategories'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->builder = $this->objectManagerHelper->getObject(
            \Magento\SharedCatalog\Model\Form\Storage\Wizard\Builder::class,
            [
                'sharedCatalogProductsLoader' => $this->sharedCatalogProductsLoader,
                'productTierPriceLoader' => $this->productTierPriceLoader,
                'sharedCatalogCategoryManagement' => $this->sharedCatalogCategoryManagement
            ]
        );
    }

    /**
     * Test for build().
     *
     * @return void
     */
    public function testBuild()
    {
        $categoryIds = [23];
        $customerGroupId = 23;
        $productSkus = ['sku_1', 'sku_2'];
        $wizardStorage = $this->getMockBuilder(\Magento\SharedCatalog\Model\Form\Storage\Wizard::class)
            ->setMethods(['assignProducts', 'assignCategories'])
            ->disableOriginalConstructor()
            ->getMock();
        $sharedCatalog = $this->getMockBuilder(\Magento\SharedCatalog\Api\Data\SharedCatalogInterface::class)
            ->setMethods(['getCustomerGroupId', 'getId'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $sharedCatalog->expects($this->once())->method('getCustomerGroupId')->willReturn($customerGroupId);
        $this->sharedCatalogProductsLoader->expects($this->once())
            ->method('getAssignedProductsSkus')
            ->with($customerGroupId)
            ->willReturn($productSkus);
        $this->productTierPriceLoader->expects($this->once())
            ->method('populateTierPrices')
            ->with($productSkus, 1, $wizardStorage);
        $wizardStorage->expects($this->once())->method('assignProducts')->with($productSkus)->willReturnSelf();
        $sharedCatalog->expects($this->atLeastOnce())->method('getId')->willReturn(1);
        $this->sharedCatalogCategoryManagement->expects($this->once())
            ->method('getCategories')
            ->willReturn($categoryIds);
        $wizardStorage->expects($this->once())->method('assignCategories')->with($categoryIds)->willReturnSelf();

        $this->assertEquals($wizardStorage, $this->builder->build($wizardStorage, $sharedCatalog));
    }
}
