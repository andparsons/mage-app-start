<?php

namespace Magento\NegotiableQuote\Test\Unit\Model\Quote;

use Magento\Framework\DataObject;

/**
 * Test for Magento\NegotiableQuote\Model\Quote\History class.
 */
class HistoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\NegotiableQuote\Model\PriceChecker|\PHPUnit_Framework_MockObject_MockObject
     */
    private $priceCheckerMock;

    /**
     * @var \Magento\NegotiableQuote\Model\RuleChecker|\PHPUnit_Framework_MockObject_MockObject
     */
    private $ruleCheckerMock;

    /**
     * @var \Magento\NegotiableQuote\Model\HistoryManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $historyManagementMock;

    /**
     * @var \Magento\Quote\Api\Data\CartInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteMock;

    /**
     * @var \Magento\NegotiableQuote\Model\Quote\History
     */
    private $model;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->priceCheckerMock = $this->getMockBuilder(\Magento\NegotiableQuote\Model\PriceChecker::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->ruleCheckerMock = $this->getMockBuilder(\Magento\NegotiableQuote\Model\RuleChecker::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->historyManagementMock = $this->getMockBuilder(
            \Magento\NegotiableQuote\Model\HistoryManagementInterface::class
        )
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->quoteMock = $this->getMockBuilder(\Magento\Quote\Api\Data\CartInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $objectManager->getObject(
            \Magento\NegotiableQuote\Model\Quote\History::class,
            [
                'priceChecker' => $this->priceCheckerMock,
                'ruleChecker' => $this->ruleCheckerMock,
                'historyManagement' => $this->historyManagementMock
            ]
        );
    }

    /**
     * Test collectOldDataFromQuote method.
     *
     * @dataProvider collectOldDataFromQuoteDataProvider
     * @param array $appliedRuleIds
     * @param array $itemsPriceData
     * @param array $cartPriceData
     * @param int|float $totalDiscount
     * @return void
     */
    public function testCollectOldDataFromQuote(
        array $appliedRuleIds,
        array $itemsPriceData,
        array $cartPriceData,
        $totalDiscount
    ) {
        $data = new DataObject();
        $extensionAttributes = $this->getMockBuilder(\Magento\Quote\Api\Data\CartExtensionInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getNegotiableQuote'])
            ->getMockForAbstractClass();
        $quoteNegotiation = $this->getMockBuilder(\Magento\NegotiableQuote\Model\NegotiableQuote::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->quoteMock->expects($this->atLeastOnce())
            ->method('getExtensionAttributes')
            ->willReturn($extensionAttributes);
        $extensionAttributes->expects($this->atLeastOnce())
            ->method('getNegotiableQuote')
            ->willReturn($quoteNegotiation);
        $quoteNegotiation->expects($this->atLeastOnce())->method('getAppliedRuleIds')->willReturn($appliedRuleIds);
        $this->priceCheckerMock->expects($this->once())
            ->method('collectItemsPriceData')
            ->with($this->quoteMock)
            ->willReturn($itemsPriceData);
        $this->priceCheckerMock->expects($this->once())
            ->method('collectItemsCartPriceData')
            ->with($this->quoteMock)
            ->willReturn($cartPriceData);
        $this->priceCheckerMock->expects($this->once())
            ->method('getTotalDiscount')
            ->with($this->quoteMock)
            ->willReturn($totalDiscount);
        $data->setData('old_rule_ids', $appliedRuleIds);
        $data->setData('old_price_data', $itemsPriceData);
        $data->setData('old_cart_price_data', $cartPriceData);
        $data->setData('old_discount_amount', $totalDiscount);

        $this->assertEquals($data, $this->model->collectOldDataFromQuote($this->quoteMock));
    }

    /**
     * Data provider for collectOldDataFromQuote method.
     *
     * @return array
     */
    public function collectOldDataFromQuoteDataProvider()
    {
        return [
            [
                'specifiedRuleIds' => [1, 5, 8],
                'itemsPriceData' => [
                    'test_product' => 50,
                    'test_product_1' => 75
                ],
                'cartPriceData' => [
                    'test_product' => 60,
                    'test_product_1' => 90
                ],
                'totalDiscount' => 45
            ],
        ];
    }

    /**
     * Test collectTaxDataFromQuote method.
     *
     * @return void
     */
    public function testCollectTaxDataFromQuote()
    {
        $data = new DataObject();
        $this->priceCheckerMock->expects($this->once())
            ->method('getSubtotalOriginalTax')
            ->with($this->quoteMock)
            ->willReturn(25);
        $this->priceCheckerMock->expects($this->once())
            ->method('getShippingTax')
            ->with($this->quoteMock)
            ->willReturn(10);
        $data->setData('subtotal_tax', 25);
        $data->setData('shipping_tax', 10);

        $this->assertEquals($data, $this->model->collectTaxDataFromQuote($this->quoteMock));
    }

    /**
     * Test checkPricesAndDiscounts method.
     *
     * @dataProvider collectOldDataFromQuoteDataProvider
     * @param array $appliedRuleIds
     * @param array $itemsPriceData
     * @param array $cartPriceData
     * @param int|float $totalDiscount
     * @return void
     */
    public function testCheckPricesAndDiscounts(
        array $appliedRuleIds,
        array $itemsPriceData,
        array $cartPriceData,
        $totalDiscount
    ) {
        $resultData = new DataObject();
        $oldData = new DataObject();

        $this->ruleCheckerMock->expects($this->once())->method('checkIsDiscountRemoved')->willReturn(true);
        $this->priceCheckerMock->expects($this->once())->method('setIsProductPriceChanged')->willReturn(true);
        $this->priceCheckerMock->expects($this->once())->method('setIsCartPriceChanged')->willReturn(true);
        $this->priceCheckerMock->expects($this->once())->method('setIsDiscountChanged')->willReturn(true);

        $resultData->setData('is_tax_changed', true);
        $resultData->setData('is_price_changed', true);
        $resultData->setData('is_discount_changed', true);
        $resultData->setData('is_changed', true);
        $oldData->setData('old_rule_ids', $appliedRuleIds);
        $oldData->setData('old_price_data', $itemsPriceData);
        $oldData->setData('old_cart_price_data', $cartPriceData);
        $oldData->setData('old_discount_amount', $totalDiscount);

        $this->assertEquals($resultData, $this->model->checkPricesAndDiscounts($this->quoteMock, $oldData));
    }

    /**
     * Test checkTaxes method.
     *
     * @return void
     */
    public function testCheckTaxes()
    {
        $resultData = new DataObject();
        $taxData = new DataObject();

        $this->priceCheckerMock->expects($this->once())->method('setIsSubtotalOriginalTaxChanged')->willReturn(true);
        $this->priceCheckerMock->expects($this->once())->method('setIsShippingTaxChanged')->willReturn(true);

        $resultData->setData('is_tax_changed', true);
        $resultData->setData('is_shipping_tax_changed', true);
        $taxData->setData('subtotal_tax', 10);
        $taxData->setData('shipping_tax', 5);

        $this->assertEquals($resultData, $this->model->checkTaxes($this->quoteMock, $taxData));
    }

    /**
     * Test removeFrontMessage method.
     *
     * @return void
     */
    public function testRemoveFrontMessage()
    {
        $negotiableQuote = $this->getMockBuilder(\Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $negotiableQuote->expects($this->once())->method('getNotifications')->willReturn(5);
        $negotiableQuote->expects($this->once())->method('setNotifications');

        $this->model->removeFrontMessage($negotiableQuote);
    }

    /**
     * Test removeAdminMessage method.
     *
     * @return void
     */
    public function testRemoveAdminMessage()
    {
        $negotiableQuote = $this->getMockBuilder(\Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $negotiableQuote->expects($this->once())->method('getNotifications')->willReturn(2);
        $negotiableQuote->expects($this->once())->method('setNotifications');

        $this->model->removeAdminMessage($negotiableQuote);
    }

    /**
     * Test closeLog method.
     *
     * @return void
     */
    public function testCloseLog()
    {
        $quoteId = 1;
        $this->historyManagementMock->expects($this->once())->method('closeLog')->with($quoteId);
        $this->historyManagementMock->expects($this->once())->method('updateDraftLogs')->with($quoteId)->willReturn([]);

        $this->model->closeLog($quoteId);
    }

    /**
     * Test updateStatusLog method.
     *
     * @return void
     */
    public function testUpdateStatusLog()
    {
        $quoteId = 1;
        $this->historyManagementMock->expects($this->once())->method('updateStatusLog')->with($quoteId);
        $this->historyManagementMock->expects($this->once())->method('updateDraftLogs')->with($quoteId)->willReturn([]);

        $this->model->updateStatusLog($quoteId);
    }

    /**
     * Test updateLog method.
     *
     * @return void
     */
    public function testUpdateLog()
    {
        $quoteId = 1;
        $this->historyManagementMock->expects($this->once())->method('updateLog')->with($quoteId);
        $this->historyManagementMock->expects($this->once())->method('updateDraftLogs')->with($quoteId)->willReturn([]);

        $this->model->updateLog($quoteId);
    }

    /**
     * Test createLog method.
     *
     * @return void
     */
    public function testCreateLog()
    {
        $quoteId = 1;
        $this->historyManagementMock->expects($this->once())->method('createLog')->with($quoteId);

        $this->model->createLog($quoteId);
    }
}
