<?php
declare(strict_types=1);

namespace Magento\UrlRewriteImportExport\Test\Unit\Model;

use Magento\UrlRewriteImportExport\Model\Report;
use Magento\UrlRewriteImportExport\Model\Import;
use Magento\UrlRewriteImportExport\Model\FileFactory;
use Magento\UrlRewriteImportExport\Model\File;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

class ReportTest extends \PHPUnit\Framework\TestCase
{

    /**
     * @var Report
     */
    private $report;

    /**
     * @var FileFactory|MockObject
     */
    private $fileFactoryMock;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->fileFactoryMock = $this->createMock(FileFactory::class);
        $this->report = new Report($this->fileFactoryMock);

        parent::setUp();
    }

    /**
     * @return void
     */
    public function testSave()
    {
        $operationId = 7;
        $rows = [
            ['some path', 'some targer path', 305, 'default', 'Wrong redirect code']
        ];

        /** @var File|MockObject $fileMock */
        $fileMock = $this->createMock(File::class);
        $fileMock->expects($this->exactly(2))
            ->method('addRow')
            ->withConsecutive(
                [[
                    Import::COLUMN_REQUEST_PATH_TITLE,
                    Import::COLUMN_TARGET_PATH_TITLE,
                    Import::COLUMN_REDIRECT_TYPE_TITLE,
                    Import::COLUMN_STORE_VIEW_CODE_TITLE,
                    Import::COLUMN_MESSAGES_TITLE,
                ]],
                [['some path', 'some targer path', 305, 'default', 'Wrong redirect code']]
            );
        $this->fileFactoryMock->expects($this->once())
            ->method('create')
            ->with('operation_7.csv', 'w')
            ->willReturn($fileMock);

        $this->report->save($operationId, $rows);
    }
}
