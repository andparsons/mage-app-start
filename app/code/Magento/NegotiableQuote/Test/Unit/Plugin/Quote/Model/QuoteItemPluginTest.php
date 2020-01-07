<?php

namespace Magento\NegotiableQuote\Test\Unit\Plugin\Quote\Model;

use Magento\NegotiableQuote\Plugin\Quote\Model\QuoteRecalculate;
use Magento\NegotiableQuote\Plugin\Quote\Model\QuoteItemPlugin;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Quote\Model\Product\QuoteItemsCleanerInterface;

/**
 * Class ProductPluginTest.
 */
class QuoteItemPluginTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var QuoteItemPlugin
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
        $this->model = new QuoteItemPlugin($this->quoteRecalculateMock);
    }

    public function testAroundExecute()
    {
        $subjectMock = $this->getMockBuilder(QuoteItemsCleanerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $closure = function () {
        };
        $productMock = $this->getMockBuilder(ProductInterface::class)->disableOriginalConstructor()->getMock();

        $this->quoteRecalculateMock->expects($this->once())
            ->method('updateQuotesByProduct')
            ->with($closure, $productMock);
        $this->model->aroundExecute($subjectMock, $closure, $productMock);
    }
}
