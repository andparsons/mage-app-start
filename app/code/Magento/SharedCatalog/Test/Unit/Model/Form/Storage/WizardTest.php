<?php

namespace Magento\SharedCatalog\Test\Unit\Model\Form\Storage;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\SharedCatalog\Model\Form\Storage\Wizard;

/**
 * Unit test for model Form\Storage\Wizard.
 */
class WizardTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var Wizard
     */
    private $wizard;

    /**
     * @var \Magento\Framework\Session\Generic|\PHPUnit_Framework_MockObject_MockObject
     */
    private $session;

    /**
     * @var string
     */
    private $key = 'asdw23dcg3456745d435ff34545';

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->session = $this->getMockBuilder(\Magento\Framework\Session\Generic::class)
            ->setMethods(['getData', 'setData'])
            ->disableOriginalConstructor()->getMock();

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->wizard = $this->objectManagerHelper->getObject(
            Wizard::class,
            [
                'session' => $this->session,
                'key' => $this->key
            ]
        );
    }

    /**
     * Test for getAssignedProductSkus().
     *
     * @return void
     */
    public function testGetAssignedProductSkus()
    {
        $productSkus = ['sku_1', 'sku_2', 'sku_3'];
        $this->session->expects($this->once())->method('getData')->willReturn($productSkus);

        $this->assertEquals($productSkus, $this->wizard->getAssignedProductSkus());
    }

    /**
     * Test for setAssignedProductSkus().
     *
     * @return void
     */
    public function testSetAssignedProductSkus()
    {
        $productSkus = ['sku_1', 'sku_2', 'sku_3'];
        $this->session->expects($this->once())->method('setData')
            ->with($this->key . '_' . Wizard::SESSION_KEY_ASSIGNED_PRODUCT_SKUS, $productSkus)->willReturnSelf();

        $this->wizard->setAssignedProductSkus($productSkus);
    }

    /**
     * Test for getUnassignedProductSkus().
     *
     * @return void
     */
    public function testGetUnassignedProductSkus()
    {
        $unassignedProductSkus = ['sku_1', 'sku_2'];
        $this->session->expects($this->at(0))->method('getData')->willReturn($unassignedProductSkus);
        $assignedProductSkus = ['sku_1', 'sku_3'];
        $this->session->expects($this->at(1))->method('getData')->willReturn($assignedProductSkus);

        $this->assertEquals([1 => 'sku_2'], $this->wizard->getUnassignedProductSkus());
    }

    /**
     * Test for setAssignedProductSkus().
     *
     * @return void
     */
    public function testSetUnassignedProductSkus()
    {
        $productSkus = ['sku_1', 'sku_2', 'sku_3'];

        $expectedProductSkus = [0 => 'sku_1', 1 => 'sku_2', 2 => 'sku_3'];
        $this->session->expects($this->once())->method('setData')
            ->with($this->key . '_' . Wizard::SESSION_KEY_UNASSIGNED_PRODUCT_SKUS, $expectedProductSkus)
            ->willReturnSelf();

        $this->wizard->setUnassignedProductSkus($productSkus);
    }

    /**
     * Test for getAssignedCategoriesIds().
     *
     * @return void
     */
    public function testGetAssignedCategoriesIds()
    {
        $categoriesIds = [36, 36, 15];
        $this->session->expects($this->once())->method('getData')->willReturn($categoriesIds);

        $result = $this->wizard->getAssignedCategoriesIds();
        $this->assertEquals($categoriesIds, $result);
    }

    /**
     * Test for getUnassignedCategoriesIds().
     *
     * @return void
     */
    public function testGetUnassignedCategoriesIds()
    {
        $unassignedCategoriesIds = [25, 55];
        $unassignedCategoriesParamKey = $this->key . '_' . Wizard::SESSION_KEY_UNASSIGNED_CATEGORIES_IDS;
        $this->session->expects($this->at(0))->method('getData')->with($unassignedCategoriesParamKey)
            ->willReturn($unassignedCategoriesIds);

        $assignedCategoriesIds = [25, 23];
        $assignedCategoriesParamKey = $this->key . '_' . Wizard::SESSION_KEY_ASSIGNED_CATEGORIES_IDS;
        $this->session->expects($this->at(1))->method('getData')->with($assignedCategoriesParamKey)
            ->willReturn($assignedCategoriesIds);

        $expects = [1 => 55];
        $result = $this->wizard->getUnassignedCategoriesIds();
        $this->assertEquals($expects, $result);
    }

    /**
     * Test for assignProducts().
     *
     * @return void
     */
    public function testAssignProducts()
    {
        $productSkus = $expectedProductSkus = [0 => 'sku_1', 4 => 'sku_2', 5 => 'sku_3', 2 => 'sku_4'];

        $assignedProductSkus = ['sku_1', 'sku_1', 'sku_4'];
        $this->session->expects($this->once())->method('getData')->willReturn($assignedProductSkus);

        $this->session->expects($this->once())->method('setData')
            ->with($this->key . '_' . Wizard::SESSION_KEY_ASSIGNED_PRODUCT_SKUS, $expectedProductSkus)
            ->willReturnSelf();

        $this->wizard->assignProducts($productSkus);
    }

    /**
     * Test for unassignProducts().
     *
     * @return void
     */
    public function testUnassignProducts()
    {
        $unassignedProductSkus = ['sku_1', 'sku_2', 'sku_3'];
        $this->session->expects($this->at(0))->method('getData')->willReturn($unassignedProductSkus);
        $assignedProductSkus = ['sku_1', 'sku_4'];
        $this->session->expects($this->at(1))->method('getData')->willReturn($assignedProductSkus);

        $resultUnassignedProductSkus = ['sku_2', 'sku_3', 'sku_1'];
        $this->session->expects($this->at(2))->method('setData')
            ->with($this->key . '_' . Wizard::SESSION_KEY_UNASSIGNED_PRODUCT_SKUS, $resultUnassignedProductSkus)
            ->willReturnSelf();

        $assignedProductSkus = ['sku_6', 'sku_7', 'sku_8'];
        $this->session->expects($this->at(3))->method('getData')
            ->with($this->key . '_' . Wizard::SESSION_KEY_ASSIGNED_PRODUCT_SKUS)
            ->willReturn($assignedProductSkus);

        $resultAssignedProductSkus = ['sku_6', 'sku_7', 'sku_8'];
        $this->session->expects($this->at(4))->method('setData')
            ->with($this->key . '_' . Wizard::SESSION_KEY_ASSIGNED_PRODUCT_SKUS, $resultAssignedProductSkus)
            ->willReturnSelf();

        $this->wizard->unassignProducts($unassignedProductSkus);
    }

    /**
     * Test for assignCategories().
     *
     * @return void
     */
    public function testAssignCategories()
    {
        $categoriesIds = [36, 36, 15];

        $assignedCategoriesIds = [76, 76, 15];
        $this->session->expects($this->once())->method('getData')->willReturn($assignedCategoriesIds);

        $expectedCategoriesIds = [0 => 76, 2 => 15, 3 => 36];
        $this->session->expects($this->once())->method('setData')
            ->with($this->key . '_' . Wizard::SESSION_KEY_ASSIGNED_CATEGORIES_IDS, $expectedCategoriesIds)
            ->willReturnSelf();

        $this->wizard->assignCategories($categoriesIds);
    }

    /**
     * Test for unassignCategories().
     *
     * @return void
     */
    public function testUnassignCategories()
    {
        $categoriesIds = [16, 25, 8];

        $unassignedCategoriesIds = [25, 55];
        $unassignedCategoriesParamKey = $this->key . '_' . Wizard::SESSION_KEY_UNASSIGNED_CATEGORIES_IDS;
        $this->session->expects($this->at(0))->method('getData')->with($unassignedCategoriesParamKey)
            ->willReturn($unassignedCategoriesIds);

        $assignedCategoriesIds = [25, 23];
        $assignedCategoriesParamKey = $this->key . '_' . Wizard::SESSION_KEY_ASSIGNED_CATEGORIES_IDS;
        $this->session->expects($this->at(1))->method('getData')->with($assignedCategoriesParamKey)
            ->willReturn($assignedCategoriesIds);

        $resultUnassignedCategoriesIds = [55, 16, 25, 8];
        $this->session->expects($this->at(2))->method('setData')
            ->with($this->key . '_' . Wizard::SESSION_KEY_UNASSIGNED_CATEGORIES_IDS, $resultUnassignedCategoriesIds)
            ->willReturnSelf();

        $this->session->expects($this->at(3))->method('getData')->willReturn($assignedCategoriesIds);

        $resultAssignedCategoriesIds = [1 => 23];
        $this->session->expects($this->at(4))->method('setData')
            ->with($this->key . '_' . Wizard::SESSION_KEY_ASSIGNED_CATEGORIES_IDS, $resultAssignedCategoriesIds)
            ->willReturnSelf();

        $this->wizard->unassignCategories($categoriesIds);
    }

    /**
     * Test for isProductAssigned().
     *
     * @return void
     */
    public function testIsProductAssigned()
    {
        $productId = 34;

        $productIds = [34, 36, 15];
        $this->session->expects($this->once())->method('getData')->willReturn($productIds);

        $expected = true;
        $result = $this->wizard->isProductAssigned($productId);
        $this->assertEquals($expected, $result);
    }

    /**
     * Test for setTierPrices method.
     *
     * @return void
     */
    public function testSetTierPrices()
    {
        $tierPriceToAdd = ['sku_1' => [['qty' => 1, 'website_id' => 3]]];
        $presentTierPrice = ['sku_1' => [['qty' => 1, 'website_id' => 2]]];
        $this->session->expects($this->once())->method('getData')
            ->with($this->key . '_' . Wizard::SESSION_KEY_PRODUCT_TIER_PRICES)->willReturn($presentTierPrice);
        $this->session->expects($this->once())->method('setData')
            ->with(
                $this->key . '_' . Wizard::SESSION_KEY_PRODUCT_TIER_PRICES,
                ['sku_1' => [$presentTierPrice['sku_1'][0], $tierPriceToAdd['sku_1'][0]]]
            )->willReturnSelf();
        $this->wizard->setTierPrices($tierPriceToAdd);
    }

    /**
     * Test for deleteTierPrice method.
     *
     * @return void
     */
    public function testDeleteTierPrice()
    {
        $productId = 1;
        $tierPriceToDelete = ['qty' => 1, 'website_id' => 3, 'is_changed' => true, 'is_deleted' => true];
        $presentTierPrice = ['qty' => 1, 'website_id' => 2, 'is_changed' => true, 'is_deleted' => true];
        $sessionData = [$productId => [$presentTierPrice, $tierPriceToDelete]];
        $this->session->expects($this->exactly(2))->method('getData')
            ->with($this->key . '_' . Wizard::SESSION_KEY_PRODUCT_TIER_PRICES)->willReturn($sessionData);
        $this->session->expects($this->once())->method('setData')
            ->with(
                $this->key . '_' . Wizard::SESSION_KEY_PRODUCT_TIER_PRICES,
                [$productId => [$presentTierPrice, $tierPriceToDelete]]
            )->willReturnSelf();
        $this->wizard->deleteTierPrice($productId, $tierPriceToDelete['qty'], $tierPriceToDelete['website_id']);
    }

    /**
     * Test for deleteTierPrices method.
     *
     * @return void
     */
    public function testDeleteTierPrices()
    {
        $productId = 1;
        $sessionData = [$productId => [['qty' => 1, 'website_id' => 2, 'is_changed' => true, 'is_deleted' => true]]];
        $this->session->expects($this->atLeastOnce())->method('getData')
            ->with($this->key . '_' . Wizard::SESSION_KEY_PRODUCT_TIER_PRICES)->willReturn($sessionData);
        $this->session->expects($this->once())->method('setData')
            ->with(
                $this->key . '_' . Wizard::SESSION_KEY_PRODUCT_TIER_PRICES,
                $sessionData
            )->willReturnSelf();
        $this->wizard->deleteTierPrices($productId);
    }

    /**
     * Test for getProductPrice method.
     *
     * @return void
     */
    public function testGetProductPrice()
    {
        $productId = 1;
        $websiteId = 2;
        $sessionData = [$productId => [['qty' => 2, 'website_id' => 2], ['qty' => 1, 'website_id' => 2]]];
        $this->session->expects($this->once())->method('getData')
            ->with($this->key . '_' . Wizard::SESSION_KEY_PRODUCT_TIER_PRICES)->willReturn($sessionData);
        $this->assertEquals($sessionData[$productId][1], $this->wizard->getProductPrice($productId, $websiteId));
    }

    /**
     * Test for getProductPrices method.
     *
     * @return void
     */
    public function testGetProductPrices()
    {
        $productSku = 'sku_1';
        $sessionData = [$productSku => [['qty' => 2, 'website_id' => 2], ['qty' => 1, 'website_id' => 2]]];
        $this->session->expects($this->once())->method('getData')
            ->with($this->key . '_' . Wizard::SESSION_KEY_PRODUCT_TIER_PRICES)->willReturn($sessionData);
        $this->assertEquals([2 => $sessionData[$productSku][1]], $this->wizard->getProductPrices($productSku));
    }
}
