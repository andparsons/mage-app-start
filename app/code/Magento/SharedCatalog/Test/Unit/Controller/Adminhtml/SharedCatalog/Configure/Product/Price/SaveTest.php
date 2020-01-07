<?php

namespace Magento\SharedCatalog\Test\Unit\Controller\Adminhtml\SharedCatalog\Configure\Product\Price;

/**
 * Unit test for Save controller.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SaveTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultJsonFactory;

    /**
     * @var \Magento\SharedCatalog\Model\Form\Storage\WizardFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $wizardStorageFactory;

    /**
     * @var \Magento\Framework\Locale\FormatInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $valueFormatter;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $request;

    /**
     * @var \Magento\SharedCatalog\Model\ProductItemTierPriceValidator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productItemTierPriceValidator;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productRepository;

    /**
     * @var \Magento\SharedCatalog\Controller\Adminhtml\SharedCatalog\Configure\Product\Price\Save
     */
    private $save;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->request = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->resultJsonFactory = $this->getMockBuilder(\Magento\Framework\Controller\Result\JsonFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()->getMock();
        $this->wizardStorageFactory = $this
            ->getMockBuilder(\Magento\SharedCatalog\Model\Form\Storage\WizardFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()->getMock();
        $this->valueFormatter = $this->getMockBuilder(\Magento\Framework\Locale\FormatInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->productItemTierPriceValidator = $this
            ->getMockBuilder(\Magento\SharedCatalog\Model\ProductItemTierPriceValidator::class)
            ->disableOriginalConstructor()->getMock();
        $this->productRepository = $this
            ->getMockBuilder(\Magento\Catalog\Api\ProductRepositoryInterface::class)
            ->disableOriginalConstructor()->getMock();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->save = $objectManager->getObject(
            \Magento\SharedCatalog\Controller\Adminhtml\SharedCatalog\Configure\Product\Price\Save::class,
            [
                '_request' => $this->request,
                'resultJsonFactory' => $this->resultJsonFactory,
                'wizardStorageFactory' => $this->wizardStorageFactory,
                'valueFormatter' => $this->valueFormatter,
                'productItemTierPriceValidator' => $this->productItemTierPriceValidator,
                'productRepository' => $this->productRepository,
            ]
        );
    }

    /**
     * Test for execute method.
     *
     * @param string $customPrice
     * @param int $numericPrice
     * @param \PHPUnit\Framework\MockObject\Matcher\Invocation $setPriceInvocation
     * @param \PHPUnit\Framework\MockObject\Matcher\Invocation $deletePriceInvocation
     * @return void
     * @dataProvider executeDataProvider
     */
    public function testExecute($customPrice, $numericPrice, $setPriceInvocation, $deletePriceInvocation)
    {
        $configureKey = 'configure_key_value';
        $productId = 1;
        $productSku = 'ProductSKU';
        $priceType = 'fixed';
        $requestParamConfigureKey = \Magento\SharedCatalog\Model\Form\Storage\UrlBuilder::REQUEST_PARAM_CONFIGURE_KEY;
        $websiteId = 2;
        $prices = [
            [
                'product_id' => $productId,
                'custom_price' => $customPrice,
                'website_id' => $websiteId,
                'price_type' => $priceType,
            ],
            []
        ];
        $this->request->expects($this->atLeastOnce())->method('getParam')
            ->withConsecutive(
                ['prices'],
                [$requestParamConfigureKey]
            )
            ->willReturnOnConsecutiveCalls($prices, $configureKey);
        $wizardStorage = $this->getMockBuilder(\Magento\SharedCatalog\Model\Form\Storage\Wizard::class)
            ->disableOriginalConstructor()->getMock();
        $this->wizardStorageFactory->expects($this->once())
            ->method('create')->with(['key' => $configureKey])->willReturn($wizardStorage);
        $product = $this->getMockBuilder(\Magento\Catalog\Api\Data\ProductInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->productRepository->expects($this->once())
            ->method('getById')->with($productId)->willReturn($product);
        $this->valueFormatter->expects($this->once())
            ->method('getNumber')->with($customPrice)->willReturn($numericPrice);
        $wizardStorage->expects($this->atLeastOnce())
            ->method('getProductPrices')->with($productId)->willReturn([]);
        $this->productItemTierPriceValidator->expects($this->atLeastOnce())
            ->method('canChangePrice')->with([], $websiteId)->willReturn(true);
        $product->expects($this->once())->method('getSku')->willReturn($productSku);
        $wizardStorage->expects($setPriceInvocation)
            ->method('setTierPrices')->with([
                $productSku => [
                    [
                        'qty' => 1,
                        \Magento\Catalog\Api\Data\ProductAttributeInterface::CODE_PRICE => $numericPrice,
                        'value_type' => $priceType,
                        'website_id' => $websiteId,
                        'is_changed' => true,
                    ],
                ]
            ]);
        $wizardStorage->expects($deletePriceInvocation)->method('deleteTierPrice')->with($productSku, 1, $websiteId);
        $json = $this->getMockBuilder(\Magento\Framework\Controller\Result\Json::class)
            ->disableOriginalConstructor()->getMock();
        $json->expects($this->once())->method('setJsonData')
            ->with(json_encode(['data' => ['status' => 1]], JSON_NUMERIC_CHECK))->willReturnSelf();
        $this->resultJsonFactory->expects($this->once())->method('create')->willReturn($json);
        $this->assertEquals($json, $this->save->execute());
    }

    /**
     * Data provider for testExecute.
     *
     * @return array
     */
    public function executeDataProvider()
    {
        return [
            ['$15', 15, $this->once(), $this->never()],
            ['-$15', -15, $this->never(), $this->once()],
            ['$0', 0, $this->once(), $this->never()],
        ];
    }
}
