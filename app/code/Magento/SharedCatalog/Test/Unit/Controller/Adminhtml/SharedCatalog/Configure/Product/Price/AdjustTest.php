<?php

namespace Magento\SharedCatalog\Test\Unit\Controller\Adminhtml\SharedCatalog\Configure\Product\Price;

/**
 * Test for Adjust controller.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AdjustTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\SharedCatalog\Controller\Adminhtml\SharedCatalog\Configure\Product\Price\Adjust
     */
    private $controller;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultJsonFactory;

    /**
     * @var \Magento\SharedCatalog\Model\Form\Storage\Wizard|\PHPUnit_Framework_MockObject_MockObject
     */
    private $wizardStorage;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $request;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Collection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $collection;

    /**
     * @var \Magento\SharedCatalog\Model\Form\Storage\WizardFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $wizardStorageFactory;

    /**
     * @var \Magento\SharedCatalog\Model\ProductItemTierPriceValidator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productItemTierPriceValidator;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->request = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultJsonFactory = $this
            ->getMockBuilder(\Magento\Framework\Controller\Result\JsonFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->wizardStorageFactory = $this
            ->getMockBuilder(\Magento\SharedCatalog\Model\Form\Storage\WizardFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->wizardStorage = $this->getMockBuilder(\Magento\SharedCatalog\Model\Form\Storage\Wizard::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->productItemTierPriceValidator = $this
            ->getMockBuilder(\Magento\SharedCatalog\Model\ProductItemTierPriceValidator::class)
            ->disableOriginalConstructor()
            ->getMock();
        $filter = $this->getMockBuilder(\Magento\Ui\Component\MassAction\Filter::class)
            ->disableOriginalConstructor()
            ->getMock();
        $collectionFactory = $this
            ->getMockBuilder(\Magento\Catalog\Model\ResourceModel\Product\CollectionFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->collection = $this->getMockBuilder(\Magento\Catalog\Model\ResourceModel\Product\Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $collectionFactory->expects($this->once())->method('create')->willReturn($this->collection);
        $filter->expects($this->once())
            ->method('getCollection')->with($this->collection)->willReturn($this->collection);

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->controller = $objectManager->getObject(
            \Magento\SharedCatalog\Controller\Adminhtml\SharedCatalog\Configure\Product\Price\Adjust::class,
            [
                '_request' => $this->request,
                'filter' => $filter,
                'collectionFactory' => $collectionFactory,
                'resultJsonFactory' => $this->resultJsonFactory,
                'wizardStorageFactory' => $this->wizardStorageFactory,
                'productItemTierPriceValidator' => $this->productItemTierPriceValidator,
            ]
        );
    }

    /**
     * Test for method Execute without price.
     *
     * @return void
     */
    public function testExecuteWithoutPrice()
    {
        $this->request->expects($this->once())->method('getParam')->with('value')->willReturn(1000);
        $json = $this->getMockBuilder(\Magento\Framework\Controller\Result\Json::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->wizardStorageFactory->expects($this->never())->method('create')->willReturn($this->wizardStorage);
        $result = ['data' => ['status' => false, 'error' => __("Discount value cannot be outside the range 0-100")]];
        $json->expects($this->once())->method('setJsonData')
            ->with(json_encode($result, JSON_NUMERIC_CHECK))->willReturnSelf();
        $this->resultJsonFactory->expects($this->once())
            ->method('create')->will($this->returnValue($json));
        $this->assertEquals($json, $this->controller->execute());
    }

    /**
     * Test for method Execute with price.
     *
     * @return void
     */
    public function testExecuteWithPrice()
    {
        $productSku = 'ProductSKU';
        $configureKey = 'test';
        $websiteId = 2;
        $this->request->expects($this->atLeastOnce())
            ->method('getParam')
            ->withConsecutive(['value'], ['configure_key'], ['website_id'])
            ->willReturnOnConsecutiveCalls(20, $configureKey, $websiteId);
        $this->wizardStorageFactory->expects($this->once())->method('create')
            ->with(['key' => $configureKey])->willReturn($this->wizardStorage);
        $this->collection->expects($this->once())->method('addFieldToSelect')->with('price')->willReturnSelf();
        $this->collection->expects($this->once())->method('addFieldToFilter')
            ->with('type_id', ['nin' => []])->willReturnSelf();
        $product = $this->getMockBuilder(\Magento\Catalog\Api\Data\ProductInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $product->expects($this->atLeastOnce())->method('getSku')->willReturn($productSku);
        $this->wizardStorage->expects($this->atLeastOnce())
            ->method('getProductPrices')
            ->with($productSku)
            ->willReturn([]);
        $this->collection->expects($this->once())
            ->method('getIterator')->willReturn(new \ArrayIterator([$product, $product]));
        $product->expects($this->atLeastOnce())
            ->method('getTypeId')->willReturnOnConsecutiveCalls(
                \Magento\Bundle\Model\Product\Type::TYPE_CODE,
                \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE
            );
        $this->productItemTierPriceValidator->expects($this->atLeastOnce())->method('isTierPriceApplicable')
            ->withConsecutive(
                [\Magento\Bundle\Model\Product\Type::TYPE_CODE],
                [\Magento\Catalog\Model\Product\Type::TYPE_SIMPLE]
            )
            ->willReturnOnConsecutiveCalls(true, false);
        $this->productItemTierPriceValidator->expects($this->atLeastOnce())
            ->method('canChangePrice')
            ->with([], $websiteId)
            ->willReturnOnConsecutiveCalls(true, false);
        $product->expects($this->once())->method('getPrice')->willReturn(10);
        $this->wizardStorage->expects($this->once())
            ->method('setTierPrices')
            ->with(
                [
                    $productSku => [
                        [
                            'qty' => 1,
                            'price' => 8.0,
                            'value_type' => 'fixed',
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
        $json->expects($this->once())->method('setJsonData')
            ->with(json_encode($result, JSON_NUMERIC_CHECK))->willReturnSelf();
        $this->resultJsonFactory->expects($this->once())->method('create')->willReturn($json);
        $this->assertEquals($json, $this->controller->execute());
    }
}
