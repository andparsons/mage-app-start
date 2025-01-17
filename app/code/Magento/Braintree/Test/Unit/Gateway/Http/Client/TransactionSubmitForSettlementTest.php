<?php
namespace Magento\Braintree\Test\Unit\Gateway\Http\Client;

use Braintree\Result\Successful;
use Magento\Braintree\Gateway\Http\Client\TransactionSubmitForSettlement;
use Magento\Braintree\Model\Adapter\BraintreeAdapter;
use Magento\Braintree\Model\Adapter\BraintreeAdapterFactory;
use Magento\Payment\Gateway\Http\TransferInterface;
use Magento\Payment\Model\Method\Logger;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Psr\Log\LoggerInterface;

/**
 * Tests \Magento\Braintree\Gateway\Http\Client\TransactionSubmitForSettlement.
 */
class TransactionSubmitForSettlementTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var TransactionSubmitForSettlement
     */
    private $client;

    /**
     * @var Logger|MockObject
     */
    private $loggerMock;

    /**
     * @var BraintreeAdapter|MockObject
     */
    private $adapterMock;

    protected function setUp()
    {
        /** @var LoggerInterface|MockObject $criticalLoggerMock */
        $criticalLoggerMock = $this->getMockForAbstractClass(LoggerInterface::class);
        $this->loggerMock = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->setMethods(['debug'])
            ->getMock();

        $this->adapterMock = $this->getMockBuilder(BraintreeAdapter::class)
            ->disableOriginalConstructor()
            ->setMethods(['submitForSettlement'])
            ->getMock();
        /** @var BraintreeAdapterFactory|MockObject $adapterFactoryMock */
        $adapterFactoryMock = $this->getMockBuilder(BraintreeAdapterFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $adapterFactoryMock->method('create')
            ->willReturn($this->adapterMock);

        $this->client = new TransactionSubmitForSettlement(
            $criticalLoggerMock,
            $this->loggerMock,
            $adapterFactoryMock
        );
    }

    /**
     * @covers \Magento\Braintree\Gateway\Http\Client\TransactionSubmitForSettlement::placeRequest
     * @expectedException \Magento\Payment\Gateway\Http\ClientException
     * @expectedExceptionMessage Transaction has been declined
     */
    public function testPlaceRequestWithException()
    {
        $exception = new \Exception('Transaction has been declined');
        $this->adapterMock->expects(static::once())
            ->method('submitForSettlement')
            ->willThrowException($exception);

        /** @var TransferInterface|MockObject $transferObject */
        $transferObject = $this->getTransferObjectMock();
        $this->client->placeRequest($transferObject);
    }

    /**
     * @covers \Magento\Braintree\Gateway\Http\Client\TransactionSubmitForSettlement::process
     */
    public function testPlaceRequest()
    {
        $data = new Successful(['success'], [true]);
        $this->adapterMock->expects(static::once())
            ->method('submitForSettlement')
            ->willReturn($data);

        /** @var TransferInterface|MockObject $transferObject */
        $transferObject = $this->getTransferObjectMock();
        $response = $this->client->placeRequest($transferObject);
        static::assertTrue(is_object($response['object']));
        static::assertEquals(['object' => $data], $response);
    }

    /**
     * Creates mock for TransferInterface
     *
     * @return TransferInterface|MockObject
     */
    private function getTransferObjectMock()
    {
        $mock = $this->createMock(TransferInterface::class);
        $mock->expects($this->once())
            ->method('getBody')
            ->willReturn([
                'transaction_id' => 'vb4c6b',
                'amount' => 124.00,
            ]);

        return $mock;
    }
}
