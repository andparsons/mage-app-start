<?php
namespace Magento\Company\Test\Unit\Block\Adminhtml\Sales\Order\View\Info;

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
     * @var \Magento\Company\Block\Adminhtml\Sales\Order\View\Info\OrderCompanyInfo
     */
    private $orderCompanyInfo;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $orderRepositoryMock;

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
     * @var int
     */
    private $orderId = 1;

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
        $this->requestMock = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->requestMock->expects($this->once())->method('getParam')->with('order_id')->willReturn($this->orderId);
        $this->orderMock = $this->getMockBuilder(\Magento\Sales\Api\Data\OrderInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->orderRepositoryMock->expects($this->once())->method('get')->with($this->orderId)
            ->willReturn($this->orderMock);
        $this->urlBuilderMock = $this->getMockBuilder(\Magento\Framework\UrlInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->orderCompanyInfo = $this->objectManagerHelper->getObject(
            \Magento\Company\Block\Adminhtml\Sales\Order\View\Info\OrderCompanyInfo::class,
            [
                'orderRepository' => $this->orderRepositoryMock,
                '_request' => $this->requestMock,
                '_urlBuilder' => $this->urlBuilderMock
            ]
        );
    }

    /**
     * Test for canShow() method.
     *
     * @dataProvider canShowDataProvider
     * @param int|null $companyId
     * @param boolean $result
     * @return void
     */
    public function testCanShow($companyId, $result)
    {
        $companyOrderAttributesMock = $this->createCompanyOrderAttributesMock();
        $companyOrderAttributesMock->expects($this->once())
            ->method('getCompanyId')
            ->willReturn($companyId);

        $this->assertEquals($result, $this->orderCompanyInfo->canShow());
    }

    /**
     * Data provider for canShow() method.
     *
     * @return array
     */
    public function canShowDataProvider()
    {
        return [
            [1, true],
            [null, false]
        ];
    }

    /**
     * Test for getCompanyName().
     *
     * @return void
     */
    public function testGetCompanyName()
    {
        $companyName = 'test';
        $companyOrderAttributesMock = $this->createCompanyOrderAttributesMock();
        $companyOrderAttributesMock->expects($this->once())->method('getCompanyName')->willReturn($companyName);

        $this->assertEquals($companyName, $this->orderCompanyInfo->getCompanyName());
    }

    /**
     * Test for getCompanyUrl().
     *
     * @return void
     */
    public function testGetCompanyUrl()
    {
        $companyId = 1;
        $companyUrl = 'test.com/' . $companyId;
        $companyOrderAttributesMock = $this->createCompanyOrderAttributesMock();
        $companyOrderAttributesMock->expects($this->once())->method('getCompanyId')->willReturn($companyId);
        $this->urlBuilderMock->expects($this->once())->method('getUrl')
            ->with(
                'company/index/edit',
                ['_secure' => true, 'id' => $companyId]
            )
            ->willReturn($companyUrl);

        $this->assertEquals($companyUrl, $this->orderCompanyInfo->getCompanyUrl());
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
