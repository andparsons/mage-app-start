<?php
namespace Magento\NegotiableQuote\Test\Unit\Plugin\Quote\Model;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class QuoteRecalculateTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\NegotiableQuote\Plugin\Quote\Model\QuoteRecalculate
     */
    private $model;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $cartRepositoryMock;

    /**
     * @var \Magento\Quote\Model\ResourceModel\Quote\Item|\PHPUnit_Framework_MockObject_MockObject
     */
    private $itemResourceMock;

    /**
     * @var \Magento\NegotiableQuote\Model\Quote\ItemRemove|\PHPUnit_Framework_MockObject_MockObject
     */
    private $itemRemoveMock;

    /**
     * @var \Magento\NegotiableQuote\Api\NegotiableQuoteItemManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $itemManagementMock;

    /**
     * @var \Psr\Log\LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $loggerMock;

    protected function setUp()
    {
        $this->cartRepositoryMock = $this->getMockBuilder(\Magento\Quote\Api\CartRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->itemResourceMock = $this->getMockBuilder(\Magento\Quote\Model\ResourceModel\Quote\Item::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->itemRemoveMock = $this->getMockBuilder(\Magento\NegotiableQuote\Model\Quote\ItemRemove::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->itemManagementMock =
            $this->getMockBuilder(\Magento\NegotiableQuote\Api\NegotiableQuoteItemManagementInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->loggerMock = $this->getMockBuilder(\Psr\Log\LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = new \Magento\NegotiableQuote\Plugin\Quote\Model\QuoteRecalculate(
            $this->cartRepositoryMock,
            $this->itemResourceMock,
            $this->itemRemoveMock,
            $this->itemManagementMock,
            $this->loggerMock
        );
    }

    public function testUpdateQuotesByProductIfQuoteDoesNotExist()
    {
        $tableName = 'table_name';
        $productId = 100;
        $itemsArray = [100 => 'sku'];
        $result = 'result';
        $closure = function () use ($result) {
            return $result;
        };
        $productMock = $this->getMockBuilder(\Magento\Catalog\Api\Data\ProductInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $adapterMock = $this->getMockBuilder(\Magento\Framework\DB\Adapter\AdapterInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $selectMock = $this->getMockBuilder(\Magento\Framework\DB\Select::class)
            ->disableOriginalConstructor()
            ->getMock();

        $productMock->expects($this->once())->method('getId')->willReturn($productId);
        $this->itemResourceMock->expects($this->once())->method('getConnection')->willReturn($adapterMock);
        $this->itemResourceMock->expects($this->once())->method('getMainTable')->willReturn($tableName);

        $adapterMock->expects($this->once())->method('select')->willReturn($selectMock);
        $selectMock->expects($this->once())->method('reset')->willReturnSelf();
        $selectMock->expects($this->once())->method('from')->with($tableName, ['sku', 'quote_id'])->willReturnSelf();
        $selectMock->expects($this->once())->method('where')->with('product_id = ?', $productId)->willReturnSelf();

        $adapterMock->expects($this->once())->method('fetchPairs')->with($selectMock)->willReturn($itemsArray);

        $exception = new \Exception('Quote not found');
        $this->cartRepositoryMock->expects($this->once())
            ->method('get')
            ->with(100, ['*'])
            ->willThrowException($exception);

        $this->loggerMock->expects($this->once())->method('critical')->with($exception);
        $this->model->updateQuotesByProduct($closure, $productMock);
    }

    public function testUpdateQuotesByProductIfQuoteNotNegotiable()
    {
        $tableName = 'table_name';
        $productId = 20;
        $itemsArray = [10 => 'sku'];
        $result = 'result';
        $closure = function () use ($result) {
            return $result;
        };
        $productMock = $this->getMockBuilder(\Magento\Catalog\Api\Data\ProductInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $productMock->expects($this->once())->method('getId')->willReturn($productId);
        $adapterMock = $this->getMockBuilder(\Magento\Framework\DB\Adapter\AdapterInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $selectMock = $this->getMockBuilder(\Magento\Framework\DB\Select::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->itemResourceMock->expects($this->once())->method('getConnection')->willReturn($adapterMock);
        $this->itemResourceMock->expects($this->once())->method('getMainTable')->willReturn($tableName);

        $adapterMock->expects($this->once())->method('select')->willReturn($selectMock);
        $adapterMock->expects($this->once())->method('fetchPairs')->with($selectMock)->willReturn($itemsArray);

        $selectMock->expects($this->once())->method('reset')->willReturnSelf();
        $selectMock->expects($this->once())->method('from')->with($tableName, ['sku', 'quote_id'])->willReturnSelf();
        $selectMock->expects($this->once())->method('where')->with('product_id = ?', $productId)->willReturnSelf();

        $quoteMock = $this->getMockBuilder(\Magento\Quote\Api\Data\CartInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->cartRepositoryMock->expects($this->once())
            ->method('get')
            ->with(10, ['*'])
            ->willReturn($quoteMock);

        $extensionAttrsMock = $this->getMockBuilder(\Magento\Quote\Api\Data\CartExtension::class)
            ->setMethods(['getNegotiableQuote'])
            ->disableOriginalConstructor()
            ->getMock();
        $quoteMock->expects($this->exactly(2))->method('getExtensionAttributes')->willReturn($extensionAttrsMock);
        $extensionAttrsMock->expects($this->once())->method('getNegotiableQuote')->willReturn(null);

        $this->itemRemoveMock->expects($this->never())->method('setNotificationRemove');
        $this->itemManagementMock->expects($this->never())->method('updateQuoteItemsCustomPrices');

        $this->assertEquals($result, $this->model->updateQuotesByProduct($closure, $productMock));
    }

    public function testUpdateQuotesByProduct()
    {
        $quoteId = 20;
        $tableName = 'table_name';
        $productId = 200;
        $itemsArray = [$quoteId => 'sku'];
        $result = 'result';
        $closure = function () use ($result) {
            return $result;
        };
        $productMock = $this->getMockBuilder(\Magento\Catalog\Api\Data\ProductInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $productMock->expects($this->exactly(2))->method('getId')->willReturn($productId);

        $selectMock = $this->getMockBuilder(\Magento\Framework\DB\Select::class)
            ->disableOriginalConstructor()
            ->getMock();
        $selectMock->expects($this->once())->method('reset')->willReturnSelf();
        $selectMock->expects($this->once())->method('from')->with($tableName, ['sku', 'quote_id'])->willReturnSelf();
        $selectMock->expects($this->once())->method('where')->with('product_id = ?', $productId)->willReturnSelf();

        $adapterMock = $this->getMockBuilder(\Magento\Framework\DB\Adapter\AdapterInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $adapterMock->expects($this->once())->method('select')->willReturn($selectMock);
        $adapterMock->expects($this->once())->method('fetchPairs')->with($selectMock)->willReturn($itemsArray);

        $this->itemResourceMock->expects($this->once())->method('getConnection')->willReturn($adapterMock);
        $this->itemResourceMock->expects($this->once())->method('getMainTable')->willReturn($tableName);

        $quoteMock = $this->getMockBuilder(\Magento\Quote\Api\Data\CartInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->cartRepositoryMock->expects($this->once())
            ->method('get')
            ->with($quoteId, ['*'])
            ->willReturn($quoteMock);

        $extensionAttrsMock = $this->getMockBuilder(\Magento\Quote\Api\Data\CartExtension::class)
            ->setMethods(['getNegotiableQuote'])
            ->disableOriginalConstructor()
            ->getMock();
        $quoteMock->expects($this->exactly(3))->method('getExtensionAttributes')->willReturn($extensionAttrsMock);

        $negotiableQuoteMock = $this->getMockBuilder(\Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $extensionAttrsMock->expects($this->exactly(2))->method('getNegotiableQuote')->willReturn($negotiableQuoteMock);
        $negotiableQuoteMock->expects($this->once())->method('getIsRegularQuote')->willReturn(true);

        $this->itemRemoveMock->expects($this->once())
            ->method('setNotificationRemove')
            ->with($quoteId, $productId, [$itemsArray[$quoteId]]);
        $this->itemManagementMock->expects($this->once())
            ->method('updateQuoteItemsCustomPrices')
            ->with($quoteId);

        $this->assertEquals($result, $this->model->updateQuotesByProduct($closure, $productMock));
    }
}
