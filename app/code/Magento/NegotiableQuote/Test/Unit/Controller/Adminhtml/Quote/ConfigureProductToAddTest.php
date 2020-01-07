<?php
namespace Magento\NegotiableQuote\Test\Unit\Controller\Adminhtml\Quote;

/**
 * Unit test for Configure Product To Add.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ConfigureProductToAddTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\NegotiableQuote\Controller\Adminhtml\Quote\ConfigureProductToAdd
     */
    private $controller;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $request;

    /**
     * @var \Magento\Framework\DataObject|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dataObject;

    /**
     * @var \Magento\Backend\Model\Session\Quote|\PHPUnit_Framework_MockObject_MockObject
     */
    private $sessionQuote;

    /**
     * @var \Magento\Catalog\Helper\Product\Composite|\PHPUnit_Framework_MockObject_MockObject
     */
    private $compositeHelper;

    /**
     * @var \Magento\NegotiableQuote\Api\NegotiableQuoteManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteManagement;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->request = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->dataObject = $this->getMockBuilder(\Magento\Framework\DataObject::class)
            ->disableOriginalConstructor()
            ->setMethods(['setOk', 'setProductId', 'setCurrentStoreId', 'setCurrentCustomerId', 'setBuyRequest'])
            ->getMock();
        $this->sessionQuote = $this->getMockBuilder(\Magento\Backend\Model\Session\Quote::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->compositeHelper = $this->getMockBuilder(\Magento\Catalog\Helper\Product\Composite::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->quoteManagement = $this
            ->getMockBuilder(\Magento\NegotiableQuote\Api\NegotiableQuoteManagementInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
    }

    /**
     * Creates an instance of subject under test.
     *
     * @return void
     */
    private function createInstance()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->controller = $objectManager->getObject(
            \Magento\NegotiableQuote\Controller\Adminhtml\Quote\ConfigureProductToAdd::class,
            [
                'request' => $this->request,
                'dataObject' => $this->dataObject,
                'sessionQuote' => $this->sessionQuote,
                'compositeHelper' => $this->compositeHelper,
                'quoteManagement' => $this->quoteManagement,
                'productTypesToReplace' => [\Magento\Bundle\Model\Product\Type::TYPE_CODE]
            ]
        );
    }

    /**
     * Test execute method.
     *
     * @return void
     */
    public function testExecute()
    {
        $productId = '1';
        $quoteId = 1;
        $this->request->expects(($this->atLeastOnce()))
            ->method('getParam')
            ->withConsecutive(
                ['id'],
                ['quote_id'],
                ['config']
            )
            ->willReturnOnConsecutiveCalls(
                $productId,
                $quoteId,
                'testConfig'
            );
        $customer = $this->getMockBuilder(\Magento\Customer\Api\Data\CustomerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $customer->expects($this->once())->method('getId')->willReturn(1);
        $quote = $this->getMockBuilder(\Magento\Quote\Api\Data\CartInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $quote->expects($this->once())->method('getCustomer')->willReturn($customer);
        $this->quoteManagement->expects($this->once())->method('getNegotiableQuote')->willReturn($quote);
        $this->dataObject->expects($this->once())
            ->method('setOk');
        $store = $this->getMockBuilder(\Magento\Store\Model\Store::class)
            ->disableOriginalConstructor()
            ->getMock();
        $store->expects($this->once())
            ->method('getId')
            ->willReturn(1);
        $this->sessionQuote->expects($this->once())
            ->method('getStore')
            ->willReturn($store);
        $this->dataObject->expects($this->once())
            ->method('setBuyRequest');
        $resultLayout = $this->getMockBuilder(\Magento\Framework\View\Result\Layout::class)
            ->disableOriginalConstructor()
            ->getMock();
        $layoutMock = $this->getLayoutMock();
        $resultLayout->expects($this->once())->method('getLayout')->willReturn($layoutMock);
        $this->compositeHelper->expects($this->once())
            ->method('renderConfigureResult')
            ->willReturn($resultLayout);

        $this->createInstance();
        $result = $this->controller->execute();

        $this->assertSame($result, $resultLayout);
    }

    /**
     * Get Layout Mock.
     *
     * @return \Magento\Framework\View\Layout|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getLayoutMock()
    {
        $customProductTypeHandle = 'catalog_product_view_type_' . \Magento\Bundle\Model\Product\Type::TYPE_CODE;
        $updateLayoutMock = $this->getMockBuilder(\Magento\Framework\View\Layout\ProcessorInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getHandles', 'addHandle', 'removeHandle'])
            ->getMockForAbstractClass();
        $origHandles = [
            'CATALOG_PRODUCT_COMPOSITE_CONFIGURE',
            $customProductTypeHandle
        ];
        $updateLayoutMock->expects($this->once())->method('getHandles')->willReturn($origHandles);
        $updateLayoutMock->expects($this->atLeastOnce())->method('removeHandle')
            ->withConsecutive(
                ['CATALOG_PRODUCT_COMPOSITE_CONFIGURE'],
                [$customProductTypeHandle]
            )
            ->willReturnSelf();
        $updateLayoutMock->expects($this->atLeastOnce())->method('addHandle')
            ->withConsecutive(
                ['negotiable_quote_catalog_product_composite_configure'],
                ['negotiablequote_' . $customProductTypeHandle]
            )
            ->willReturnSelf();
        $layoutMock = $this->getMockBuilder(\Magento\Framework\View\Layout::class)
            ->disableOriginalConstructor()
            ->setMethods(['getUpdate'])
            ->getMock();
        $layoutMock->expects($this->once())->method('getUpdate')->willReturn($updateLayoutMock);

        return $layoutMock;
    }
}
