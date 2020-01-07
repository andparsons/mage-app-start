<?php

namespace Magento\NegotiableQuote\Test\Unit\Model\Attachment;

/**
 * Unit test for Uploader.
 */
class UploaderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $negotiableQuoteConfig;

    /**
     * @var \Magento\NegotiableQuote\Model\Attachment\Uploader|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $uploader;

    /**
     * @var string
     */
    private $filename;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    private $objectManager;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->setupFiles();
        $this->negotiableQuoteConfig = $this->createMock(
            \Magento\NegotiableQuote\Model\Config::class
        );
        $this->negotiableQuoteConfig->expects($this->any())->method('getMaxFileSize')->willReturn(1);
        $this->negotiableQuoteConfig->expects($this->any())
            ->method('getAllowedExtensions')
            ->willReturn('doc,txt,jpg,png');
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $class = new \ReflectionObject($this);
        $this->filename = $class->getFilename();
        $this->setupTestData();
    }

    /**
     * Setup global variable $_FILES.
     *
     * @param int $fileSize [optional]
     * @param string $fileName [optional]
     * @param string $fileType [optional]
     * @return void
     */
    private function setupFiles(
        $fileSize = 1234567890123,
        $fileName = 'sample-file.doc',
        $fileType = 'application/msword'
    ) {
        $_FILES = [
            'file[0]' => [
                'name' => $fileName,
                'type' => $fileType,
                'tmp_name' => $this->filename,
                'error' => 0,
                'size' => $fileSize,
            ]
        ];
    }

    /**
     * Setup config.
     *
     * @param int $fileSize [optional]
     * @param string $fileName [optional]
     * @param string $fileType [optional]
     * @return void
     */
    private function setupTestData(
        $fileSize = 1234567890123,
        $fileName = 'sample-file.doc',
        $fileType = 'application/msword'
    ) {
        $this->uploader = $this->objectManager->getObject(
            \Magento\NegotiableQuote\Model\Attachment\Uploader::class,
            [
                'negotiableQuoteConfig' => $this->negotiableQuoteConfig,
                'fileId' => [
                    'name' => $fileName,
                    'type' => $fileType,
                    'tmp_name' => $this->filename,
                    'error' => 0,
                    'size' => $fileSize,
                ]
            ]
        );
        $this->uploader->processFileAttributes([
            'name' => $fileName,
            'type' => $fileType,
            'tmp_name' => $this->filename,
            'error' => 0,
            'size' => $fileSize,
        ]);
    }

    /**
     * Test validateSize().
     *
     * @return void
     */
    public function testValidateSizeFailed()
    {
        $this->assertFalse($this->uploader->validateSize());
    }

    /**
     * Test validateSize() - failed.
     *
     * @return void
     */
    public function testValidateSizePassed()
    {
        $this->setupFiles(123456);
        $this->setupTestData(123456);
        $this->assertTrue($this->uploader->validateSize());
    }

    /**
     * Test validateNameLength.
     *
     * @return void
     */
    public function testValidateNameLengthPassed()
    {
        $this->assertTrue($this->uploader->validateNameLength());
    }

    /**
     * Test validateNameLength - failed.
     *
     * @return void
     */
    public function testValidateNameLengthFailed()
    {
        $this->setupFiles(1234567890123, 'sample-file-with-veeeeery-looooong-title.doc');
        $this->setupTestData(1234567890123, 'sample-file-with-veeeeery-looooong-title.doc');
        $this->assertFalse($this->uploader->validateNameLength());
    }

    /**
     * Test checkAllowedExtension() - failed.
     *
     * @return void
     */
    public function testCheckAllowedExtensionFailed()
    {
        $this->setupFiles(1234567890123, 'sample-file.zip', 'application/x-zip-compressed');
        $this->setupTestData(1234567890123, 'sample-file.zip', 'application/x-zip-compressed');
        $this->assertFalse($this->uploader->checkAllowedExtension($this->uploader->getFileExtension()));
    }

    /**
     * TearDown.
     *
     * @return void
     */
    protected function tearDown()
    {
        unset($_FILES['file[0]']);
    }
}
