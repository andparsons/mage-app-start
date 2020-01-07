<?php

namespace Magento\NegotiableQuote\Test\Unit\Block\Order;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Class OwnerFilterTest.
 */
class OwnerFilterTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var \Magento\NegotiableQuote\Block\Order\OwnerFilter|\PHPUnit_Framework_MockObject_MockObject
     */
    private $ownerFilter;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requestMock;

    /**
     * @var \Magento\Authorization\Model\UserContextInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerContextMock;

    /**
     * @var \Magento\Company\Model\Company\Structure|\PHPUnit_Framework_MockObject_MockObject
     */
    private $structureMock;

    /**
     * @var \Magento\Company\Api\AuthorizationInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $authorization;

    /**
     * @var \Magento\Company\Api\StatusServiceInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $companyModuleConfig;

    /**
     * Set up.
     *
     * @return void
     */
    public function setUp()
    {
        $this->requestMock = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getParam'])
            ->getMockForAbstractClass();
        $this->customerContextMock = $this->getMockBuilder(\Magento\Authorization\Model\UserContextInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getUserId'])
            ->getMockForAbstractClass();
        $this->structureMock = $this->getMockBuilder(\Magento\Company\Model\Company\Structure::class)
            ->disableOriginalConstructor()
            ->setMethods(['getAllowedChildrenIds'])
            ->getMock();
        $this->authorization = $this->getMockBuilder(\Magento\Company\Api\AuthorizationInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['isAllowed'])
            ->getMockForAbstractClass();

        $this->companyModuleConfig = $this->getMockBuilder(\Magento\Company\Api\StatusServiceInterface::class)
            ->setMethods(['isActive'])
            ->disableOriginalConstructor()->getMockForAbstractClass();

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->ownerFilter = $this->objectManagerHelper->getObject(
            \Magento\NegotiableQuote\Block\Order\OwnerFilter::class,
            [
                'customerContext' => $this->customerContextMock,
                'structure' => $this->structureMock,
                'authorization' => $this->authorization,
                'request' => $this->requestMock,
                'companyModuleConfig' => $this->companyModuleConfig
            ]
        );
    }

    /**
     * Test for isViewAll() method.
     *
     * @return void
     */
    public function testIsViewAllOrders()
    {
        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('created_by')
            ->willReturn('all');

        $this->assertEquals(true, $this->ownerFilter->isViewAll());
    }

    /**
     * Test for canShow() method.
     *
     * @param bool $expected
     * @param bool $companyModuleIsActive
     * @param array $calls
     * @dataProvider canShowDataProvider
     * @return void
     */
    public function testCanShow($expected, $companyModuleIsActive, array $calls)
    {
        $subCustomers = [1,2];

        $customerId = 1;
        $this->customerContextMock->expects($this->any())
            ->method('getUserId')
            ->willReturn($customerId);

        $this->companyModuleConfig->expects($this->exactly(1))->method('isActive')->willReturn($companyModuleIsActive);

        $this->structureMock->expects($this->exactly($calls['structure_getAllowedChildrenIds']))
            ->method('getAllowedChildrenIds')
            ->with($customerId)
            ->willReturn($subCustomers);
        $this->authorization->expects($this->any())->method('isAllowed')->willReturn($expected);

        $this->assertEquals($expected, $this->ownerFilter->canShow());
    }

    /**
     * DataProvider for testAdminSend.
     *
     * @return array
     */
    public function canShowDataProvider()
    {
        return [
            [
                false, false, ['structure_getAllowedChildrenIds' => 0]
            ],
            [
                true, true, ['structure_getAllowedChildrenIds' => 1]
            ]
        ];
    }
}
