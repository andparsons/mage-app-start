<?php

namespace Magento\NegotiableQuote\Test\Unit\Model\History;

/**
 * Unit test for Magento\NegotiableQuote\Model\History\LogProductInformation class.
 */
class LogProductInformationTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productRepository;

    /**
     * @var \Psr\Log\LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $logger;

    /**
     * @var \Magento\NegotiableQuote\Model\ProductOptionsProviderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $optionsProvider;

    /**
     * @var \Magento\NegotiableQuote\Model\History\LogProductInformation
     */
    private $model;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->productRepository = $this->getMockBuilder(\Magento\Catalog\Api\ProductRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->logger = $this->getMockBuilder(\Psr\Log\LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->optionsProvider = $this->getMockBuilder(
            \Magento\NegotiableQuote\Model\ProductOptionsProviderInterface::class
        )
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $objectManagerHelper->getObject(
            \Magento\NegotiableQuote\Model\History\LogProductInformation::class,
            [
                'productRepository' => $this->productRepository,
                'logger' => $this->logger,
                'productOptionsProviders' => [$this->optionsProvider]
            ]
        );
    }

    /**
     * Test getProductName method.
     *
     * @return void
     */
    public function testGetProductName()
    {
        $sku = 'product-sku';
        $productName = 'Product Name';
        $product = $this->getMockBuilder(\Magento\Catalog\Api\Data\ProductInterface::class)
           ->disableOriginalConstructor()
           ->getMockForAbstractClass();
        $this->productRepository->expects($this->once())->method('get')->with($sku)->willReturn($product);
        $product->expects($this->once())->method('getName')->willReturn($productName);

        $this->assertEquals('Product Name', $this->model->getProductName($sku));
    }

    /**
     * Test getProductName method with NoSuchEntityException.
     *
     * @return void
     */
    public function testGetProductNameWithNoSuchEntityException()
    {
        $sku = 'product-sku';
        $exception = new \Magento\Framework\Exception\NoSuchEntityException(__('No such entity.'));
        $this->productRepository->expects($this->once())->method('get')->with($sku)->willThrowException($exception);
        $this->logger->expects($this->once())->method('critical')->with($exception);

        $this->assertEquals('product-sku' . __(' - deleted'), $this->model->getProductName($sku));
    }

    /**
     * Test getProductName method with Exception.
     *
     * @return void
     */
    public function testGetProductNameWithException()
    {
        $sku = 'product-sku';
        $exception = new \Exception();
        $this->productRepository->expects($this->once())->method('get')->with($sku)->willThrowException($exception);
        $this->logger->expects($this->once())->method('critical')->with($exception);

        $this->assertEquals('product-sku', $this->model->getProductName($sku));
    }

    /**
     * Test getProductNameById method.
     *
     * @return void
     */
    public function testGetProductNameById()
    {
        $productId = 1;
        $productName = 'Product Name';
        $product = $this->getMockBuilder(\Magento\Catalog\Api\Data\ProductInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->productRepository->expects($this->once())->method('getById')->with($productId)->willReturn($product);
        $product->expects($this->once())->method('getName')->willReturn($productName);

        $this->assertEquals('Product Name', $this->model->getProductNameById($productId));
    }

    /**
     * Test getProductNameById method with NoSuchEntityException.
     *
     * @return void
     */
    public function testGetProductNameByIdWithNoSuchEntityException()
    {
        $productId = 1;
        $exception = new \Magento\Framework\Exception\NoSuchEntityException(__('No such entity.'));
        $this->productRepository->expects($this->once())
            ->method('getById')
            ->with($productId)
            ->willThrowException($exception);
        $this->logger->expects($this->once())->method('critical')->with($exception);

        $this->assertEquals(__('Product with ID #%1 is deleted', 1), $this->model->getProductNameById($productId));
    }

    /**
     * Test getProductNameById method with Exception.
     *
     * @return void
     */
    public function testGetProductNameByIdWithException()
    {
        $productId = 1;
        $exception = new \Exception();
        $this->productRepository->expects($this->once())
            ->method('getById')
            ->with($productId)
            ->willThrowException($exception);
        $this->logger->expects($this->once())->method('critical')->with($exception);

        $this->assertEquals(1, $this->model->getProductNameById($productId));
    }

    /**
     * Test getProductAttributes method.
     *
     * @return void
     */
    public function testGetProductAttributes()
    {
        $productId = 1;
        $product = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->productRepository->expects($this->once())->method('getById')->with($productId)->willReturn($product);
        $product->expects($this->once())->method('getTypeId')->willReturn('bundle');
        $this->optionsProvider->expects($this->once())->method('getProductType')->willReturn('bundle');
        $this->optionsProvider->expects($this->once())->method('getOptions')->with($product)->willReturn([]);

        $this->assertEquals([], $this->model->getProductAttributes($productId));
    }

    /**
     * Test getProductAttributes method with NoSuchEntityException.
     *
     * @return void
     */
    public function testGetProductAttributesWithNoSuchEntityException()
    {
        $productId = 1;
        $exception = new \Magento\Framework\Exception\NoSuchEntityException(__('No such entity.'));
        $this->productRepository->expects($this->once())
            ->method('getById')
            ->with($productId)
            ->willThrowException($exception);
        $this->logger->expects($this->once())->method('critical')->with($exception);

        $this->assertEquals([], $this->model->getProductAttributes($productId));
    }

    /**
     * Test getProductAttributes method with Exception.
     *
     * @return void
     */
    public function testGetProductAttributesWithException()
    {
        $productId = 1;
        $exception = new \Exception();
        $this->productRepository->expects($this->once())
            ->method('getById')
            ->with($productId)
            ->willThrowException($exception);
        $this->logger->expects($this->once())->method('critical')->with($exception);

        $this->assertEquals([], $this->model->getProductAttributes($productId));
    }
}
