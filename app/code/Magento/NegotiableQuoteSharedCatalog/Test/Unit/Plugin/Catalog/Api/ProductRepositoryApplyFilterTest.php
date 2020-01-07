<?php

namespace Magento\NegotiableQuoteSharedCatalog\Test\Unit\Plugin\Catalog\Api;

use Magento\NegotiableQuoteSharedCatalog\Plugin\Catalog\Api\ProductRepositoryApplyFilter;
use Magento\NegotiableQuoteSharedCatalog\Model\SharedCatalog\ProductItem\Retrieve;

/**
 * Unit tests for ProductRepositoryApplyFilter plugin.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ProductRepositoryApplyFilterTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ProductRepositoryApplyFilter
     */
    private $productRepositoryPlugin;

    /**
     * @var \Magento\SharedCatalog\Model\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $config;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $subject;

    /**
     * @var \Magento\Catalog\Api\Data\ProductInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $product;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $request;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteRepository;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManager;

    /**
     * @var Retrieve|\PHPUnit_Framework_MockObject_MockObject
     */
    private $sharedCatalogProductItemRetriever;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->config = $this->getMockBuilder(\Magento\SharedCatalog\Model\Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->request = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getParam'])
            ->getMockForAbstractClass();
        $this->quoteRepository = $this->getMockBuilder(\Magento\Quote\Api\CartRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->subject = $this->getMockBuilder(\Magento\Catalog\Api\ProductRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->product = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeManager = $this->getMockBuilder(\Magento\Store\Model\StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->sharedCatalogProductItemRetriever = $this->getMockBuilder(Retrieve::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->productRepositoryPlugin = $objectManager->getObject(
            ProductRepositoryApplyFilter::class,
            [
                'config' => $this->config,
                'request' => $this->request,
                'quoteRepository' => $this->quoteRepository,
                'storeManager' => $this->storeManager,
                'sharedCatalogProductItemRetriever' => $this->sharedCatalogProductItemRetriever
            ]
        );
    }

    /**
     * Test for getById() method when Shared Catalog is disabled.
     *
     * @return void
     */
    public function testConfigDisabledAfterGetById()
    {
        $website = $this->getMockBuilder(\Magento\Store\Api\Data\WebsiteInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->storeManager->expects($this->atLeastOnce())->method('getWebsite')->willReturn($website);
        $this->config->expects($this->once())
            ->method('isActive')
            ->willReturn(false);

        $result = $this->productRepositoryPlugin->afterGetById($this->subject, $this->product);
        $this->assertEquals($result, $this->product);
    }

    /**
     * Test for get() method when Shared Catalog is disabled.
     *
     * @return void
     */
    public function testConfigDisabledAfterGet()
    {
        $website = $this->getMockBuilder(\Magento\Store\Api\Data\WebsiteInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->storeManager->expects($this->atLeastOnce())->method('getWebsite')->willReturn($website);
        $this->config->expects($this->once())
            ->method('isActive')
            ->willReturn(false);

        $result = $this->productRepositoryPlugin->afterGet($this->subject, $this->product);
        $this->assertEquals($result, $this->product);
    }

    /**
     * Test for getById() method when Shared Catalog is enabled.
     *
     * @param int $throwException
     * @return void
     * @dataProvider afterGetDataProvider
     */
    public function testAfterGetById($throwException)
    {
        $this->request->expects($this->atLeastOnce())->method('getParam')->willReturn(true);
        $this->prepareBody($throwException);
        $result = $this->productRepositoryPlugin->afterGetById($this->subject, $this->product);
        $this->assertEquals($result, $this->product);
    }

    /**
     * Test for get() method when Shared Catalog is enabled.
     *
     * @param int $throwException
     * @return void
     * @dataProvider afterGetDataProvider
     */
    public function testAfterGet($throwException)
    {
        $this->request->expects($this->atLeastOnce())->method('getParam')->willReturn(true);
        $this->prepareBody($throwException);
        $result = $this->productRepositoryPlugin->afterGet($this->subject, $this->product);
        $this->assertEquals($result, $this->product);
    }

    /**
     * Test for get() method when customer group ID is empty.
     *
     * @return void
     */
    public function testAfterGetWithEmptyCustomerGroupId()
    {
        $website = $this->getMockBuilder(\Magento\Store\Api\Data\WebsiteInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->storeManager->expects($this->atLeastOnce())->method('getWebsite')->willReturn($website);
        $this->request->expects($this->atLeastOnce())->method('getParam')->willReturn(true);
        $this->config->expects($this->once())
            ->method('isActive')
            ->willReturn(true);
        $quote = $this->getMockBuilder(\Magento\Quote\Api\Data\CartInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCustomerGroupId'])
            ->getMockForAbstractClass();
        $quote->expects($this->once())
            ->method('getCustomerGroupId')
            ->willReturn(null);
        $this->quoteRepository->expects($this->once())
            ->method('get')
            ->willReturn($quote);
        $this->product->expects($this->never())
            ->method('getData');

        $result = $this->productRepositoryPlugin->afterGet($this->subject, $this->product);
        $this->assertEquals($result, $this->product);
    }

    /**
     * Prepare the body of main tests.
     *
     * @param int $throwException
     * @return void
     */
    private function prepareBody($throwException)
    {
        $customerGroupId = 1;
        $quote = $this->getMockBuilder(\Magento\Quote\Api\Data\CartInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCustomerGroupId'])
            ->getMockForAbstractClass();
        $quote->expects($this->any())
            ->method('getCustomerGroupId')
            ->willReturn($customerGroupId);
        $this->quoteRepository->expects($this->any())
            ->method('get')
            ->willReturn($quote);
        $website = $this->getMockBuilder(\Magento\Store\Api\Data\WebsiteInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->storeManager->expects($this->atLeastOnce())->method('getWebsite')->willReturn($website);
        $this->config->expects($this->once())
            ->method('isActive')
            ->willReturn(true);
        $this->product->expects($this->once())
            ->method('getData')
            ->willReturnMap([
                ['sku', null, 'testsku']
            ]);
        $items = $this->getMockBuilder(
            \Magento\SharedCatalog\Api\Data\ProductItemInterface::class
        )
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        if ($throwException == 1) {
            $items = null;
            $this->expectException(\Magento\Framework\Exception\NoSuchEntityException::class);
            $this->expectExceptionMessage(
                'The product that was requested doesn\'t exist. Verify the product and try again.'
            );
        }
        $this->sharedCatalogProductItemRetriever
            ->expects($this->once())
            ->method('retrieve')
            ->willReturn($items);
    }

    /**
     * Data provider for testAfterGetById test.
     *
     * @return array
     */
    public function afterGetDataProvider()
    {
        return [
            [
                'throwException' => 0
            ],
            [
                'throwException' => 1
            ]
        ];
    }
}
