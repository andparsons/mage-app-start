<?php

namespace Magento\NegotiableQuote\Test\Unit\Block\Order;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Class CreatedByTest
 */
class CreatedByTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var \Magento\NegotiableQuote\Block\Order\CreatedBy|\PHPUnit_Framework_MockObject_MockObject
     */
    private $createdBy;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerRepositoryMock;

    /**
     * @var \Magento\Customer\Api\CustomerNameGenerationInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerViewHelperMock;

    /**
     * Set up
     *
     * @return void
     */
    public function setUp()
    {
        $this->customerRepositoryMock = $this->getMockBuilder(\Magento\Customer\Api\CustomerRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getById'])
            ->getMockForAbstractClass();
        $this->customerViewHelperMock =
            $this->getMockBuilder(\Magento\Customer\Api\CustomerNameGenerationInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCustomerName'])
            ->getMockForAbstractClass();

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->createdBy = $this->objectManagerHelper->getObject(
            \Magento\NegotiableQuote\Block\Order\CreatedBy::class,
            [
                'customerRepository' => $this->customerRepositoryMock,
                'customerViewHelper' => $this->customerViewHelperMock
            ]
        );
    }

    /**
     * Test for getCreatedBy() method
     *
     * @return void
     */
    public function testGetCreatedBy()
    {
        $customerName = 'Peter Parker';
        $orderMock = $this->getMockBuilder(\Magento\Sales\Api\Data\OrderInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCustomerId'])
            ->getMockForAbstractClass();
        $orderMock->expects($this->once())->method('getCustomerId')
            ->willReturn(1);
        $this->createdBy->setOrder($orderMock);
        $customerMock = $this->getMockBuilder(\Magento\Customer\Api\Data\CustomerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->customerRepositoryMock->expects($this->any())
            ->method('getById')
            ->willReturn($customerMock);
        $this->customerViewHelperMock->expects($this->once())
            ->method('getCustomerName')
            ->willReturn($customerName);

        $this->assertEquals($customerName, $this->createdBy->getCreatedBy());
    }
}
