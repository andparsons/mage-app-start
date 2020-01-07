<?php

namespace Magento\NegotiableQuote\Test\Unit\Plugin\Quote\Model;

use Magento\NegotiableQuote\Plugin\Quote\Model\QuoteRecalculate;
use Magento\NegotiableQuote\Plugin\Quote\Model\QuoteItemConsumerPlugin;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\ScalableCheckout\Model\ResourceModel\Quote\Item\Consumer;

class QuoteItemConsumerPluginTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var QuoteItemConsumerPlugin
     */
    private $model;

    /**
     * @var QuoteRecalculate|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteRecalculateMock;

    protected function setUp()
    {
        $this->quoteRecalculateMock = $this->getMockBuilder(QuoteRecalculate::class)
            ->disableOriginalConstructor()
            ->setMethods(['updateQuotesByProduct'])
            ->getMock();
        $this->model = new QuoteItemConsumerPlugin($this->quoteRecalculateMock);
    }

    public function testAroundExecute()
    {
        $subjectMock = $this->getMockBuilder(Consumer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $closure = function () {
        };
        $productMock = $this->getMockBuilder(ProductInterface::class)->disableOriginalConstructor()->getMock();

        $this->quoteRecalculateMock->expects($this->once())
            ->method('updateQuotesByProduct')
            ->with($closure, $productMock);
        $this->model->aroundProcessMessage($subjectMock, $closure, $productMock);
    }
}
