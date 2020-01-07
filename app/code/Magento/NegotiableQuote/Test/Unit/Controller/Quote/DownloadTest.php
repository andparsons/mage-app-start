<?php

namespace Magento\NegotiableQuote\Test\Unit\Controller\Quote;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\App\Response\Http as HttpResponse;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\NegotiableQuote\Controller\Quote\Download;
use Magento\NegotiableQuote\Model\Attachment\DownloadProvider;
use Magento\NegotiableQuote\Model\Attachment\DownloadProviderFactory;
use PHPUnit\Framework\MockObject\MockObject as MockObject;
use Psr\Log\LoggerInterface;

/**
 * Class DownloadTest
 */
class DownloadTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var DownloadProviderFactory|MockObject
     */
    private $downloadProviderFactory;

    /**
     * @var Download|MockObject
     */
    private $download;

    /**
     * @var DownloadProvider|MockObject
     */
    private $downloadProvider;

    /**
     * @var LoggerInterface|MockObject
     */
    private $logger;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->downloadProvider = $this->createPartialMock(
            DownloadProvider::class,
            ['getAttachmentContents']
        );
        $this->downloadProviderFactory = $this->createPartialMock(
            DownloadProviderFactory::class,
            ['create']
        );
        $this->logger = $this->getMockForAbstractClass(
            LoggerInterface::class,
            ['critical'],
            '',
            false,
            false,
            true,
            []
        );
        $this->downloadProviderFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->downloadProvider);
        $objectManager = new ObjectManager($this);
        $request = $this->createPartialMock(HttpRequest::class, ['getParam'], []);
        $request->expects($this->any())->method('getParam')
            ->willReturn(1);
        $response = $this->createMock(HttpResponse::class);
        $response->expects($this->atLeastOnce())
            ->method('setNoCacheHeaders');
        $context = $objectManager->getObject(
            Context::class,
            [
                'request' => $request,
                'response' => $response,
            ]
        );
        $this->download = $objectManager->getObject(
            Download::class,
            [
                'context' => $context,
                'downloadProviderFactory' => $this->downloadProviderFactory,
                'logger' => $this->logger,
            ]
        );
    }

    /**
     * Test execute()
     *
     * @return void
     */
    public function testExecute(): void
    {
        $this->downloadProvider->expects($this->once())
            ->method('getAttachmentContents')
            ->willReturn('data');
        $this->download->execute();
    }

    /**
     * Test execute()
     *
     * @return void
     * @expectedException \Magento\Framework\Exception\NotFoundException
     * @expectedExceptionMessage Attachment not found.
     */
    public function testExecuteWithException(): void
    {
        $this->downloadProvider
            ->expects($this->once())
            ->method('getAttachmentContents')
            ->willThrowException(new \Exception);
        $this->logger->expects($this->once())
            ->method('critical');
        $this->download->execute();
    }
}
