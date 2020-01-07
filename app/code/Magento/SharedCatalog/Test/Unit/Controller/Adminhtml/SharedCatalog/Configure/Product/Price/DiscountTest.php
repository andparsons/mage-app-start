<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SharedCatalog\Test\Unit\Controller\Adminhtml\SharedCatalog\Configure\Product\Price;

/**
 * Unit test for Discount controller.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DiscountTest extends \PHPUnit\Framework\TestCase
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
     * @var \Magento\Ui\Component\MassAction\Filter|\PHPUnit_Framework_MockObject_MockObject
     */
    private $filter;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $collectionFactory;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultJsonFactory;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Collection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productCollection;

    /**
     * @var \Magento\SharedCatalog\Controller\Adminhtml\SharedCatalog\Configure\Product\Price\Discount
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
        $this->productItemTierPriceValidator = $this->getMockBuilder(
            \Magento\SharedCatalog\Model\ProductItemTierPriceValidator::class
        )
            ->disableOriginalConstructor()
            ->getMock();
        $this->request = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->filter = $this->getMockBuilder(\Magento\Ui\Component\MassAction\Filter::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->collectionFactory = $this->getMockBuilder(
            \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory::class
        )
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->resultJsonFactory = $this
            ->getMockBuilder(\Magento\Framework\Controller\Result\JsonFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->productCollection = $this->getMockBuilder(\Magento\Catalog\Model\ResourceModel\Product\Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->collectionFactory->expects($this->once())->method('create')->willReturn($this->productCollection);
        $this->filter->expects($this->once())
            ->method('getCollection')
            ->with($this->productCollection)
            ->willReturn($this->productCollection);

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->controller = $objectManager->getObject(
            \Magento\SharedCatalog\Controller\Adminhtml\SharedCatalog\Configure\Product\Price\Discount::class,
            [
                '_request' => $this->request,
                'productItemTierPriceValidator' => $this->productItemTierPriceValidator,
                'wizardStorageFactory' => $this->wizardStorageFactory,
                'filter' => $this->filter,
                'collectionFactory' => $this->collectionFactory,
                'resultJsonFactory' => $this->resultJsonFactory,
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
        $productSku = 'ProductSKU';
        $price = 10;
        $websiteId = 2;
        $storage = $this
            ->getMockBuilder(\Magento\SharedCatalog\Model\Form\Storage\Wizard::class)
            ->disableOriginalConstructor()
            ->getMock();
        $product = $this->getMockBuilder(\Magento\Catalog\Api\Data\ProductInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->request->expects($this->atLeastOnce())
            ->method('getParam')
            ->withConsecutive(['value'], ['configure_key'], ['website_id'])
            ->willReturnOnConsecutiveCalls($price, 'configure_key', $websiteId);
        $this->wizardStorageFactory->expects($this->once())
            ->method('create')
            ->with(['key' => 'configure_key'])
            ->willReturn($storage);
        $this->productCollection->expects($this->once())->method('addFieldToSelect')->with('price')->willReturnSelf();
        $this->productCollection->expects($this->once())
            ->method('getIterator')->willReturn(new \ArrayIterator([$product, $product]));
        $product->expects($this->atLeastOnce())->method('getSku')->willReturn($productSku);
        $storage->expects($this->atLeastOnce())
            ->method('getProductPrices')
            ->with($productSku)
            ->willReturn([]);
        $product->expects($this->exactly(2))
            ->method('getTypeId')
            ->willReturnOnConsecutiveCalls(
                \Magento\Bundle\Model\Product\Type::TYPE_CODE,
                \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE
            );
        $this->productItemTierPriceValidator->expects($this->exactly(2))->method('isTierPriceApplicable')
            ->withConsecutive(
                [\Magento\Bundle\Model\Product\Type::TYPE_CODE],
                [\Magento\Catalog\Model\Product\Type::TYPE_SIMPLE]
            )
            ->willReturnOnConsecutiveCalls(true, false);
        $this->productItemTierPriceValidator->expects($this->atLeastOnce())
            ->method('canChangePrice')
            ->with([], $websiteId)
            ->willReturnOnConsecutiveCalls(true, false);
        $storage->expects($this->once())
            ->method('setTierPrices')
            ->with(
                [
                    $productSku => [
                        [
                            'qty' => 1,
                            'percentage_value' => $price,
                            'value_type' => 'percent',
                            'website_id' => $websiteId,
                            'is_changed' => true,
                        ]
                    ],
                ]
            );
        $json = $this->getMockBuilder(\Magento\Framework\Controller\Result\Json::class)
            ->disableOriginalConstructor()
            ->getMock();
        $result = ['data' => ['status' => true]];
        $json->expects($this->once())
            ->method('setJsonData')
            ->with(json_encode($result, JSON_NUMERIC_CHECK))
            ->willReturnSelf();
        $this->resultJsonFactory->expects($this->once())->method('create')->willReturn($json);
        $this->assertEquals($json, $this->controller->execute());
    }

    /**
     * Test Execute method with negative price.
     *
     * @return void
     */
    public function testExecuteWithInvalidPrice()
    {
        $price = -15;
        $storage = $this
            ->getMockBuilder(\Magento\SharedCatalog\Model\Form\Storage\Wizard::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->request->expects($this->once())->method('getParam')->with('value')->willReturn($price);
        $json = $this->getMockBuilder(\Magento\Framework\Controller\Result\Json::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->wizardStorageFactory->expects($this->never())->method('create')->willReturn($storage);
        $result = ['data' => ['status' => false, 'error' => __("Discount value cannot be outside the range 0-100")]];
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
