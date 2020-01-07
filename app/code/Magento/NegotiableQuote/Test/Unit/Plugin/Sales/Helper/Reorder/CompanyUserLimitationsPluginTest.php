<?php

namespace Magento\NegotiableQuote\Test\Unit\Plugin\Sales\Helper\Reorder;

/**
 * Test for company limitations plugin check.
 */
class CompanyUserLimitationsPluginTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Authorization\Model\UserContextInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $userContext;

    /**
     * @var \Magento\Company\Model\CompanyContext|\PHPUnit_Framework_MockObject_MockObject
     */
    private $companyContext;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $orderRepository;

    /**
     * @var \Magento\NegotiableQuote\Plugin\Sales\Helper\Reorder\CompanyUserLimitationsPlugin
     */
    private $companyUserLimitationsPlugin;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->userContext = $this->getMockBuilder(\Magento\Authorization\Model\UserContextInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->companyContext = $this->getMockBuilder(\Magento\Company\Model\CompanyContext::class)
            ->disableOriginalConstructor()
            ->setMethods(['isCurrentUserCompanyUser'])
            ->getMockForAbstractClass();
        $this->orderRepository = $this->getMockBuilder(\Magento\Sales\Api\OrderRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->companyUserLimitationsPlugin = $objectManager->getObject(
            \Magento\NegotiableQuote\Plugin\Sales\Helper\Reorder\CompanyUserLimitationsPlugin::class,
            [
                'userContext' => $this->userContext,
                'companyContext' => $this->companyContext,
                'orderRepository' => $this->orderRepository,
            ]
        );
    }

    /**
     * Test aroundCanReorder plugin method.
     *
     * @return void
     */
    public function testAroundCanReorder()
    {
        $orderId = 7;
        $customerId = 17;

        $this->companyContext->expects($this->once())
            ->method('isCurrentUserCompanyUser')
            ->willReturn(true);

        $order = $this->getMockBuilder(\Magento\Sales\Api\Data\OrderInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCustomerId'])
            ->getMockForAbstractClass();
        $order->expects($this->once())->method('getCustomerId')->willReturn($customerId);
        $this->orderRepository->expects($this->once())
            ->method('get')
            ->willReturn($order);
        $this->userContext->expects($this->once())->method('getUserId')->willReturn($customerId);

        $subject = $this->getMockBuilder(\Magento\Sales\Helper\Reorder::class)->disableOriginalConstructor()->getMock();
        $proceed = function () use ($orderId) {
            return true;
        };

        $this->assertTrue($this->companyUserLimitationsPlugin->aroundCanReorder($subject, $proceed, $orderId));
    }

    /**
     * Test aroundCanReorder method for non company users.
     *
     * @return void
     */
    public function testAroundCanReorderForNonCompanyUser()
    {
        $orderId = 7;

        $this->companyContext->expects($this->once())
            ->method('isCurrentUserCompanyUser')
            ->willReturn(false);

        $subject = $this->getMockBuilder(\Magento\Sales\Helper\Reorder::class)->disableOriginalConstructor()->getMock();
        $proceed = function () {
            return true;
        };

        $this->assertTrue($this->companyUserLimitationsPlugin->aroundCanReorder($subject, $proceed, $orderId));
    }
}
