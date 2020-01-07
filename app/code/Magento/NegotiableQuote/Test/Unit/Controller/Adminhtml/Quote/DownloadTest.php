<?php

namespace Magento\NegotiableQuote\Test\Unit\Controller\Adminhtml\Quote;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\App\Response\Http as HttpResponse;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\NegotiableQuote\Controller\Quote\Download;
use Magento\NegotiableQuote\Model\Attachment\DownloadProvider;
use Magento\NegotiableQuote\Model\Attachment\DownloadProviderFactory;
use PHPUnit\Framework\MockObject\MockObject as MockObject;

/**
 * Class DownloadTest
 */
class DownloadTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var DownloadProviderFactory|MockObject
     */
    protected $downloadProviderFactory;

    /**
     * @var Download|MockObject
     */
    protected $download;

    /**
     * @var DownloadProvider|MockObject
     */
    protected $downloadProvider;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->downloadProvider = $this->createPartialMock(
            DownloadProvider::class,
            ['canDownload', 'getAttachmentContents']
        );
        $this->downloadProvider->expects($this->any())
            ->method('getAttachmentContents')
            ->willReturn('data');
        $this->downloadProviderFactory = $this->createPartialMock(
            DownloadProviderFactory::class,
            ['create']
        );
        $this->downloadProviderFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->downloadProvider);
        $objectManager = new ObjectManager($this);
        $request = $this->createPartialMock(HttpRequest::class, ['getParam'], []);
        $request->expects($this->any())->method('getParam')
            ->with('attachmentId')
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
            ->willThrowException(new NotFoundException(__('Attachment not found.')));
        $this->download->execute();
    }
}
