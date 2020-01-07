<?php

namespace Magento\NegotiableQuote\Test\Unit\Block\Order\Info;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Class CreationInfoTest
 */
class CreationInfoTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var \Magento\NegotiableQuote\Block\Order\Info\CreationInfo|\PHPUnit_Framework_MockObject_MockObject
     */
    private $creationInfo;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $orderRepositoryMock;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerRepositoryMock;

    /**
     * @var \Magento\Customer\Api\CustomerNameGenerationInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerViewHelperMock;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requestMock;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $localeDateMock;

    /**
     * Set up
     *
     * @return void
     */
    public function setUp()
    {
        $this->orderRepositoryMock = $this->getMockBuilder(\Magento\Sales\Api\OrderRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['get'])
            ->getMockForAbstractClass();
        $this->customerRepositoryMock = $this->getMockBuilder(\Magento\Customer\Api\CustomerRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->customerViewHelperMock =
            $this->getMockBuilder(\Magento\Customer\Api\CustomerNameGenerationInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->requestMock = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getParam'])
            ->getMockForAbstractClass();
        $this->localeDateMock = $this->getMockBuilder(\Magento\Framework\Stdlib\DateTime\TimezoneInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['formatDateTime'])
            ->getMockForAbstractClass();

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->creationInfo = $this->objectManagerHelper->getObject(
            \Magento\NegotiableQuote\Block\Order\Info\CreationInfo::class,
            [
                'orderRepository' => $this->orderRepositoryMock,
                'customerRepository' => $this->customerRepositoryMock,
                'customerViewHelper' => $this->customerViewHelperMock,
                'request' => $this->requestMock,
                'localeDate' => $this->localeDateMock,
                'data' => []
            ]
        );
    }

    /**
     * Test for getCreationInfo() method
     *
     * @return void
     */
    public function testGetCreationInfo()
    {
        $createdAt = date('Y-m-d H:i:s');
        $customerName = 'Peter Parker';
        $result = $createdAt . ' (' . $customerName . ')';

        $this->requestMock->expects($this->any())
            ->method('getParam')
            ->willReturn(1);
        $orderMock = $this->getMockBuilder(\Magento\Sales\Api\Data\OrderInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCustomerId', 'getEntityId', 'getCreatedAt'])
            ->getMockForAbstractClass();
        $orderMock->expects($this->any())->method('getCustomerId')->willReturn(1);
        $orderMock->expects($this->any())->method('getEntityId')->willReturn(1);
        $orderMock->expects($this->any())->method('getCreatedAt')->willReturn($createdAt);
        $this->orderRepositoryMock->expects($this->any())
            ->method('get')
            ->willReturn($orderMock);
        $customerMock = $this->getMockBuilder(\Magento\Customer\Api\Data\CustomerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->customerRepositoryMock->expects($this->any())
            ->method('getById')
            ->willReturn($customerMock);
        $this->customerViewHelperMock->expects($this->once())
            ->method('getCustomerName')
            ->willReturn($customerName);
        $this->localeDateMock->expects($this->any())
            ->method('formatDateTime')
            ->willReturn($createdAt);

        $this->assertEquals($result, $this->creationInfo->getCreationInfo());
    }
}
