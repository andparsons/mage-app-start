<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SharedCatalog\Test\Unit\Controller\Adminhtml\SharedCatalog\Configure\Product\TierPrice;

/**
 * Unit test for tier price Save controller.
 */
class SaveTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\SharedCatalog\Model\Form\Storage\WizardFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $wizardStorageFactory;

    /**
     * @var \Magento\SharedCatalog\Model\ProductItemTierPriceValidator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productItemTierPriceValidator;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $request;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultJsonFactory;

    /**
     * @var \Magento\Framework\Locale\FormatInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $format;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productRepository;

    /**
     * @var \Magento\SharedCatalog\Controller\Adminhtml\SharedCatalog\Configure\Product\TierPrice\Save
     */
    private $controller;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->wizardStorageFactory = $this
            ->getMockBuilder(\Magento\SharedCatalog\Model\Form\Storage\WizardFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->productItemTierPriceValidator = $this
            ->getMockBuilder(\Magento\SharedCatalog\Model\ProductItemTierPriceValidator::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->request = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultJsonFactory = $this
            ->getMockBuilder(\Magento\Framework\Controller\Result\JsonFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->format = $this->getMockBuilder(\Magento\Framework\Locale\FormatInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->productRepository = $this->getMockBuilder(\Magento\Catalog\Api\ProductRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->controller = $objectManager->getObject(
            \Magento\SharedCatalog\Controller\Adminhtml\SharedCatalog\Configure\Product\TierPrice\Save::class,
            [
                '_request' => $this->request,
                'productItemTierPriceValidator' => $this->productItemTierPriceValidator,
                'wizardStorageFactory' => $this->wizardStorageFactory,
                'resultJsonFactory' => $this->resultJsonFactory,
                'format' => $this->format,
                'productRepository' => $this->productRepository,
            ]
        );
    }

    /**
     * Test for execute method.
     *
     * @return void
     */
    public function testExecute()
    {
        $tierPrices = [
            [
                'qty' => 1,
                'website_id' => 1,
                'value_type' => 'percent',
                'price' => 10,
                'percentage_value' => 5,
            ],
            [
                'delete' => true
            ]
        ];
        $productId = 1;
        $productSku = 'ProductSKU';
        $storage = $this
            ->getMockBuilder(\Magento\SharedCatalog\Model\Form\Storage\Wizard::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->request->expects($this->atLeastOnce())
            ->method('getParam')
            ->withConsecutive(['tier_price', []], ['product_id'], ['configure_key'])
            ->willReturnOnConsecutiveCalls($tierPrices, $productId, 'configure_key');
        $product = $this->getMockBuilder(\Magento\Catalog\Api\Data\ProductInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->productRepository->expects($this->once())
            ->method('getById')->with($productId)->willReturn($product);
        $this->productItemTierPriceValidator->expects($this->once())
            ->method('validateDuplicates')
            ->with($tierPrices)
            ->willReturn(true);
        $this->wizardStorageFactory->expects($this->once())
            ->method('create')
            ->with(['key' => 'configure_key'])
            ->willReturn($storage);
        $storage->expects($this->once())->method('deleteTierPrices')->with($productSku);
        $this->format->expects($this->exactly(4))
            ->method('getNumber')
            ->withConsecutive([1], [1], [10], [5])
            ->willReturnOnConsecutiveCalls(1, 1, 10, 5);
        $product->expects($this->atLeastOnce())->method('getSku')->willReturn($productSku);
        $storage->expects($this->once())
            ->method('setTierPrices')
            ->with(
                [
                    $productSku => [
                        [
                            'qty' => 1,
                            'website_id' => 1,
                            'value_type' => 'percent',
                            'is_changed' => true,
                            'price' => 10,
                            'percentage_value' => 5,
                        ],
                    ],
                ]
            );
        $json = $this->getMockBuilder(\Magento\Framework\Controller\Result\Json::class)
            ->disableOriginalConstructor()
            ->getMock();
        $result = ['data' => ['status' => 1]];
        $json->expects($this->once())
            ->method('setJsonData')
            ->with(json_encode($result, JSON_NUMERIC_CHECK))
            ->willReturnSelf();
        $this->resultJsonFactory->expects($this->once())->method('create')->willReturn($json);
        $this->assertEquals($json, $this->controller->execute());
    }

    /**
     * Test Execute method with duplicate tier prices.
     *
     * @return void
     */
    public function testExecuteWithInvalidPrice()
    {
        $tierPrices = [
            [
                'qty' => 1,
                'website_id' => 1,
                'value_type' => 'percent',
                'price' => 10,
                'percentage_value' => 5,
            ],
            [
                'delete' => true
            ]
        ];
        $productId = 1;
        $this->request->expects($this->atLeastOnce())
            ->method('getParam')
            ->withConsecutive(['tier_price', []], ['product_id'])
            ->willReturnOnConsecutiveCalls($tierPrices, $productId);
        $product = $this->getMockBuilder(\Magento\Catalog\Api\Data\ProductInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->productRepository->expects($this->once())
            ->method('getById')->with($productId)->willReturn($product);
        $this->productItemTierPriceValidator->expects($this->once())
            ->method('validateDuplicates')
            ->with($tierPrices)
            ->willReturn(false);
        $json = $this->getMockBuilder(\Magento\Framework\Controller\Result\Json::class)
            ->disableOriginalConstructor()
            ->getMock();
        $result = [
            'data' => ['status' => false, 'error' => __("We found a duplicate website, tier price or quantity.")]
        ];
        $json->expects($this->once())
            ->method('setJsonData')
            ->with(json_encode($result, JSON_NUMERIC_CHECK))
            ->willReturnSelf();
        $this->resultJsonFactory->expects($this->once())
            ->method('create')
            ->willReturn($json);
        $this->assertEquals($json, $this->controller->execute());
    }
}
