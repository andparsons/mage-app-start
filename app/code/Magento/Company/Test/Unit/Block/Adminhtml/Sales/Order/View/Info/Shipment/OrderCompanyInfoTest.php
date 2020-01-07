<?php
namespace Magento\Company\Test\Unit\Block\Adminhtml\Sales\Order\View\Info\Shipment;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Unit tests for OrderCompanyInfo object which is responsible for displaying company info on order view page.
 */
class OrderCompanyInfoTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var \Magento\Company\Block\Adminhtml\Sales\Order\View\Info\Creditmemo\OrderCompanyInfo
     */
    private $orderCompanyInfo;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $orderRepositoryMock;

    /**
     * @var \Magento\Sales\Api\ShipmentRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $shipmentRepositoryMock;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requestMock;

    /**
     * @var \Magento\Sales\Api\Data\OrderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $orderMock;

    /**
     * @var \Magento\Framework\UrlInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $urlBuilderMock;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->orderRepositoryMock = $this->getMockBuilder(\Magento\Sales\Api\OrderRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->shipmentRepositoryMock = $this->getMockBuilder(\Magento\Sales\Api\ShipmentRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->requestMock = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->orderMock = $this->getMockBuilder(\Magento\Sales\Api\Data\OrderInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->urlBuilderMock = $this->getMockBuilder(\Magento\Framework\UrlInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->orderCompanyInfo = $this->objectManagerHelper->getObject(
            \Magento\Company\Block\Adminhtml\Sales\Order\View\Info\Shipment\OrderCompanyInfo::class,
            [
                'orderRepository' => $this->orderRepositoryMock,
                'shipmentRepository' => $this->shipmentRepositoryMock,
                '_request' => $this->requestMock,
                '_urlBuilder' => $this->urlBuilderMock
            ]
        );
    }

    /**
     * Test for getOrder() method.
     *
     * @return void
     */
    public function testGetOrder()
    {
        $companyId = 1;
        $shipmentId = 1;
        $orderId = 1;

        $this->requestMock->expects($this->once())->method('getParam')->with('shipment_id')
            ->willReturn($shipmentId);
        $shipmentMock = $this->getMockBuilder(\Magento\Sales\Api\Data\ShipmentInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->shipmentRepositoryMock->expects($this->once())->method('get')->with($shipmentId)
            ->willReturn($shipmentMock);
        $shipmentMock->expects($this->once())->method('getOrderId')->willReturn($orderId);
        $this->orderRepositoryMock->expects($this->once())->method('get')->with($orderId)
            ->willReturn($this->orderMock);

        $companyOrderAttributesMock = $this->createCompanyOrderAttributesMock();
        $companyOrderAttributesMock->expects($this->once())
            ->method('getCompanyId')
            ->willReturn($companyId);

        $this->assertEquals(true, $this->orderCompanyInfo->canShow());
    }

    /**
     * Create company order attributes mock.
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function createCompanyOrderAttributesMock()
    {
        $orderExtensionAttributesMock = $this->getMockBuilder(\Magento\Sales\Api\Data\OrderExtensionInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCompanyOrderAttributes'])
            ->getMockForAbstractClass();
        $this->orderMock->expects($this->atLeastOnce())->method('getExtensionAttributes')
            ->willReturn($orderExtensionAttributesMock);
        $companyOrderAttributesMock = $this->getMockBuilder(\Magento\Company\Api\Data\CompanyOrderInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $orderExtensionAttributesMock->expects($this->atLeastOnce())->method('getCompanyOrderAttributes')
            ->willReturn($companyOrderAttributesMock);

        return $companyOrderAttributesMock;
    }
}
