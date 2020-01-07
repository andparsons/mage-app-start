<?php

namespace Magento\NegotiableQuote\Test\Unit\Model\ResourceModel;

/**
 * Class NegotiableQuoteTest
 */
class NegotiableQuoteTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Model\ResourceModel\Db\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $context;

    /**
     * @var \Magento\NegotiableQuote\Model\ResourceModel\NegotiableQuote|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $negotiableQuoteMock;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $connection;

    /**
     * Set up
     */
    protected function setUp()
    {
        $this->context = $this->createMock(\Magento\Framework\Model\ResourceModel\Db\Context::class);
        $resource = $this->createMock(\Magento\Framework\App\ResourceConnection::class);
        $this->connection = $this->createMock(\Magento\Framework\DB\Adapter\AdapterInterface::class);
        $resource->expects($this->any())->method('getConnection')->willReturn($this->connection);
        $this->context->expects($this->any())->method('getResources')->willReturn($resource);
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->negotiableQuoteMock = $objectManager->getObject(
            \Magento\NegotiableQuote\Model\ResourceModel\NegotiableQuote::class,
            [
                'context' => $this->context,
            ]
        );
    }

    /**
     * Test saveNegotiatedQuoteData()
     */
    public function testSaveNegotiatedQuoteData()
    {
        $quote = $this->createMock(\Magento\NegotiableQuote\Model\NegotiableQuote::class);
        $quote->expects($this->any())->method('getData')->willReturn([
            'quote_name' => 'new quote',
            'status' => 'submitted',
        ]);

        $this->assertInstanceOf(
            \Magento\NegotiableQuote\Model\ResourceModel\NegotiableQuote::class,
            $this->negotiableQuoteMock->saveNegotiatedQuoteData($quote)
        );
    }

    /**
     * Test saveNegotiatedQuoteData() with exception
     */
    public function testSaveNegotiatedQuoteDataException()
    {
        $this->connection->expects($this->any())
            ->method('insertOnDuplicate')
            ->with(null, ['bad array'], [0])
            ->willThrowException(new \Exception(''));
        $quote = $this->createMock(\Magento\NegotiableQuote\Model\NegotiableQuote::class);
        $quote->expects($this->any())->method('getData')->willReturn(['bad array']);

        $this->expectException(\Magento\Framework\Exception\CouldNotSaveException::class);
        $this->negotiableQuoteMock->saveNegotiatedQuoteData($quote);
    }
}
