<?php
namespace Magento\NegotiableQuote\Test\Unit\Model;

use \Magento\NegotiableQuote\Model\HistoryManagementInterface;
use \Magento\NegotiableQuote\Model\Discount\StateChanges\Applier;

/**
 * Unit test for rule checker model.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RuleCheckerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    private $objectManagerHelper;

    /**
     * @var \Magento\NegotiableQuote\Model\RuleChecker
     */
    private $ruleChecker;

    /**
     * @var \Magento\NegotiableQuote\Helper\Quote|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteHelperMock;

    /**
     * @var \Magento\SalesRule\Model\Rule\CustomerFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerFactoryMock;

    /**
     * @var \Magento\NegotiableQuote\Model\HistoryManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $historyManagementMock;

    /**
     * @var \Magento\SalesRule\Api\RuleRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $ruleRepositoryMock;

    /**
     * @var \Magento\NegotiableQuote\Model\Discount\StateChanges\Applier|\PHPUnit_Framework_MockObject_MockObject
     */
    private $messageApplierMock;

    /**
     * @var \Magento\Quote\Api\Data\CartInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteMock;

    /**
     * Set up.
     *
     * @return void
     */
    public function setUp()
    {
        $this->quoteHelperMock = $this->getMockBuilder(\Magento\NegotiableQuote\Helper\Quote::class)
            ->disableOriginalConstructor()
            ->setMethods(['formatPrice'])
            ->getMock();
        $this->customerFactoryMock = $this->getMockBuilder(\Magento\SalesRule\Model\Rule\CustomerFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->historyManagementMock = $this->getMockBuilder(HistoryManagementInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['addCustomLog'])
            ->getMockForAbstractClass();
        $this->ruleRepositoryMock = $this->getMockBuilder(\Magento\SalesRule\Api\RuleRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->messageApplierMock = $this->getMockBuilder(Applier::class)
            ->disableOriginalConstructor()
            ->setMethods(['setIsDiscountRemovedLimit', 'setIsDiscountRemoved'])
            ->getMock();
        $this->quoteMock = $this->getMockBuilder(\Magento\Quote\Api\Data\CartInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getId',
                'getExtensionAttributes',
                'getCustomer'
            ])
            ->getMockForAbstractClass();
        $this->quoteMock->expects($this->atLeastOnce())->method('getId')->willReturn(1);

        $this->objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->ruleChecker = $this->objectManagerHelper->getObject(
            \Magento\NegotiableQuote\Model\RuleChecker::class,
            [
                'quoteHelper' => $this->quoteHelperMock,
                'customerFactory' => $this->customerFactoryMock,
                'historyManagement' => $this->historyManagementMock,
                'ruleRepository' => $this->ruleRepositoryMock,
                'messageApplier' => $this->messageApplierMock
            ]
        );
    }

    /**
     * Test for checkIsDiscountRemoved() method.
     *
     * @dataProvider checkIsDiscountRemovedDataProvider
     * @param bool $isUsageLimitReached
     * @param bool $discountByPercent
     * @param int $formatPriceCalls
     * @return void
     */
    public function testCheckIsDiscountRemoved($isUsageLimitReached, $discountByPercent, $formatPriceCalls)
    {
        $getTimesUsed = 1;
        $getUsedPerCustomer = 2;
        $discountType = 'dummy';
        $oldRuleIds = '1,2';
        $newRuleIdsArray = '3';
        $result = true;

        if ($isUsageLimitReached) {
            $getTimesUsed = 100;
            $this->messageApplierMock->expects($this->once())->method('setIsDiscountRemovedLimit');
        } else {
            $this->messageApplierMock->expects($this->once())->method('setIsDiscountRemoved');
        }

        if ($discountByPercent) {
            $discountType = \Magento\SalesRule\Api\Data\RuleInterface::DISCOUNT_ACTION_BY_PERCENT;
        }

        $negotiableQuoteMock = $this->getNegotiableQuoteMock();
        $negotiableQuoteMock->expects($this->atLeastOnce())->method('getAppliedRuleIds')->willReturn($newRuleIdsArray);
        $ruleMock = $this->getMockBuilder(\Magento\SalesRule\Api\Data\RuleInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getRuleId', 'getUsesPerCustomer', 'getSimpleAction', 'getDiscountAmount'])
            ->getMockForAbstractClass();
        $this->ruleRepositoryMock->expects($this->atLeastOnce())->method('getById')->willReturn($ruleMock);
        $ruleMock->expects($this->atLeastOnce())->method('getRuleId')->willReturn(1);
        $ruleMock->expects($this->atLeastOnce())->method('getUsesPerCustomer')->willReturn($getUsedPerCustomer);
        $customerMock = $this->getMockBuilder(\Magento\Customer\Api\Data\CustomerInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId'])
            ->getMockForAbstractClass();
        $this->quoteMock->expects($this->atLeastOnce())->method('getCustomer')->willReturn($customerMock);
        $customerMock->expects($this->atLeastOnce())->method('getId')->willReturn(1);
        $ruleCustomerMock = $this->getMockBuilder(\Magento\SalesRule\Model\Rule\Customer::class)
            ->disableOriginalConstructor()
            ->setMethods(['loadByCustomerRule', 'getId', 'getTimesUsed'])
            ->getMock();
        $this->customerFactoryMock->expects($this->atLeastOnce())->method('create')->willReturn($ruleCustomerMock);
        $ruleCustomerMock->expects($this->atLeastOnce())->method('loadByCustomerRule')->willReturnSelf();
        $ruleCustomerMock->expects($this->atLeastOnce())->method('getId')->willReturn(1);
        $ruleCustomerMock->expects($this->atLeastOnce())->method('getTimesUsed')->willReturn($getTimesUsed);
        $ruleMock->expects($this->atLeastOnce())->method('getSimpleAction')
            ->willReturn($discountType);
        $ruleMock->expects($this->atLeastOnce())->method('getDiscountAmount')->willReturn(10);
        $this->quoteHelperMock->expects($this->exactly($formatPriceCalls))->method('formatPrice')->willReturn('5');
        $this->historyManagementMock->expects($this->once())->method('addCustomLog');

        $this->assertEquals($result, $this->ruleChecker->checkIsDiscountRemoved($this->quoteMock, $oldRuleIds, true));
    }

    /**
     * Test for checkIsDiscountRemoved method with exception.
     *
     * @return void
     */
    public function testCheckIsDiscountRemovedWithException()
    {
        $oldRuleIds = '1,2';
        $newRuleIdsArray = '3';
        $negotiableQuoteMock = $this->getNegotiableQuoteMock();
        $negotiableQuoteMock->expects($this->once())->method('getAppliedRuleIds')->willReturn($newRuleIdsArray);
        $this->ruleRepositoryMock->expects($this->once())->method('getById')
            ->willThrowException(new \Magento\Framework\Exception\NoSuchEntityException());
        $this->messageApplierMock->expects($this->once())
            ->method('setIsDiscountRemoved')->with($this->quoteMock)->willReturnSelf();
        $this->historyManagementMock->expects($this->once())->method('addCustomLog')->with(
            1,
            [
                [
                    'field_title' => 'Quote Discount',
                    'field_id' => 'discount',
                    'values' => [['new_value' => 'Cart rule deleted.', 'field_id' => 'rule_remove']]
                ]
            ],
            false,
            true
        );
        $this->assertTrue($this->ruleChecker->checkIsDiscountRemoved($this->quoteMock, $oldRuleIds, true));
    }

    /**
     * Get negotiable quote mock.
     *
     * @return \Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getNegotiableQuoteMock()
    {
        $quoteExtensionAttributesMock = $this->getMockBuilder(\Magento\Quote\Api\Data\CartExtensionInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getNegotiableQuote'])
            ->getMockForAbstractClass();
        $negotiableQuoteMock = $this->getMockBuilder(\Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getAppliedRuleIds'])
            ->getMockForAbstractClass();
        $quoteExtensionAttributesMock->expects($this->atLeastOnce())
            ->method('getNegotiableQuote')
            ->willReturn($negotiableQuoteMock);
        $this->quoteMock->expects($this->atLeastOnce())->method('getExtensionAttributes')
            ->willReturn($quoteExtensionAttributesMock);

        return $negotiableQuoteMock;
    }

    /**
     * Data provider for testCheckIsDiscountRemoved test.
     *
     * @return array
     */
    public function checkIsDiscountRemovedDataProvider()
    {
        return [
            [true, true, 0],
            [false, false, 1]
        ];
    }
}
