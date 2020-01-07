<?php

namespace Magento\SharedCatalog\Test\Unit\Model\ResourceModel\ProductItem\Price;

use Magento\SharedCatalog\Model\ResourceModel\ProductItem\Price\PriceProcessor;

/**
 * Test for Magento/SharedCatalog/Model/ResourceModel/ProductItem/Price/Consumer class.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ConsumerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Psr\Log\LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $loggerMock;

    /**
     * @var \Magento\Framework\EntityManager\EntityManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $entityManagerMock;

    /**
     * @var \Magento\Framework\Serialize\SerializerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $serializerMock;

    /**
     * @var \Magento\Catalog\Api\TierPriceStorageInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $tierPriceStorage;

    /**
     * @var \Magento\AsynchronousOperations\Api\Data\OperationInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $operation;

    /**
     * @var \Magento\Catalog\Api\Data\TierPriceInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $tierPrice;

    /**
     * @var PriceProcessor|\PHPUnit_Framework_MockObject_MockObject
     */
    private $priceProcessor;

    /**
     * @var \Magento\SharedCatalog\Model\ResourceModel\ProductItem\Price\Consumer
     */
    private $consumer;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->loggerMock = $this->getMockBuilder(\Psr\Log\LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->entityManagerMock = $this->getMockBuilder(\Magento\Framework\EntityManager\EntityManager::class)
            ->disableOriginalConstructor()
            ->setMethods(['save'])
            ->getMock();
        $this->serializerMock = $this->getMockBuilder(\Magento\Framework\Serialize\SerializerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->tierPriceStorage = $this->getMockBuilder(\Magento\Catalog\Api\TierPriceStorageInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->operation = $this->getMockBuilder(\Magento\AsynchronousOperations\Api\Data\OperationInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->tierPrice = $this->getMockBuilder(\Magento\Catalog\Api\Data\TierPriceInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->priceProcessor = $this
            ->getMockBuilder(\Magento\SharedCatalog\Model\ResourceModel\ProductItem\Price\PriceProcessor::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->consumer = $objectManagerHelper->getObject(
            \Magento\SharedCatalog\Model\ResourceModel\ProductItem\Price\Consumer::class,
            [
                'logger' => $this->loggerMock,
                'entityManager' => $this->entityManagerMock,
                'serializer' => $this->serializerMock,
                'tierPriceStorage' => $this->tierPriceStorage,
                'priceProcessor' => $this->priceProcessor
            ]
        );
    }

    /**
     * Test for processOperation().
     *
     * @param array $unserializedData
     * @return void
     * @dataProvider processOperationsDataProvider
     */
    public function testProcessOperations(array $unserializedData)
    {
        $serializedData = json_encode($unserializedData);
        $this->operation->expects($this->atLeastOnce())->method('getSerializedData')->willReturn($serializedData);
        $this->serializerMock->expects($this->atLeastOnce())->method('unserialize')->willReturn($unserializedData);
        $priceUpdateResult = $this->getMockBuilder(\Magento\Catalog\Api\Data\PriceUpdateResultInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->tierPriceStorage->expects($this->atLeastOnce())
            ->method('delete')
            ->with([])
            ->willReturn([$priceUpdateResult]);
        $this->tierPriceStorage->expects($this->atLeastOnce())
            ->method('update')
            ->willReturn([$priceUpdateResult]);
        $this->priceProcessor->expects($this->atLeastOnce())->method('createPricesUpdate')->willReturn([]);
        $this->priceProcessor->expects($this->atLeastOnce())->method('createPricesDelete')->willReturn([]);
        $this->operation->expects($this->atLeastOnce())
            ->method('setStatus')
            ->willReturnSelf();
        $this->operation->expects($this->atLeastOnce())
            ->method('setResultMessage')
            ->willReturnSelf();
        $this->operation->expects($this->atLeastOnce())->method('setResultMessage')->willReturnSelf();
        $operationList = $this->getMockBuilder(\Magento\AsynchronousOperations\Api\Data\OperationListInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getItems'])
            ->getMockForAbstractClass();
        $operationList->expects($this->atLeastOnce())->method('getItems')->willReturn([$this->operation]);
        $this->entityManagerMock->expects($this->atLeastOnce())->method('save')->with($operationList);

        $this->consumer->processOperations($operationList);
    }

    /**
     * Test for processOperations() with Exception during changing operation status.
     *
     * @param array $unserializedData
     * @return void
     * @dataProvider processOperationsDataProvider
     */
    public function testProcessOperationWhenExceptionOccurs(array $unserializedData)
    {
        $exceptionMessage = 'Exception message.';
        $exception = new \Exception(__($exceptionMessage));
        $serializedData = json_encode($unserializedData);
        $this->operation->expects($this->atLeastOnce())->method('getSerializedData')->willReturn($serializedData);
        $this->serializerMock->expects($this->atLeastOnce())->method('unserialize')->willReturn($unserializedData);
        $priceUpdateResult = $this->getMockBuilder(\Magento\Catalog\Api\Data\PriceUpdateResultInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->tierPriceStorage->expects($this->atLeastOnce())
            ->method('delete')
            ->with([])
            ->willReturn([$priceUpdateResult]);
        $this->tierPriceStorage->expects($this->atLeastOnce())
            ->method('update')
            ->willReturn([$priceUpdateResult]);
        $this->priceProcessor->expects($this->atLeastOnce())->method('createPricesUpdate')->willReturn([]);
        $this->priceProcessor->expects($this->atLeastOnce())->method('createPricesDelete')->willReturn([]);
        $this->operation->expects($this->atLeastOnce())
            ->method('setStatus')
            ->willReturnSelf();
        $this->operation->expects($this->atLeastOnce())
            ->method('setResultMessage')
            ->willReturnSelf();
        $this->operation->expects($this->atLeastOnce())->method('setResultMessage')->willReturnSelf();
        $operationList = $this->getMockBuilder(\Magento\AsynchronousOperations\Api\Data\OperationListInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getItems'])
            ->getMockForAbstractClass();
        $operationList->expects($this->atLeastOnce())->method('getItems')->willReturn([$this->operation]);
        $this->entityManagerMock->expects($this->once())
            ->method('save')->with($operationList)->willThrowException($exception);

        $this->consumer->processOperations($operationList);
    }

    /**
     * Test for processOperations() with CouldNotSaveException.
     *
     * @return void
     */
    public function testProcessOperationsWithCouldNotSaveException()
    {
        $exceptionMessage = 'Exception message.';
        $exception = new \Magento\Framework\Exception\CouldNotSaveException(__($exceptionMessage));
        $priceUpdateResult = $this->getMockBuilder(\Magento\Catalog\Api\Data\PriceUpdateResultInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->tierPriceStorage->expects($this->atLeastOnce())
            ->method('delete')
            ->with([])
            ->willReturn([$priceUpdateResult]);
        $this->tierPriceStorage->expects($this->atLeastOnce())
            ->method('update')
            ->willThrowException($exception);
        $operationList = $this->getMockBuilder(\Magento\AsynchronousOperations\Api\Data\OperationListInterface::class)
            ->disableOriginalConstructor()->getMock();
        $operationList->expects($this->atLeastOnce())->method('getItems')->willReturn([]);
        $this->entityManagerMock->expects($this->atLeastOnce())->method('save')->with($operationList);

        $this->consumer->processOperations($operationList);
    }

    /**
     * Test for processOperations() with CouldNotDeleteException.
     *
     * @return void
     */
    public function testProcessOperationsWithCouldNotDeleteException()
    {
        $exceptionMessage = 'Exception message.';
        $exception = new \Magento\Framework\Exception\CouldNotDeleteException(__($exceptionMessage));
        $this->tierPriceStorage->expects($this->atLeastOnce())
            ->method('delete')
            ->with([])
            ->willThrowException($exception);
        $this->tierPriceStorage->expects($this->never())->method('update');
        $operationList = $this->getMockBuilder(\Magento\AsynchronousOperations\Api\Data\OperationListInterface::class)
            ->disableOriginalConstructor()->getMock();
        $operationList->expects($this->atLeastOnce())->method('getItems')->willReturn([]);
        $this->entityManagerMock->expects($this->atLeastOnce())->method('save')->with($operationList);

        $this->consumer->processOperations($operationList);
    }

    /**
     * Test for processOperations() with Exception.
     *
     * @return void
     */
    public function testProcessOperationsWithException()
    {
        $exceptionMessage = 'Exception message.';
        $exception = new \Exception(__($exceptionMessage));
        $priceUpdateResult = $this->getMockBuilder(\Magento\Catalog\Api\Data\PriceUpdateResultInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->tierPriceStorage->expects($this->atLeastOnce())
            ->method('delete')
            ->with([])
            ->willThrowException($exception);
        $this->tierPriceStorage->expects($this->never())
            ->method('update')
            ->with([$this->tierPrice])
            ->willReturn([$priceUpdateResult]);
        $operationList = $this->getMockBuilder(\Magento\AsynchronousOperations\Api\Data\OperationListInterface::class)
            ->disableOriginalConstructor()->getMock();
        $operationList->expects($this->atLeastOnce())->method('getItems')->willReturn([]);
        $this->entityManagerMock->expects($this->atLeastOnce())->method('save')->with($operationList);

        $this->consumer->processOperations($operationList);
    }

    /**
     * Data provider for processOperations method.
     *
     * @return array
     */
    public function processOperationsDataProvider()
    {
        return [
            [
                [
                    'shared_catalog_id' => 1,
                    'entity_id' => 2,
                    'prices' => [
                        [
                            'qty' => 1,
                            'value_type' => 'percent',
                            'percentage_value' => 50,
                            'website_id' => 1,
                        ]
                    ],
                    'entity_link' => 'http://example.com',
                    'product_sku' => 'test_sku',
                    'customer_group' => 3,
                ]
            ],
            [
                [
                    'shared_catalog_id' => 1,
                    'entity_id' => 2,
                    'prices' => [
                        [
                            'qty' => 1,
                            'value_type' => 'fixed',
                            'price' => 20,
                            'website_id' => 1,
                        ]
                    ],
                    'entity_link' => 'http://example.com',
                    'product_sku' => 'test_sku',
                    'customer_group' => 3,
                ]
            ],
        ];
    }
}
