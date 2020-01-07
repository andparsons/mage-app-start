<?php

namespace Magento\NegotiableQuote\Test\Unit\Model\Email\Provider;

/**
 * SalesRepresentative Test.
 */
class SalesRepresentativeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\User\Api\Data\UserInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $userFactory;

    /**
     * @var \Magento\Company\Api\CompanyManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $companyManagement;

    /**
     * @var \Psr\Log\LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $logger;

    /**
     * @var \Magento\NegotiableQuote\Model\Email\Provider\SalesRepresentative
     */
    private $salesRepresentative;

    /**
     * Set up.
     * @return void
     */
    protected function setUp()
    {
        $this->userFactory = $this->getMockBuilder(\Magento\User\Api\Data\UserInterfaceFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->companyManagement = $this->getMockBuilder(\Magento\Company\Api\CompanyManagementInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->logger = $this->getMockBuilder(\Psr\Log\LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->salesRepresentative = $objectManager->getObject(
            \Magento\NegotiableQuote\Model\Email\Provider\SalesRepresentative::class,
            [
                'userFactory' => $this->userFactory,
                'companyManagement' => $this->companyManagement,
                'logger' => $this->logger,
            ]
        );
    }

    /**
     * Test getSalesRepresentativeForQuote().
     * @return void
     */
    public function testGetSalesRepresentativeForQuote()
    {
        $customer = $this->getMockBuilder(
            \Magento\Customer\Api\Data\CustomerInterface::class
        )->disableOriginalConstructor()->getMock();
        $customer->expects($this->atLeastOnce())->method('getId')->willReturn(1);
        $quote = $this->getMockBuilder(
            \Magento\Quote\Api\Data\CartInterface::class
        )->disableOriginalConstructor()->getMock();
        $quote->expects($this->atLeastOnce())->method('getCustomer')->willReturn($customer);
        $company = $this->getMockBuilder(
            \Magento\Company\Api\Data\CompanyInterface::class
        )->disableOriginalConstructor()->getMock();
        $company->expects($this->atLeastOnce())->method('getSalesRepresentativeId')->willReturn(1);
        $this->companyManagement->expects($this->atLeastOnce())->method('getByCustomerId')->willReturn($company);
        $user = $this->getMockBuilder(
            \Magento\User\Api\Data\UserInterface::class
        )
            ->disableOriginalConstructor()
            ->setMethods(['load'])
            ->getMockForAbstractClass();
        $user->expects($this->atLeastOnce())->method('load')->willReturnSelf();
        $this->userFactory->expects($this->atLeastOnce())->method('create')->willReturn($user);
        $this->salesRepresentative->getSalesRepresentativeForQuote($quote);
    }

    /**
     * Test getSalesRepresentativeForQuote() execution with exception thrown.
     * @return void
     */
    public function testGetSalesRepresentativeForQuoteWithException()
    {
        $customer = $this->getMockBuilder(
            \Magento\Customer\Api\Data\CustomerInterface::class
        )->disableOriginalConstructor()->getMock();
        $customer->expects($this->atLeastOnce())->method('getId')->willReturn(1);
        $quote = $this->getMockBuilder(
            \Magento\Quote\Api\Data\CartInterface::class
        )->disableOriginalConstructor()->getMock();
        $quote->expects($this->atLeastOnce())->method('getCustomer')->willReturn($customer);
        $company = $this->getMockBuilder(
            \Magento\Company\Api\Data\CompanyInterface::class
        )->disableOriginalConstructor()->getMock();
        $company->expects($this->never())->method('getSalesRepresentativeId');
        $this->companyManagement->expects($this->atLeastOnce())
            ->method('getByCustomerId')
            ->willThrowException(new \Magento\Framework\Exception\NoSuchEntityException());
        $user = $this->getMockBuilder(
            \Magento\User\Api\Data\UserInterface::class
        )
            ->disableOriginalConstructor()
            ->setMethods(['load'])
            ->getMockForAbstractClass();
        $user->expects($this->never())->method('load');
        $this->userFactory->expects($this->never())->method('create');
        $this->assertNull($this->salesRepresentative->getSalesRepresentativeForQuote($quote));
    }
}
