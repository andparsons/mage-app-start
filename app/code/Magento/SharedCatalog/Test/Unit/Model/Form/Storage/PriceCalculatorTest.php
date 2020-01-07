<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SharedCatalog\Test\Unit\Model\Form\Storage;

/**
 * Unit test for price calculator model.
 */
class PriceCalculatorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\SharedCatalog\Model\Form\Storage\WizardFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storageFactory;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productRepository;

    /**
     * @var \Magento\SharedCatalog\Model\Form\Storage\Wizard|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storage;

    /**
     * @var \Magento\SharedCatalog\Model\ProductItemTierPriceValidator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productItemTierPriceValidator;

    /**
     * @var \Magento\Framework\Locale\FormatInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $localeFormat;

    /**
     * @var \Magento\SharedCatalog\Model\Form\Storage\PriceCalculator
     */
    private $model;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->storageFactory = $this
            ->getMockBuilder(\Magento\SharedCatalog\Model\Form\Storage\WizardFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->productRepository = $this->getMockBuilder(
            \Magento\Catalog\Api\ProductRepositoryInterface::class
        )
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->storage = $this->getMockBuilder(\Magento\SharedCatalog\Model\Form\Storage\Wizard::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->productItemTierPriceValidator = $this->getMockBuilder(
            \Magento\SharedCatalog\Model\ProductItemTierPriceValidator::class
        )
            ->disableOriginalConstructor()
            ->getMock();
        $this->localeFormat = $this->getMockBuilder(\Magento\Framework\Locale\FormatInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $objectManager->getObject(
            \Magento\SharedCatalog\Model\Form\Storage\PriceCalculator::class,
            [
                'storageFactory' => $this->storageFactory,
                'productRepository' => $this->productRepository,
                'productItemTierPriceValidator' => $this->productItemTierPriceValidator,
                'localeFormat' => $this->localeFormat,
            ]
        );
    }

    /**
     * Test calculateNewPriceForProduct method.
     *
     * @param float $expectedResult
     * @param array $customPrice
     * @return void
     * @dataProvider calculateNewPriceForProductDataProvider
     */
    public function testCalculateNewPriceForProduct($expectedResult, array $customPrice)
    {
        $productId = 1;
        $oldPrice = 15;
        $this->storageFactory->expects($this->once())
            ->method('create')
            ->with(['key' => 'configure_key'])
            ->willReturn($this->storage);
        $this->storage->expects($this->once())->method('getProductPrices')->with($productId)->willReturn([]);
        $this->productItemTierPriceValidator->expects($this->once())
            ->method('existsPricePerWebsite')
            ->with([])
            ->willReturn(false);
        $this->storage->expects($this->once())->method('getProductPrice')->with($productId)->willReturn($customPrice);

        $this->assertEquals(
            $expectedResult,
            $this->model->calculateNewPriceForProduct('configure_key', $productId, $oldPrice)
        );
    }

    /**
     * Test calculateNewPriceForProduct method with fixed price.
     *
     * @return void
     */
    public function testCalculateNewPriceForProductWithFixedPrice()
    {
        $productId = 1;
        $oldPrice = 15;
        $customPrice = [
            'value_type' => 'fixed',
            'price' => 23,
        ];
        $this->storageFactory->expects($this->once())
            ->method('create')
            ->with(['key' => 'configure_key'])
            ->willReturn($this->storage);
        $this->storage->expects($this->once())->method('getProductPrices')->with($productId)->willReturn([]);
        $this->productItemTierPriceValidator->expects($this->once())
            ->method('existsPricePerWebsite')
            ->with([])
            ->willReturn(false);
        $this->storage->expects($this->once())->method('getProductPrice')->with($productId)->willReturn($customPrice);
        $this->localeFormat->expects($this->once())
            ->method('getNumber')
            ->with($customPrice['price'])
            ->willReturn(23);

        $this->assertEquals(
            23,
            $this->model->calculateNewPriceForProduct('configure_key', $productId, $oldPrice)
        );
    }

    /**
     * Test calculateNewPriceForProduct method when price already exists for the website.
     *
     * @return void
     */
    public function testCalculateNewPriceForProductPriceExists()
    {
        $productId = 1;
        $oldPrice = 15;
        $this->storageFactory->expects($this->once())
            ->method('create')
            ->with(['key' => 'configure_key'])
            ->willReturn($this->storage);
        $this->storage->expects($this->once())->method('getProductPrices')->with($productId)->willReturn([]);
        $this->productItemTierPriceValidator->expects($this->once())
            ->method('existsPricePerWebsite')
            ->with([])
            ->willReturn(true);
        $this->assertNull($this->model->calculateNewPriceForProduct('configure_key', $productId, $oldPrice));
    }

    /**
     * Data provider for calculateNewPriceForProduct method.
     *
     * @return array
     */
    public function calculateNewPriceForProductDataProvider()
    {
        return [
            [
                14.25,
                [
                    'value_type' => 'percent',
                    'percentage_value' => 5,
                ]
            ],
            [
                15,
                []
            ]
        ];
    }
}
