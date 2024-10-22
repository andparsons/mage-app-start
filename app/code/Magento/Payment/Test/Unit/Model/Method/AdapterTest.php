<?php
namespace Magento\Payment\Test\Unit\Model\Method;

use Magento\Framework\Event\ManagerInterface;
use Magento\Payment\Gateway\Command\CommandManagerInterface;
use Magento\Payment\Gateway\Command\CommandPoolInterface;
use Magento\Payment\Gateway\CommandInterface;
use Magento\Payment\Gateway\Config\ValueHandlerInterface;
use Magento\Payment\Gateway\Config\ValueHandlerPoolInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectFactory;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Validator\ResultInterface;
use Magento\Payment\Gateway\Validator\ValidatorInterface;
use Magento\Payment\Gateway\Validator\ValidatorPoolInterface;
use Magento\Payment\Model\InfoInterface;
use Magento\Payment\Model\Method\Adapter;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AdapterTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var MockObject|ManagerInterface
     */
    private $eventManager;

    /**
     * @var MockObject|ValueHandlerPoolInterface
     */
    private $valueHandlerPool;

    /**
     * @var MockObject|ValidatorPoolInterface
     */
    private $validatorPool;

    /**
     * @var MockObject|CommandPoolInterface
     */
    private $commandPool;

    /**
     * @var MockObject|PaymentDataObjectFactory
     */
    private $paymentDataObjectFactory;

    /**
     * @var MockObject
     */
    private $logger;

    /**
     * @var string
     */
    private $code;

    /**
     * @var string
     */
    private $formBlockType;

    /**
     * @var string
     */
    private $infoBlockType;

    /**
     * @var Adapter
     */
    private $adapter;

    protected function setUp()
    {
        $this->eventManager = $this->createMock(ManagerInterface::class);
        $this->valueHandlerPool = $this->createMock(ValueHandlerPoolInterface::class);
        $this->validatorPool = $this->createMock(ValidatorPoolInterface::class);
        $this->commandPool = $this->createMock(CommandPoolInterface::class);
        $this->paymentDataObjectFactory = $this->getMockBuilder(PaymentDataObjectFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->code = 'CODE';
        $this->formBlockType = '\FormBlock';
        $this->infoBlockType = '\InfoBlock';

        $this->logger = $this->getMockBuilder(\Psr\Log\LoggerInterface::class)
            ->getMock();
        $this->adapter = new Adapter(
            $this->eventManager,
            $this->valueHandlerPool,
            $this->paymentDataObjectFactory,
            $this->code,
            $this->formBlockType,
            $this->infoBlockType,
            $this->commandPool,
            $this->validatorPool,
            null,
            $this->logger
        );
    }

    public function testFetchTransactionInfo()
    {
        $transactionId = 10555;
        $transactionInfo = ['test_key' => 'test_value'];

        $valueHandler = $this->getMockForAbstractClass(ValueHandlerInterface::class);
        $command = $this->getMockForAbstractClass(CommandInterface::class);

        /** @var  InfoInterface|MockObject $paymentInfo */
        $paymentInfo = $this->getMockForAbstractClass(InfoInterface::class);
        $paymentDO = $this->getMockForAbstractClass(PaymentDataObjectInterface::class);

        $this->valueHandlerPool->method('get')
            ->with('can_fetch_transaction_information')
            ->willReturn($valueHandler);
        $valueHandler->expects($this->atLeastOnce())
            ->method('handle')
            ->with(['field' => 'can_fetch_transaction_information'])
            ->willReturn(true);

        $this->paymentDataObjectFactory->method('create')
            ->with($paymentInfo)
            ->willReturn($paymentDO);

        $this->commandPool->method('get')
            ->with('fetch_transaction_information')
            ->willReturn($command);
        $command->expects($this->atLeastOnce())
            ->method('execute')
            ->with(['transactionId' => $transactionId, 'payment' => $paymentDO])
            ->willReturn($transactionInfo);

        $this->assertEquals(
            $transactionInfo,
            $this->adapter->fetchTransactionInfo($paymentInfo, $transactionId)
        );
    }

    /**
     * @covers \Magento\Payment\Model\Method\Adapter::isAvailable
     */
    public function testIsAvailableNotActive()
    {
        $activeValueHandler = $this->createMock(ValueHandlerInterface::class);

        $this->valueHandlerPool->expects(static::once())
            ->method('get')
            ->with('active')
            ->willReturn($activeValueHandler);
        $activeValueHandler->expects(static::once())
            ->method('handle')
            ->with(['field' => 'active'])
            ->willReturn(false);

        $this->eventManager->expects(static::never())
            ->method('dispatch');

        static::assertFalse($this->adapter->isAvailable(null));
    }

    /**
     * @covers \Magento\Payment\Model\Method\Adapter::isAvailable
     */
    public function testIsAvailableEmptyQuote()
    {
        $activeValueHandler = $this->createMock(ValueHandlerInterface::class);
        $availabilityValidator = $this->createMock(ValidatorInterface::class);
        $paymentDO = $this->createMock(PaymentDataObjectInterface::class);
        $validationResult = $this->createMock(ResultInterface::class);
        $paymentInfo = $this->createMock(InfoInterface::class);

        $this->valueHandlerPool->expects(static::once())
            ->method('get')
            ->with('active')
            ->willReturn($activeValueHandler);
        $activeValueHandler->expects(static::once())
            ->method('handle')
            ->with(['field' => 'active', 'payment' => $paymentDO])
            ->willReturn(true);

        $this->validatorPool->expects(static::once())
            ->method('get')
            ->with('availability')
            ->willReturn($availabilityValidator);
        $this->paymentDataObjectFactory->expects(static::exactly(2))
            ->method('create')
            ->with($paymentInfo)
            ->willReturn($paymentDO);
        $availabilityValidator->expects(static::once())
            ->method('validate')
            ->willReturn($validationResult);
        $validationResult->expects(static::once())
            ->method('isValid')
            ->willReturn(true);

        $this->eventManager->expects(static::once())
            ->method('dispatch');

        $this->adapter->setInfoInstance($paymentInfo);
        static::assertTrue($this->adapter->isAvailable(null));
    }

    /**
     * @covers \Magento\Payment\Model\Method\Adapter::isAvailable
     */
    public function testIsAvailableWithEmptyInfoInstance()
    {
        $activeValueHandler = $this->createMock(ValueHandlerInterface::class);
        $this->valueHandlerPool->expects(static::once())
            ->method('get')
            ->with('active')
            ->willReturn($activeValueHandler);
        $activeValueHandler->expects(static::once())
            ->method('handle')
            ->with(['field' => 'active'])
            ->willReturn(true);

        $this->validatorPool->expects(static::never())
            ->method('get')
            ->with('availability');

        $this->eventManager->expects(static::once())
            ->method('dispatch');

        static::assertTrue($this->adapter->isAvailable(null));
    }

    public function testExecuteCommandWithCommandExecutor()
    {
        /** @var ManagerInterface|MockObject $eventManager */
        $eventManager = $this->createMock(
            ManagerInterface::class
        );

        /** @var ValueHandlerPoolInterface|MockObject $valueHandlerPool */
        $valueHandlerPool = $this->createMock(
            ValueHandlerPoolInterface::class
        );

        /** @var CommandManagerInterface|MockObject $commandManager */
        $commandManager = $this->createMock(
            CommandManagerInterface::class
        );

        /** @var PaymentDataObjectFactory|MockObject $paymentDataObjectFactory */
        $paymentDataObjectFactory = $this->getMockBuilder(
            PaymentDataObjectFactory::class
        )
            ->disableOriginalConstructor()
            ->getMock();

        $paymentInfo = $this->createMock(InfoInterface::class);
        $paymentDO = $this->createMock(PaymentDataObjectInterface::class);

        $adapter = new Adapter(
            $eventManager,
            $valueHandlerPool,
            $paymentDataObjectFactory,
            'CODE',
            '\FormBlock',
            '\InfoBlock',
            null,
            null,
            $commandManager,
            $this->logger
        );

        $valueHandler = $this->createMock(ValueHandlerInterface::class);

        $valueHandlerPool->expects(static::once())
            ->method('get')
            ->with('can_authorize')
            ->willReturn($valueHandler);
        $valueHandler->expects(static::once())
            ->method('handle')
            ->with(['field' => 'can_authorize'])
            ->willReturn(true);

        $paymentDataObjectFactory->expects(static::once())
            ->method('create')
            ->with($paymentInfo)
            ->willReturn($paymentDO);

        $commandManager->expects(static::once())
            ->method('executeByCode')
            ->with('authorize', $paymentInfo, ['amount' => 10, 'payment' => $paymentDO])
            ->willReturn(null);

        $adapter->authorize($paymentInfo, 10);
    }

    public function testExecuteCommandWithCommandPool()
    {
        /** @var ManagerInterface|MockObject $eventManager */
        $eventManager = $this->createMock(ManagerInterface::class);

        /** @var ValueHandlerPoolInterface|MockObject $valueHandlerPool */
        $valueHandlerPool = $this->createMock(ValueHandlerPoolInterface::class);

        /** @var CommandPoolInterface|MockObject $commandPool */
        $commandPool = $this->createMock(CommandPoolInterface::class);

        /** @var PaymentDataObjectFactory|MockObject $paymentDataObjectFactory */
        $paymentDataObjectFactory = $this->getMockBuilder(PaymentDataObjectFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $paymentInfo = $this->createMock(InfoInterface::class);
        $paymentDO = $this->createMock(PaymentDataObjectInterface::class);

        $adapter = new Adapter(
            $eventManager,
            $valueHandlerPool,
            $paymentDataObjectFactory,
            'CODE',
            '\FormBlock',
            '\InfoBlock',
            $commandPool,
            null,
            null,
            $this->logger
        );

        $valueHandler = $this->createMock(ValueHandlerInterface::class);
        $command = $this->createMock(CommandInterface::class);

        $valueHandlerPool->expects(static::once())
            ->method('get')
            ->with('can_authorize')
            ->willReturn($valueHandler);
        $valueHandler->expects(static::once())
            ->method('handle')
            ->with(['field' => 'can_authorize'])
            ->willReturn(true);

        $paymentDataObjectFactory->expects(static::once())
            ->method('create')
            ->with($paymentInfo)
            ->willReturn($paymentDO);

        $commandPool->expects(static::once())
            ->method('get')
            ->with('authorize')
            ->willReturn($command);
        $command->expects(static::once())
            ->method('execute')
            ->with(['amount' => 10, 'payment' => $paymentDO])
            ->willReturn(null);

        $adapter->authorize($paymentInfo, 10);
    }
}
