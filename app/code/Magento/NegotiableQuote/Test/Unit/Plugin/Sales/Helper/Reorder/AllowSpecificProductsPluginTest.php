<?php
namespace Magento\NegotiableQuote\Test\Unit\Plugin\Sales\Helper\Reorder;

/**
 * Test for AllowSpecificProductsPlugin.
 */
class AllowSpecificProductsPluginTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Company\Model\CompanyContext|\PHPUnit_Framework_MockObject_MockObject
     */
    private $companyContext;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $orderRepository;

    /**
     * @var \Magento\NegotiableQuote\Plugin\Sales\Helper\Reorder\AllowSpecificProductsPlugin
     */
    private $allowSpecificProductsPlugin;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->orderRepository = $this->getMockBuilder(\Magento\Sales\Api\OrderRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->companyContext = $this->getMockBuilder(\Magento\Company\Model\CompanyContext::class)
            ->disableOriginalConstructor()
            ->getMock();
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->allowSpecificProductsPlugin = $objectManager->getObject(
            \Magento\NegotiableQuote\Plugin\Sales\Helper\Reorder\AllowSpecificProductsPlugin::class,
            [
                'companyContext' => $this->companyContext,
                'orderRepository' => $this->orderRepository,
            ]
        );
    }

    /**
     * Test aroundCanReorder method for customer without company.
     *
     * @return void
     */
    public function testAroundCanReorderForB2cUser()
    {
        $subject = $this->getMockBuilder(\Magento\Sales\Helper\Reorder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $proceed = function () {
            return true;
        };
        $this->companyContext->expects($this->once())->method('isCurrentUserCompanyUser')->willReturn(false);
        $this->orderRepository->expects($this->never())->method('get');

        $this->assertTrue(
            $this->allowSpecificProductsPlugin->aroundCanReorder($subject, $proceed, 1)
        );
    }

    /**
     * Test aroundCanReorder method for customer with company for order that can unhold.
     *
     * @return void
     */
    public function testAroundCanReorderForUnholdOrder()
    {
        $subject = $this->getMockBuilder(\Magento\Sales\Helper\Reorder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $proceed = function () {
            return true;
        };
        $this->companyContext->expects($this->once())->method('isCurrentUserCompanyUser')->willReturn(true);
        $order = $this->getMockBuilder(\Magento\Sales\Model\Order::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->orderRepository->expects($this->once())->method('get')->willReturn($order);
        $order->expects($this->once())->method('canUnhold')->willReturn(true);

        $this->assertFalse(
            $this->allowSpecificProductsPlugin->aroundCanReorder($subject, $proceed, 1)
        );
    }

    /**
     * Test aroundCanReorder method for customer with company for order that have no reorder flag.
     *
     * @return void
     */
    public function testAroundCanReorderWithActionFlag()
    {
        $subject = $this->getMockBuilder(\Magento\Sales\Helper\Reorder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $proceed = function () {
            return true;
        };
        $this->companyContext->expects($this->once())->method('isCurrentUserCompanyUser')->willReturn(true);
        $order = $this->getMockBuilder(\Magento\Sales\Model\Order::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->orderRepository->expects($this->once())->method('get')->willReturn($order);
        $order->expects($this->once())->method('canUnhold')->willReturn(false);
        $order->expects($this->once())->method('isPaymentReview')->willReturn(false);
        $order->expects($this->once())->method('getActionFlag')
            ->with(\Magento\Sales\Model\Order::ACTION_FLAG_REORDER)->willReturn(false);

        $this->assertFalse(
            $this->allowSpecificProductsPlugin->aroundCanReorder($subject, $proceed, 1)
        );
    }

    /**
     * Test aroundCanReorder method for customer with company for order that available for reorder.
     *
     * @return void
     */
    public function testAroundCanReorderWithAvailableForReorder()
    {
        $subject = $this->getMockBuilder(\Magento\Sales\Helper\Reorder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $proceed = function () {
            return true;
        };
        $this->companyContext->expects($this->once())->method('isCurrentUserCompanyUser')->willReturn(true);
        $order = $this->getMockBuilder(\Magento\Sales\Model\Order::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->orderRepository->expects($this->once())->method('get')->willReturn($order);
        $order->expects($this->once())->method('canUnhold')->willReturn(false);
        $order->expects($this->once())->method('isPaymentReview')->willReturn(false);
        $order->expects($this->once())->method('getActionFlag')
            ->with(\Magento\Sales\Model\Order::ACTION_FLAG_REORDER)->willReturn(true);

        $this->assertTrue(
            $this->allowSpecificProductsPlugin->aroundCanReorder($subject, $proceed, 1)
        );
    }
}
