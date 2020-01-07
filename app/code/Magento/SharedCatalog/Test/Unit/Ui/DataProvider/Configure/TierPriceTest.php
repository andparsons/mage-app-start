<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SharedCatalog\Test\Unit\Ui\DataProvider\Configure;

/**
 * Unit test for TierPrice data provider.
 */
class TierPriceTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Ui\DataProvider\Modifier\PoolInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $modifiers;

    /**
     * @var \Magento\SharedCatalog\Model\Form\Storage\WizardFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $wizardStorageFactory;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $request;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productRepository;

    /**
     * @var \Magento\SharedCatalog\Ui\DataProvider\Configure\TierPrice
     */
    private $tierPriceDataProvider;

    /**
     * @var array
     */
    private $meta = ['meta_data'];

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->modifiers = $this
            ->getMockBuilder(\Magento\Ui\DataProvider\Modifier\PoolInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->wizardStorageFactory = $this
            ->getMockBuilder(\Magento\SharedCatalog\Model\Form\Storage\WizardFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()->getMock();
        $this->productRepository = $this
            ->getMockBuilder(\Magento\Catalog\Api\ProductRepositoryInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->request = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->disableOriginalConstructor()->getMock();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->tierPriceDataProvider =$objectManager->getObject(
            \Magento\SharedCatalog\Ui\DataProvider\Configure\TierPrice::class,
            [
                'request' => $this->request,
                'modifiers' => $this->modifiers,
                'wizardStorageFactory' => $this->wizardStorageFactory,
                'productRepository' => $this->productRepository,
                'meta' => $this->meta,
            ]
        );
    }

    /**
     * Test for getData method.
     *
     * @return void
     */
    public function testGetData()
    {
        $productId = 1;
        $productPrice = 15;
        $tierPrices = [
            [
                'qty' => 1,
                'website_id' => 0,
                'price' => 10,
                'price_type' => 'fixed',
                'sku' => 'product_sku',
            ]
        ];
        $sku = 'product_sku';
        $configureKey = 'configure_key_value';
        $expectedResult = ['modified_tier_prices'];
        $this->request->expects($this->exactly(2))->method('getParam')
            ->withConsecutive(['product_id'], ['configure_key'])
            ->willReturnOnConsecutiveCalls($productId, $configureKey);
        $product = $this->getMockBuilder(\Magento\Catalog\Api\Data\ProductInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->productRepository->expects($this->once())
            ->method('getById')->with($productId, false, 0, true)->willReturn($product);
        $storage = $this->getMockBuilder(\Magento\SharedCatalog\Model\Form\Storage\Wizard::class)
            ->disableOriginalConstructor()->getMock();
        $this->wizardStorageFactory->expects($this->once())
            ->method('create')->with(['key' => $configureKey])->willReturn($storage);
        $product->expects($this->once())->method('getSku')->willReturn($sku);
        $storage->expects($this->once())->method('getTierPrices')->with($sku)->willReturn($tierPrices);
        $product->expects($this->once())->method('getPrice')->willReturn($productPrice);
        $modifier = $this->getMockBuilder(\Magento\Ui\DataProvider\Modifier\ModifierInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->modifiers->expects($this->once())->method('getModifiersInstances')->willReturn([$modifier]);
        $modifier->expects($this->once())->method('modifyData')
            ->with(
                [
                    $productId => [
                        'product_id' => $productId,
                        'base_price' => $productPrice,
                        'tier_price' => $tierPrices,
                        'configure_key' => $configureKey,
                    ]
                ]
            )
            ->willReturn($expectedResult);
        $this->assertEquals($expectedResult, $this->tierPriceDataProvider->getData());
    }

    /**
     * Test for getMeta method.
     *
     * @return void
     */
    public function testGetMeta()
    {
        $expectedResult = ['meta_data_modified'];
        $modifier = $this->getMockBuilder(\Magento\Ui\DataProvider\Modifier\ModifierInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->modifiers->expects($this->once())->method('getModifiersInstances')->willReturn([$modifier]);
        $modifier->expects($this->once())->method('modifyMeta')->with($this->meta)->willReturn($expectedResult);
        $this->assertEquals($expectedResult, $this->tierPriceDataProvider->getMeta());
    }
}
