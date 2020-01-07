<?php

namespace Magento\NegotiableQuote\Test\Unit\Model\Restriction;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Class CustomerTest
 */
class CustomerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var \Magento\NegotiableQuote\Model\Restriction\Customer|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customer;

    /**
     * @var \Magento\Authorization\Model\UserContextInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $userContextMock;

    /**
     * @var \Magento\Company\Model\Company\Structure|\PHPUnit_Framework_MockObject_MockObject
     */
    private $structureMock;

    /**
     * Set up
     *
     * @return void
     */
    public function setUp()
    {
        $this->userContextMock = $this->getMockBuilder(\Magento\Authorization\Model\UserContextInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getUserId'])
            ->getMockForAbstractClass();
        $this->structureMock = $this->getMockBuilder(\Magento\Company\Model\Company\Structure::class)
            ->disableOriginalConstructor()
            ->setMethods(['getAllowedChildrenIds'])
            ->getMock();

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->customer = $this->objectManagerHelper->getObject(
            \Magento\NegotiableQuote\Model\Restriction\Customer::class,
            [
                'userContext' => $this->userContextMock,
                'structure' => $this->structureMock
            ]
        );
    }

    /**
     * Test for isOwner() method
     *
     * @return void
     */
    public function testIsOwner()
    {
        $customerId = 1;
        $this->userContextMock->expects($this->any())
            ->method('getUserId')
            ->willReturn($customerId);
        $quoteMock = $this->getMockBuilder(\Magento\Quote\Api\Data\CartInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCustomer'])
            ->getMockForAbstractClass();
        $customerMock = $this->getMockBuilder(\Magento\Customer\Api\Data\CustomerInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId'])
            ->getMockForAbstractClass();
        $customerMock->expects($this->any())
            ->method('getId')
            ->willReturn($customerId);
        $quoteMock->expects($this->any())
            ->method('getCustomer')
            ->willReturn($customerMock);

        $this->customer->setQuote($quoteMock);
        $this->assertEquals(true, $this->customer->isOwner());
    }

    /**
     * Test for isOwner() method
     *
     * @return void
     */
    public function testIsSubUserContent()
    {
        $customerId = 1;
        $this->userContextMock->expects($this->any())
            ->method('getUserId')
            ->willReturn($customerId);
        $quoteMock = $this->getMockBuilder(\Magento\Quote\Api\Data\CartInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCustomer'])
            ->getMockForAbstractClass();
        $customerMock = $this->getMockBuilder(\Magento\Customer\Api\Data\CustomerInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId'])
            ->getMockForAbstractClass();
        $customerMock->expects($this->any())
            ->method('getId')
            ->willReturn($customerId);
        $quoteMock->expects($this->any())
            ->method('getCustomer')
            ->willReturn($customerMock);

        $this->customer->setQuote($quoteMock);
        $this->structureMock->expects($this->any())
            ->method('getAllowedChildrenIds')
            ->willReturn([
                1,
                2
            ]);

        $this->assertEquals(true, $this->customer->isSubUserContent());
    }
}
