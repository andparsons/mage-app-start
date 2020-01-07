<?php

namespace Magento\CompanyCredit\Test\Unit\Model;

use Magento\CompanyCredit\Model\CreditBalanceOptionsFactory;

/**
 * Unit tests for CreditBalance model.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CreditBalanceTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\CompanyCredit\Api\CreditLimitManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $creditLimitManagement;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $priceCurrency;

    /**
     * @var \Magento\CompanyCredit\Model\CompanyStatus|\PHPUnit_Framework_MockObject_MockObject
     */
    private $companyStatus;

    /**
     * @var \Magento\CompanyCredit\Api\CreditBalanceManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $creditBalanceManagement;

    /**
     * @var \Magento\CompanyCredit\Model\CompanyOrder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $companyOrder;

    /**
     * @var \Magento\CompanyCredit\Model\CreditBalanceOptionsFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $creditBalanceOptionsFactory;

    /**
     * @var \Magento\CompanyCredit\Model\CreditBalance
     */
    private $creditBalance;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->creditLimitManagement = $this->createMock(
            \Magento\CompanyCredit\Api\CreditLimitManagementInterface::class
        );
        $this->priceCurrency = $this->createMock(
            \Magento\Framework\Pricing\PriceCurrencyInterface::class
        );
        $this->companyStatus = $this->createMock(
            \Magento\CompanyCredit\Model\CompanyStatus::class
        );
        $this->creditBalanceManagement = $this->createMock(
            \Magento\CompanyCredit\Api\CreditBalanceManagementInterface::class
        );
        $this->companyOrder = $this->createMock(
            \Magento\CompanyCredit\Model\CompanyOrder::class
        );
        $this->creditBalanceOptionsFactory = $this->getMockBuilder(CreditBalanceOptionsFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->creditBalance = $objectManager->getObject(
            \Magento\CompanyCredit\Model\CreditBalance::class,
            [
                'creditLimitManagement' => $this->creditLimitManagement,
                'priceCurrency' => $this->priceCurrency,
                'companyStatus' => $this->companyStatus,
                'creditBalanceManagement' => $this->creditBalanceManagement,
                'companyOrder' => $this->companyOrder,
                'creditBalanceOptionsFactory' => $this->creditBalanceOptionsFactory
            ]
        );
    }

    /**
     * Test for method decreaseBalanceByOrder.
     *
     * @return void
     */
    public function testDecreaseBalanceByOrder()
    {
        $companyId = 2;
        $creditLimitId = 3;
        $orderId = 'O001';
        $poNumber = 'PO-001';
        $orderTotal = 12.5;
        $rate = 1.4;
        $orderCurrency = 'EUR';
        $baseCurrency = 'RUB';
        $creditCurrency = 'USD';
        $order = $this->createMock(\Magento\Sales\Api\Data\OrderInterface::class);
        $this->companyOrder->expects($this->once())
            ->method('getCompanyIdByOrder')->with($order)->willReturn($companyId);
        $creditLimit = $this->createMock(\Magento\CompanyCredit\Api\Data\CreditLimitInterface::class);
        $this->creditLimitManagement->expects($this->once())
            ->method('getCreditByCompanyId')->with($companyId)->willReturn($creditLimit);
        $creditLimit->expects($this->exactly(2))->method('getId')->willReturn($creditLimitId);
        $creditLimit->expects($this->once())->method('getExceedLimit')->willReturn(false);
        $order->expects($this->exactly(2))->method('getBaseGrandTotal')->willReturn($orderTotal);
        $order->expects($this->exactly(3))->method('getBaseCurrencyCode')->willReturn($baseCurrency);
        $order->expects($this->exactly(2))->method('getOrderCurrencyCode')->willReturn($orderCurrency);
        $currency = $this->createMock(\Magento\Directory\Model\Currency::class);
        $creditLimit->expects($this->atLeastOnce())->method('getCurrencyCode')->willReturn($creditCurrency);
        $this->priceCurrency->expects($this->once())
            ->method('getCurrency')->with(true, $baseCurrency)->willReturn($currency);
        $currency->expects($this->once())->method('getRate')->with($creditCurrency)->willReturn($rate);
        $currency->expects($this->once())
            ->method('convert')->with($orderTotal, $creditCurrency)->willReturn($orderTotal * $rate);
        $creditLimit->expects($this->once())->method('getAvailableLimit')->willReturn(100);
        $order->expects($this->once())->method('getIncrementId')->willReturn($orderId);
        $creditBalanceOptions = $this->getMockBuilder(\Magento\CompanyCredit\Model\CreditBalanceOptions::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->creditBalanceOptionsFactory->expects($this->any())->method('create')
            ->willReturn($creditBalanceOptions);
        $creditBalanceOptions->expects($this->exactly(4))->method('setData')->withConsecutive(
            ['purchase_order', $poNumber],
            ['order_increment', $orderId],
            ['currency_display', $orderCurrency],
            ['currency_base', $baseCurrency]
        );

        $this->creditBalanceManagement->expects($this->once())->method('decrease')
            ->with(
                $creditLimitId,
                $orderTotal,
                $baseCurrency,
                \Magento\CompanyCredit\Model\HistoryInterface::TYPE_PURCHASED,
                '',
                $creditBalanceOptions
            );
        $this->creditBalance->decreaseBalanceByOrder($order, $poNumber);
    }

    /**
     * Test for method decreaseBalanceByOrder with exception about unavailable payment method.
     *
     * @return void
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage The requested Payment Method is not available.
     */
    public function testDecreaseBalanceByOrderWithExceptionAboutUnavailableMethod()
    {
        $companyId = 2;
        $order = $this->getMockBuilder(\Magento\Sales\Api\Data\OrderInterface::class)->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->companyOrder->expects($this->once())
            ->method('getCompanyIdByOrder')->with($order)->willReturn($companyId);
        $creditLimit = $this->getMockBuilder(\Magento\CompanyCredit\Api\Data\CreditLimitInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->creditLimitManagement->expects($this->once())
            ->method('getCreditByCompanyId')->with($companyId)->willReturn($creditLimit);
        $creditLimit->expects($this->once())->method('getId')->willReturn(null);
        $this->creditBalance->decreaseBalanceByOrder($order);
    }

    /**
     * Test for method decreaseBalanceByOrder with exception about exceeded limit.
     *
     * @return void
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Payment On Account cannot be used for this order because your order amount exceeds your
     * credit amount.
     */
    public function testDecreaseBalanceByOrderWithExceptionAboutExceededLimit()
    {
        $companyId = 2;
        $order = $this->getMockBuilder(\Magento\Sales\Api\Data\OrderInterface::class)->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->companyOrder->expects($this->once())
            ->method('getCompanyIdByOrder')->with($order)->willReturn($companyId);
        $creditLimit = $this->getMockBuilder(\Magento\CompanyCredit\Api\Data\CreditLimitInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->creditLimitManagement->expects($this->once())
            ->method('getCreditByCompanyId')->with($companyId)->willReturn($creditLimit);
        $creditLimit->expects($this->once())->method('getId')->willReturn(1);
        $creditLimit->expects($this->once())->method('getExceedlimit')->willReturn(false);
        $order->expects($this->once())->method('getBaseGrandTotal')->willReturn(5);
        $order->expects($this->once())->method('getOrderCurrencyCode')->willReturn('USD');
        $creditLimit->expects($this->once())->method('getCurrencyCode')->willReturn('USD');
        $creditLimit->expects($this->once())->method('getAvailableLimit')->willReturn(4);

        $this->creditBalance->decreaseBalanceByOrder($order);
    }

    /**
     * Test for method increaseBalanceByOrder.
     *
     * @return void
     */
    public function testIncreaseBalanceByOrder()
    {
        $companyId = 2;
        $creditLimitId = 3;
        $orderTotal = 12.5;
        $orderCurrency = 'USD';
        $baseCurrency = 'RUB';
        $orderId = 'O001';
        $order = $this->createMock(\Magento\Sales\Api\Data\OrderInterface::class);
        $this->companyOrder->expects($this->once())
            ->method('getCompanyIdByOrder')->with($order)->willReturn($companyId);
        $creditLimit = $this->createMock(\Magento\CompanyCredit\Api\Data\CreditLimitInterface::class);
        $this->creditLimitManagement->expects($this->once())
            ->method('getCreditByCompanyId')->with($companyId)->willReturn($creditLimit);
        $creditLimit->expects($this->once())->method('getId')->willReturn($creditLimitId);
        $order->expects($this->once())->method('getBaseGrandTotal')->willReturn($orderTotal);
        $order->expects($this->exactly(2))->method('getBaseCurrencyCode')->willReturn($baseCurrency);
        $order->expects($this->once())->method('getOrderCurrencyCode')->willReturn($orderCurrency);
        $order->expects($this->once())->method('getIncrementId')->willReturn($orderId);
        $creditBalanceOptions = $this->prepareCreditBalanceOptionsMock($orderId, $orderCurrency, $baseCurrency);
        $this->creditBalanceManagement->expects($this->once())->method('increase')
            ->with(
                $creditLimitId,
                $orderTotal,
                $baseCurrency,
                \Magento\CompanyCredit\Model\HistoryInterface::TYPE_REVERTED,
                '',
                $creditBalanceOptions
            );

        $this->creditBalance->increaseBalanceByOrder($order);
    }

    /**
     * Test for method cancel without company.
     *
     * @return void
     */
    public function testCancelWithoutCompany()
    {
        $order = $this->createMock(\Magento\Sales\Api\Data\OrderInterface::class);
        $this->companyOrder->expects($this->once())
            ->method('getCompanyIdByOrder')->with($order)->willReturn(null);

        $this->creditBalanceManagement->expects($this->never())->method('increase');

        $this->assertFalse($this->creditBalance->cancel($order));
    }

    /**
     * Test for method cancel with company.
     *
     * @return void
     */
    public function testCancel()
    {
        $companyId = 2;
        $creditLimitId = 3;
        $orderTotal = 12.5;
        $orderCurrency = 'USD';
        $baseCurrency = 'RUB';
        $orderId = 'O001';
        $order = $this->createMock(\Magento\Sales\Api\Data\OrderInterface::class);
        $this->companyStatus->expects($this->once())
            ->method('isRevertAvailable')->with($companyId)->willReturn(true);
        $this->companyOrder->expects($this->exactly(2))
            ->method('getCompanyIdByOrder')->with($order)->willReturn($companyId);
        $creditLimit = $this->createMock(\Magento\CompanyCredit\Api\Data\CreditLimitInterface::class);
        $this->creditLimitManagement->expects($this->once())
            ->method('getCreditByCompanyId')->with($companyId)->willReturn($creditLimit);
        $creditLimit->expects($this->once())->method('getId')->willReturn($creditLimitId);
        $order->expects($this->once())->method('getBaseGrandTotal')->willReturn($orderTotal);
        $order->expects($this->exactly(2))->method('getBaseCurrencyCode')->willReturn($baseCurrency);
        $order->expects($this->once())->method('getOrderCurrencyCode')->willReturn($orderCurrency);
        $order->expects($this->once())->method('getIncrementId')->willReturn($orderId);
        $creditBalanceOptions = $this->prepareCreditBalanceOptionsMock($orderId, $orderCurrency, $baseCurrency);
        $this->creditBalanceManagement->expects($this->once())->method('increase')
            ->with(
                $creditLimitId,
                $orderTotal,
                $baseCurrency,
                \Magento\CompanyCredit\Model\HistoryInterface::TYPE_REVERTED,
                '',
                $creditBalanceOptions
            );

        $this->creditBalance->cancel($order);
    }

    /**
     * Test for method refund.
     *
     * @return void
     */
    public function testRefund()
    {
        $companyId = 1;
        $creditLimitId = 2;
        $creditmemoTotal = 15.5;
        $creditmemoCurrency = 'USD';
        $orderId = '001';
        $commentText = 'Refund Comment';
        $orderCurrency = 'USD';
        $baseCurrency = 'RUB';
        $order = $this->createMock(\Magento\Sales\Api\Data\OrderInterface::class);
        $creditmemo = $this->createMock(\Magento\Sales\Api\Data\CreditmemoInterface::class);
        $this->companyOrder->expects($this->once())
            ->method('getCompanyIdForRefund')->with($order)->willReturn($companyId);
        $creditLimit = $this->createMock(\Magento\CompanyCredit\Api\Data\CreditLimitInterface::class);
        $this->creditLimitManagement->expects($this->once())
            ->method('getCreditByCompanyId')->with($companyId)->willReturn($creditLimit);
        $creditLimit->expects($this->once())->method('getId')->willReturn($creditLimitId);
        $creditmemo->expects($this->once())->method('getBaseGrandTotal')->willReturn($creditmemoTotal);
        $this->priceCurrency->expects($this->once())
            ->method('round')->with($creditmemoTotal)->willReturn($creditmemoTotal);
        $creditmemo->expects($this->once())->method('getBaseCurrencyCode')->willReturn($creditmemoCurrency);
        $order->expects($this->once())->method('getIncrementId')->willReturn($orderId);
        $order->expects($this->once())->method('getBaseCurrencyCode')->willReturn($baseCurrency);
        $order->expects($this->once())->method('getOrderCurrencyCode')->willReturn($orderCurrency);
        $creditmemoComment = $this->createMock(
            \Magento\Sales\Api\Data\CreditmemoCommentInterface::class
        );
        $creditmemo->expects($this->once())->method('getComments')->willReturn([$creditmemoComment]);
        $creditmemoComment->expects($this->once())->method('getComment')->willReturn($commentText);
        $creditBalanceOptions = $this->prepareCreditBalanceOptionsMock($orderId, $orderCurrency, $baseCurrency);
        $this->creditBalanceManagement->expects($this->once())->method('increase')
            ->with(
                $creditLimitId,
                $creditmemoTotal,
                $creditmemoCurrency,
                \Magento\CompanyCredit\Model\HistoryInterface::TYPE_REFUNDED,
                $commentText,
                $creditBalanceOptions
            );
        $this->creditBalance->refund($order, $creditmemo);
    }

    /**
     * Prepare CreditBalanceOptions model mock.
     *
     * @param int $orderId
     * @param string $orderCurrency
     * @param string $baseCurrency
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function prepareCreditBalanceOptionsMock($orderId, $orderCurrency, $baseCurrency)
    {
        $creditBalanceOptions = $this->getMockBuilder(\Magento\CompanyCredit\Model\CreditBalanceOptions::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->creditBalanceOptionsFactory->expects($this->any())->method('create')
            ->willReturn($creditBalanceOptions);
        $creditBalanceOptions->expects($this->exactly(3))->method('setData')->withConsecutive(
            ['order_increment', $orderId],
            ['currency_display', $orderCurrency],
            ['currency_base', $baseCurrency]
        );

        return $creditBalanceOptions;
    }
}
