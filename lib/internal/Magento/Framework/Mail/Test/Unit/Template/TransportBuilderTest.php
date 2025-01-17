<?php
declare(strict_types=1);

namespace Magento\Framework\Mail\Test\Unit\Template;

use Magento\Framework\App\TemplateTypesInterface;
use Magento\Framework\Mail\EmailMessageInterface;
use Magento\Framework\Mail\EmailMessageInterfaceFactory;
use Magento\Framework\Mail\Message;
use Magento\Framework\Mail\MessageInterfaceFactory;
use Magento\Framework\Mail\MimePartInterface;
use Magento\Framework\Mail\MimePartInterfaceFactory;
use Magento\Framework\Mail\Template\FactoryInterface;
use Magento\Framework\Mail\Template\SenderResolverInterface;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Mail\TemplateInterface;
use Magento\Framework\Mail\TransportInterface;
use Magento\Framework\Mail\TransportInterfaceFactory;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class TransportBuilderTest
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class TransportBuilderTest extends TestCase
{
    /**
     * @var string
     */
    protected $builderClassName = TransportBuilder::class;

    /**
     * @var TransportBuilder
     */
    protected $builder;

    /**
     * @var FactoryInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $templateFactoryMock;

    /**
     * @var Message | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $messageMock;

    /**
     * @var ObjectManagerInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectManagerMock;

    /**
     * @var SenderResolverInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $senderResolverMock;

    /**
     * @var MessageInterfaceFactory| \PHPUnit_Framework_MockObject_MockObject
     */
    private $messageFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $mailTransportFactoryMock;

    /**
     * @var MimePartInterfaceFactory|MockObject
     */
    private $mimePartFactoryMock;

    /**
     * @var EmailMessageInterfaceFactory|MockObject
     */
    private $emailMessageInterfaceFactoryMock;

    /**
     * @return void
     */
    protected function setUp()
    {
        $objectManagerHelper = new ObjectManager($this);
        $this->templateFactoryMock = $this->createMock(FactoryInterface::class);
        $this->messageMock = $this->createMock(Message::class);
        $this->objectManagerMock = $this->createMock(ObjectManagerInterface::class);
        $this->senderResolverMock = $this->createMock(SenderResolverInterface::class);
        $this->mailTransportFactoryMock = $this->getMockBuilder(
            TransportInterfaceFactory::class
        )->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMockForAbstractClass();
        $this->messageFactoryMock = $this->getMockBuilder(MessageInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMockForAbstractClass();

        $this->emailMessageInterfaceFactoryMock = $this->createMock(EmailMessageInterfaceFactory::class);
        $this->mimePartFactoryMock = $this->createMock(MimePartInterfaceFactory::class);

        $this->builder = $objectManagerHelper->getObject(
            $this->builderClassName,
            [
                'templateFactory' => $this->templateFactoryMock,
                'message' => $this->messageMock,
                'objectManager' => $this->objectManagerMock,
                'senderResolver' => $this->senderResolverMock,
                'mailTransportFactory' => $this->mailTransportFactoryMock,
                'messageFactory' => $this->messageFactoryMock,
                'emailMessageInterfaceFactory' => $this->emailMessageInterfaceFactoryMock,
                'mimePartInterfaceFactory' => $this->mimePartFactoryMock,
            ]
        );
    }

    /**
     * @dataProvider getTransportDataProvider
     * @param int $templateType
     * @param string $bodyText
     * @param string $templateNamespace
     * @return void
     */
    public function testGetTransport($templateType, $bodyText, $templateNamespace)
    {
        $this->builder->setTemplateModel($templateNamespace);

        $vars = ['reason' => 'Reason', 'customer' => 'Customer'];
        $options = ['area' => 'frontend', 'store' => 1];

        /** @var MimePartInterface|MockObject $mimePartMock */
        $mimePartMock = $this->createMock(MimePartInterface::class);

        $this->mimePartFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($mimePartMock);

        /** @var EmailMessageInterface|MockObject $emailMessage */
        $emailMessage = $this->createMock(EmailMessageInterface::class);

        $this->emailMessageInterfaceFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($emailMessage);

        $template = $this->createMock(TemplateInterface::class);
        $template->expects($this->once())->method('setVars')->with($this->equalTo($vars))->willReturnSelf();
        $template->expects($this->once())->method('setOptions')->with($this->equalTo($options))->willReturnSelf();
        $template->expects($this->once())->method('getSubject')->willReturn('Email Subject');
        $template->expects($this->once())->method('getType')->willReturn($templateType);
        $template->expects($this->once())->method('processTemplate')->willReturn($bodyText);

        $this->templateFactoryMock->expects($this->once())
            ->method('get')
            ->with($this->equalTo('identifier'), $this->equalTo($templateNamespace))
            ->willReturn($template);

        $transport = $this->createMock(TransportInterface::class);

        $this->mailTransportFactoryMock->expects($this->at(0))
            ->method('create')
            ->willReturn($transport);

        $this->builder->setTemplateIdentifier('identifier')->setTemplateVars($vars)->setTemplateOptions($options);

        $result = $this->builder->getTransport();
        $this->assertInstanceOf(TransportInterface::class, $result);
    }

    /**
     * Test get transport with exception
     *
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Unknown template type
     */
    public function testGetTransportWithException()
    {
        $this->builder->setTemplateModel('Test\Namespace\Template');

        $vars = ['reason' => 'Reason', 'customer' => 'Customer'];
        $options = ['area' => 'frontend', 'store' => 1];

        $template = $this->createMock(TemplateInterface::class);
        $template->expects($this->once())->method('setVars')->with($this->equalTo($vars))->willReturnSelf();
        $template->expects($this->once())->method('setOptions')->with($this->equalTo($options))->willReturnSelf();
        $template->expects($this->once())->method('getType')->willReturn('Unknown');
        $this->templateFactoryMock->expects($this->once())
            ->method('get')
            ->with($this->equalTo('identifier'), $this->equalTo('Test\Namespace\Template'))
            ->willReturn($template);

        $this->builder->setTemplateIdentifier('identifier')->setTemplateVars($vars)->setTemplateOptions($options);

        $this->assertInstanceOf(TransportInterface::class, $this->builder->getTransport());
    }

    /**
     * @return array
     */
    public function getTransportDataProvider()
    {
        return [
            [
                TemplateTypesInterface::TYPE_TEXT,
                'Plain text',
                null
            ],
            [
                TemplateTypesInterface::TYPE_HTML,
                '<h1>Html message</h1>',
                'Test\Namespace\Template'
            ]
        ];
    }

    /**
     * @return void
     */
    public function testSetFromByScope()
    {
        $sender = ['email' => 'from@example.com', 'name' => 'name'];
        $scopeId = 1;
        $this->senderResolverMock->expects($this->once())
            ->method('resolve')
            ->with($sender, $scopeId)
            ->willReturn($sender);

        $this->builder->setFromByScope($sender, $scopeId);
    }
}
