<?php

namespace Magento\Company\Test\Unit\Model\Email;

/**
 * Unit test for @see \Magento\Company\Model\Email\Transporter model.
 */
class TransporterTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $transportBuilder;

    /**
     * @var \Magento\Framework\Mail\TransportInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $transport;

    /**
     * @var \Magento\Framework\Escaper|\PHPUnit_Framework_MockObject_MockObject
     */
    private $escaper;

    /**
     * @var \Psr\Log\LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $logger;

    /**
     * @var \Magento\Company\Model\Email\Transporter
     */
    private $transporter;

    /**
     * setUp
     * @return void
     */
    protected function setUp()
    {
        $this->transportBuilder = $this
            ->getMockBuilder(\Magento\Framework\Mail\Template\TransportBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->transport = $this->getMockBuilder(\Magento\Framework\Mail\TransportInterface::class)->getMock();
        $this->transportBuilder->expects($this->any())->method('getTransport')->willReturn($this->transport);
        $this->escaper = $this->getMockBuilder(\Magento\Framework\Escaper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->logger = $this->getMockBuilder(\Psr\Log\LoggerInterface::class)->getMock();

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->transporter = $objectManagerHelper->getObject(
            \Magento\Company\Model\Email\Transporter::class,
            [
                'transportBuilder' => $this->transportBuilder,
                'escaper' => $this->escaper,
                'logger' => $this->logger
            ]
        );
    }

    /**
     * @param string $customerEmail
     * @param string $customerName
     * @param string $from
     * @param string $templateId
     * @param array $templateParams
     * @param null $storeId
     * @param array $bcc
     * @dataProvider sendMessageDataProvider
     */
    public function testSendMessage(
        $customerEmail,
        $customerName,
        $from,
        $templateId,
        $templateParams,
        $storeId,
        $bcc
    ) {
        $this->transportBuilder
            ->expects($this->once())
            ->method('setTemplateIdentifier')
            ->with($templateId)
            ->willReturnSelf();
        $this->transportBuilder
            ->expects($this->once())
            ->method('setTemplateOptions')
            ->with(['area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => $storeId])
            ->willReturnSelf();
        $this->transportBuilder
            ->expects($this->once())
            ->method('setTemplateVars')
            ->with(['escaper' => $this->escaper])
            ->willReturnSelf();
        $this->transportBuilder
            ->expects($this->once())
            ->method('addTo')
            ->with($customerEmail, $customerName)
            ->willReturnSelf();
        $this->transportBuilder->expects($this->once())->method('setFrom')->with($from)->willReturnSelf();
        $this->transportBuilder->expects($this->once())->method('addBcc')->with($bcc)->willReturnSelf();
        $this->transportBuilder->expects($this->once())->method('getTransport')->with()->willReturn($this->transport);
        $this->transport->expects($this->once())->method('sendMessage');
        $this->transporter->sendMessage(
            $customerEmail,
            $customerName,
            $from,
            $templateId,
            $templateParams,
            $storeId,
            $bcc
        );
    }

    /**
     * @param string $customerEmail
     * @param string $customerName
     * @param string $from
     * @param string $templateId
     * @param array $templateParams
     * @param null $storeId
     * @param array $bcc
     * @dataProvider sendMessageDataProvider
     */
    public function testSendMessageWithException(
        $customerEmail,
        $customerName,
        $from,
        $templateId,
        $templateParams,
        $storeId,
        $bcc
    ) {
        $this->transportBuilder
            ->expects($this->once())
            ->method('setTemplateIdentifier')
            ->with($templateId)
            ->willReturnSelf();
        $this->transportBuilder
            ->expects($this->once())
            ->method('setTemplateOptions')
            ->with(['area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => $storeId])
            ->willReturnSelf();
        $this->transportBuilder
            ->expects($this->once())
            ->method('setTemplateVars')
            ->with(['escaper' => $this->escaper])
            ->willReturnSelf();
        $this->transportBuilder
            ->expects($this->once())
            ->method('addTo')
            ->with($customerEmail, $customerName)
            ->willReturnSelf();
        $this->transportBuilder->expects($this->once())->method('setFrom')->with($from)->willReturnSelf();
        $this->transportBuilder->expects($this->once())->method('addBcc')->with($bcc)->willReturnSelf();
        $this->transportBuilder
            ->expects($this->once())
            ->method('getTransport')
            ->with()
            ->willReturn($this->transport);
        $exception = new \Magento\Framework\Exception\MailException(__('error message'));
        $this->transport->expects($this->once())->method('sendMessage')->willThrowException($exception);
        $this->logger->expects($this->once())->method('critical')->with($exception);
        $this->transporter->sendMessage(
            $customerEmail,
            $customerName,
            $from,
            $templateId,
            $templateParams,
            $storeId,
            $bcc
        );
    }

    /**
     * @return array
     */
    public function sendMessageDataProvider()
    {
        return [
            [
                'customer@email.com',
                'customer name',
                'from',
                'templateId',
                [],
                null,
                []
            ]
        ];
    }
}
