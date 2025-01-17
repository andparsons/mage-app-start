<?php
namespace Magento\Braintree\Test\Unit\Controller\Payment;

use Magento\Braintree\Controller\Payment\GetNonce;
use Magento\Braintree\Gateway\Command\GetPaymentNonceCommand;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Webapi\Exception;
use Magento\Payment\Gateway\Command\ResultInterface as CommandResultInterface;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Psr\Log\LoggerInterface;

/**
 * Class GetNonceTest
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class GetNonceTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var GetNonce
     */
    private $action;

    /**
     * @var GetPaymentNonceCommand|MockObject
     */
    private $commandMock;

    /**
     * @var Session|MockObject
     */
    private $sessionMock;

    /**
     * @var LoggerInterface|MockObject
     */
    private $loggerMock;

    /**
     * @var ResultFactory|MockObject
     */
    private $resultFactoryMock;

    /**
     * @var ResultInterface|MockObject
     */
    private $resultMock;

    /**
     * @var Http|MockObject
     */
    private $requestMock;

    /**
     * @var CommandResultInterface|MockObject
     */
    private $commandResultMock;

    protected function setUp()
    {
        $this->initResultFactoryMock();

        $this->requestMock = $this->getMockBuilder(RequestInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getParam'])
            ->getMock();

        $this->commandMock = $this->getMockBuilder(GetPaymentNonceCommand::class)
            ->disableOriginalConstructor()
            ->setMethods(['execute', '__wakeup'])
            ->getMock();

        $this->commandResultMock = $this->getMockBuilder(CommandResultInterface::class)
            ->setMethods(['get'])
            ->getMock();

        $this->sessionMock = $this->getMockBuilder(Session::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCustomerId', 'getStoreId'])
            ->getMock();
        $this->sessionMock->expects(static::once())
            ->method('getStoreId')
            ->willReturn(null);

        $this->loggerMock = $this->createMock(LoggerInterface::class);

        $context = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $context->expects(static::any())
            ->method('getRequest')
            ->willReturn($this->requestMock);
        $context->expects(static::any())
            ->method('getResultFactory')
            ->willReturn($this->resultFactoryMock);

        $managerHelper = new ObjectManager($this);
        $this->action = $managerHelper->getObject(GetNonce::class, [
            'context' => $context,
            'logger' => $this->loggerMock,
            'session' => $this->sessionMock,
            'command' => $this->commandMock,
        ]);
    }

    /**
     * @covers \Magento\Braintree\Controller\Payment\GetNonce::execute
     */
    public function testExecuteWithException()
    {
        $this->requestMock->expects(static::once())
            ->method('getParam')
            ->with('public_hash')
            ->willReturn(null);

        $this->sessionMock->expects(static::once())
            ->method('getCustomerId')
            ->willReturn(null);

        $exception = new \Exception('The "publicHash" field does not exists');
        $this->commandMock->expects(static::once())
            ->method('execute')
            ->willThrowException($exception);

        $this->loggerMock->expects(static::once())
            ->method('critical')
            ->with($exception);

        $this->resultMock->expects(static::once())
            ->method('setHttpResponseCode')
            ->with(Exception::HTTP_BAD_REQUEST);
        $this->resultMock->expects(static::once())
            ->method('setData')
            ->with(['message' => 'Sorry, but something went wrong']);

        $this->action->execute();
    }

    /**
     * @covers \Magento\Braintree\Controller\Payment\GetNonce::execute
     */
    public function testExecute()
    {
        $customerId = 1;
        $publicHash = '65b7bae0dcb690d93';
        $nonce = 'f1hc45';

        $this->requestMock->expects(static::once())
            ->method('getParam')
            ->with('public_hash')
            ->willReturn($publicHash);

        $this->sessionMock->expects(static::once())
            ->method('getCustomerId')
            ->willReturn($customerId);

        $this->commandResultMock->expects(static::once())
            ->method('get')
            ->willReturn([
                'paymentMethodNonce' => $nonce
            ]);
        $this->commandMock->expects(static::once())
            ->method('execute')
            ->willReturn($this->commandResultMock);

        $this->resultMock->expects(static::once())
            ->method('setData')
            ->with(['paymentMethodNonce' => $nonce]);

        $this->loggerMock->expects(static::never())
            ->method('critical');

        $this->resultMock->expects(static::never())
            ->method('setHttpResponseCode');

        $this->action->execute();
    }

    /**
     * Create mock for result factory object
     */
    private function initResultFactoryMock()
    {
        $this->resultMock = $this->getMockBuilder(ResultInterface::class)
            ->setMethods(['setHttpResponseCode', 'renderResult', 'setHeader', 'setData'])
            ->getMock();

        $this->resultFactoryMock = $this->getMockBuilder(ResultFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->resultFactoryMock->expects(static::once())
            ->method('create')
            ->willReturn($this->resultMock);
    }
}
