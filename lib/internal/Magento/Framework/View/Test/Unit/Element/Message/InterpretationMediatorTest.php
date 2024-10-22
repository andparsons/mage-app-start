<?php
namespace Magento\Framework\View\Test\Unit\Element\Message;

use Magento\Framework\Message\MessageInterface;
use Magento\Framework\View\Element\Message\InterpretationMediator;
use Magento\Framework\View\Element\Message\InterpretationStrategy;

class InterpretationMediatorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var InterpretationStrategy | \PHPUnit_Framework_MockObject_MockObject
     */
    private $interpretationStrategy;

    /**
     * @var MessageInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    private $messageMock;

    /**
     * @var InterpretationMediator
     */
    private $interpretationMediator;

    protected function setUp()
    {
        $this->interpretationStrategy = $this->getMockBuilder(
            \Magento\Framework\View\Element\Message\InterpretationStrategy::class
        )
            ->disableOriginalConstructor()
            ->getMock();
        $this->messageMock = $this->createMock(
            \Magento\Framework\Message\MessageInterface::class
        );

        $this->interpretationMediator = new InterpretationMediator(
            $this->interpretationStrategy
        );
    }

    public function testInterpretNotIdentifiedMessage()
    {
        $messageText = 'Very awful message. Shame.';
        $this->messageMock->expects(static::once())
            ->method('getIdentifier')
            ->willReturn(null);
        $this->messageMock->expects(static::once())
            ->method('getText')
            ->willReturn($messageText);

        static::assertSame(
            $messageText,
            $this->interpretationMediator->interpret($this->messageMock)
        );
    }

    public function testInterpretIdentifiedMessage()
    {
        $messageInterpreted = 'Awesome message. An identified message is the one we appreciate.';

        $this->messageMock->expects(static::once())
            ->method('getIdentifier')
            ->willReturn('Great identifier');
        $this->interpretationStrategy->expects(static::once())
            ->method('interpret')
            ->with($this->messageMock)
            ->willReturn($messageInterpreted);

        static::assertSame(
            $messageInterpreted,
            $this->interpretationMediator->interpret($this->messageMock)
        );
    }

    public function testInterpretIdentifiedMessageNotConfigured()
    {
        $messageStillNotInterpreted = 'One step left to call it an awesome message.';

        $this->messageMock->expects(static::once())
            ->method('getIdentifier')
            ->willReturn('Great identifier');
        $this->messageMock->expects(static::once())
            ->method('getText')
            ->willReturn($messageStillNotInterpreted);
        $this->interpretationStrategy->expects(static::once())
            ->method('interpret')
            ->with($this->messageMock)
            ->willThrowException(new \LogicException());

        static::assertSame(
            $messageStillNotInterpreted,
            $this->interpretationMediator->interpret($this->messageMock)
        );
    }
}
