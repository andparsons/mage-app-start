<?php
declare(strict_types=1);

namespace Magento\UrlRewriteImportExport\Test\Unit\Model\Validator;

use Magento\UrlRewriteImportExport\Model\Validator\RequestPath;
use Magento\UrlRewriteImportExport\Model\Import;
use Magento\Framework\Exception\LocalizedException;
use Magento\UrlRewrite\Helper\UrlRewrite as UrlRewriteHelper;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

class RequestPathTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var RequestPath
     */
    private $requestPath;

    /**
     * @var UrlRewriteHelper|MockObject
     */
    private $urlRewriteHelperMock;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->urlRewriteHelperMock = $this->createMock(UrlRewriteHelper::class);
        $this->requestPath = new RequestPath($this->urlRewriteHelperMock);

        parent::setUp();
    }

    /**
     * @return void
     */
    public function testIsValidEmpty()
    {
        $this->urlRewriteHelperMock->expects($this->never())
            ->method('validateRequestPath');
        $this->assertFalse($this->requestPath->isValid([]));
        $this->assertEquals(
            [__('Column %1 is empty', Import::COLUMN_REQUEST_PATH_TITLE)],
            $this->requestPath->getMessages()
        );
    }

    /**
     * @return void
     */
    public function testIsValidWrongPath()
    {
        $this->urlRewriteHelperMock->expects($this->once())
            ->method('validateRequestPath')
            ->with('path')
            ->willThrowException(new LocalizedException(__('Some error')));
        $this->assertFalse($this->requestPath->isValid([Import::COLUMN_REQUEST_PATH => 'path']));
        $this->assertEquals(
            [__('Some error')],
            $this->requestPath->getMessages()
        );
    }

    /**
     * @return void
     */
    public function testIsValid()
    {
        $this->urlRewriteHelperMock->expects($this->once())
            ->method('validateRequestPath')
            ->with('path')
            ->willReturn(true);
        $this->assertTrue($this->requestPath->isValid([Import::COLUMN_REQUEST_PATH => 'path']));
        $this->assertEquals([], $this->requestPath->getMessages());
    }
}
