<?php
namespace Magento\SharedCatalog\Test\Unit\Plugin\AdvancedCheckout\Block;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Unit test for ConfigureButtonPlugin.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RenderConfigureButtonTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Serialize\SerializerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $serializer;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManager;

    /**
     * @var \Magento\SharedCatalog\Model\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $config;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteRepository;

    /**
     * @var \Magento\SharedCatalog\Model\SharedCatalogProductsLoader|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productsLoader;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $request;

    /**
     * @var \Magento\SharedCatalog\Plugin\AdvancedCheckout\Block\RenderConfigureButton
     */
    private $renderConfigureButton;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->serializer = $this->getMockBuilder(\Magento\Framework\Serialize\SerializerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->storeManager = $this->getMockBuilder(\Magento\Store\Model\StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->config = $this->getMockBuilder(\Magento\SharedCatalog\Model\Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->quoteRepository = $this->getMockBuilder(\Magento\Quote\Api\CartRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->productsLoader = $this->getMockBuilder(\Magento\SharedCatalog\Model\SharedCatalogProductsLoader::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->request = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->renderConfigureButton = $objectManagerHelper->getObject(
            \Magento\SharedCatalog\Plugin\AdvancedCheckout\Block\RenderConfigureButton::class,
            [
                'serializer' => $this->serializer,
                'storeManager' => $this->storeManager,
                'config' => $this->config,
                'quoteRepository' => $this->quoteRepository,
                'productsLoader' => $this->productsLoader,
                'request' => $this->request
            ]
        );
    }

    /**
     * Test for aroundGetConfigureButtonHtml().
     *
     * @return void
     */
    public function testAroundGetConfigureButtonHtml()
    {
        $productId = 1;
        $customerGroupId = 3;
        $quoteId = 4;
        $quoteIdParamKey = 'quote_id';
        $sku = 'sku';
        $productSkus = ['sku', 'product_sku'];
        $buttonHtml = 'button_html';
        $item = $this->getMockBuilder(\Magento\Framework\DataObject::class)
            ->disableOriginalConstructor()
            ->setMethods(['getSku', 'getIsConfigureDisabled'])
            ->getMock();
        $item->expects($this->atLeastOnce())->method('getSku')->willReturn($sku);
        $item->expects($this->atLeastOnce())->method('getIsConfigureDisabled')->willReturn(false);
        $product = $this->getMockBuilder(\Magento\Catalog\Api\Data\ProductInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['canConfigure'])
            ->getMockForAbstractClass();
        $product->expects($this->atLeastOnce())->method('getId')->willReturn($productId);
        $product->expects($this->atLeastOnce())->method('canConfigure')->willReturn(true);
        $encodedProductId = json_encode($productId);
        $encodedSku = json_encode($sku);
        $this->serializer->expects($this->atLeastOnce())->method('serialize')
            ->willReturnOnConsecutiveCalls($encodedProductId, $encodedSku);
        $button = $this->getMockBuilder(\Magento\Backend\Block\Widget\Button::class)
            ->disableOriginalConstructor()
            ->getMock();
        $button->expects($this->atLeastOnce())->method('toHtml')->willReturn($buttonHtml);
        $layout = $this->getMockBuilder(\Magento\Framework\View\LayoutInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $layout->expects($this->atLeastOnce())->method('createBlock')->willReturn($button);
        $this->request->expects($this->atLeastOnce())->method('getParam')->with($quoteIdParamKey)->willReturn($quoteId);
        $subject = $this->getMockBuilder(\Magento\AdvancedCheckout\Block\Adminhtml\Sku\Errors\Grid\Description::class)
            ->disableOriginalConstructor()
            ->setMethods(['getItem', 'getProduct', 'escapeHtml', 'getLayout', 'getRequest'])
            ->getMock();
        $subject->expects($this->atLeastOnce())->method('getItem')->willReturn($item);
        $subject->expects($this->atLeastOnce())->method('getProduct')->willReturn($product);
        $subject->expects($this->atLeastOnce())->method('escapeHtml')
            ->willReturnOnConsecutiveCalls($encodedProductId, $encodedSku);
        $subject->expects($this->atLeastOnce())->method('getLayout')->willReturn($layout);
        $subject->expects($this->atLeastOnce())->method('getRequest')->willReturn($this->request);
        $website = $this->getMockBuilder(\Magento\Store\Api\Data\WebsiteInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $website->expects($this->atLeastOnce())->method('getId')->willReturn(2);
        $this->storeManager->expects($this->atLeastOnce())->method('getWebsite')->willReturn($website);
        $this->config->expects($this->atLeastOnce())->method('isActive')->willReturn(true);
        $quote = $this->getMockBuilder(\Magento\Quote\Api\Data\CartInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCustomerGroupId'])
            ->getMockForAbstractClass();
        $quote->expects($this->atLeastOnce())->method('getCustomerGroupId')->willReturn($customerGroupId);
        $this->quoteRepository->expects($this->atLeastOnce())->method('get')->with($quoteId)->willReturn($quote);
        $this->productsLoader->expects($this->atLeastOnce())->method('getAssignedProductsSkus')
            ->with($customerGroupId)->willReturn($productSkus);
        $closure = function () {
            return;
        };

        $this->assertEquals(
            $buttonHtml,
            $this->renderConfigureButton->aroundGetConfigureButtonHtml($subject, $closure)
        );
    }
}
