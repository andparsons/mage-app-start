<?php
namespace Magento\Company\Test\Unit\Block\Link;

/**
 * Unit test for OrdersLink block.
 */
class OrdersLinkTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Company\Model\CompanyContext|\PHPUnit_Framework_MockObject_MockObject
     */
    private $companyContext;

    /**
     * @var \Magento\Company\Api\CompanyManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $companyManagement;

    /**
     * @var string
     */
    private $resource = 'view_link_resource';

    /**
     * @var \Magento\Company\Block\Link\OrdersLink
     */
    private $ordersLink;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->companyContext = $this->getMockBuilder(\Magento\Company\Model\CompanyContext::class)
            ->disableOriginalConstructor()->getMock();
        $this->companyManagement = $this->getMockBuilder(\Magento\Company\Api\CompanyManagementInterface::class)
            ->disableOriginalConstructor()->getMock();
        $request = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->setMethods(['getControllerName', 'getPathInfo'])
            ->disableOriginalConstructor()->getMockForAbstractClass();

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->ordersLink = $objectManagerHelper->getObject(
            \Magento\Company\Block\Link\OrdersLink::class,
            [
                'companyContext' => $this->companyContext,
                'companyManagement' => $this->companyManagement,
                '_request' => $request,
                'data' => ['resource' => $this->resource],
            ]
        );
    }

    /**
     * Test for toHtml method.
     *
     * @param bool $isAllowed
     * @param string $expectedResult
     * @return void
     * @dataProvider toHtmlDataProvider
     */
    public function testToHtml($isAllowed, $expectedResult)
    {
        $customerId = 1;
        $this->companyContext->expects($this->atLeastOnce())->method('getCustomerId')->willReturn($customerId);
        $company = $this->getMockBuilder(\Magento\Company\Api\CompanyInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->companyManagement->expects($this->once())
            ->method('getByCustomerId')->with($customerId)->willReturn($company);
        $this->companyContext->expects($this->once())
            ->method('isResourceAllowed')->with($this->resource)->willReturn($isAllowed);
        $this->assertEquals($expectedResult, $this->ordersLink->toHtml());
    }

    /**
     * Data provider for testToHtml.
     *
     * @return array
     */
    public function toHtmlDataProvider()
    {
        return [
            [true, '<li class="nav item current"><strong></strong></li>'],
            [false, ''],
        ];
    }
}
