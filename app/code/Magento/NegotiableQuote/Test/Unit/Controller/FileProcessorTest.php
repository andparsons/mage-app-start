<?php
namespace Magento\NegotiableQuote\Test\Unit\Controller;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\NegotiableQuote\Api\Data\AttachmentContentInterface;

/**
 * Unit test for FileProcessor.
 */
class FileProcessorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $request;

    /**
     * @var \Magento\NegotiableQuote\Api\Data\AttachmentContentInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $attachmentFactory;

    /**
     * @var \Magento\Framework\Filesystem\File\ReadFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $readFactory;

    /**
     * @var \Magento\NegotiableQuote\Controller\FileProcessor
     */
    private $fileProcessor;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->request = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getFiles'])
            ->getMockForAbstractClass();
        $this->attachmentFactory = $this
            ->getMockBuilder(\Magento\NegotiableQuote\Api\Data\AttachmentContentInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->readFactory = $this->getMockBuilder(\Magento\Framework\Filesystem\File\ReadFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->fileProcessor = $objectManagerHelper->getObject(
            \Magento\NegotiableQuote\Controller\FileProcessor::class,
            [
                'request' => $this->request,
                'attachmentFactory' => $this->attachmentFactory,
                'readFactory' => $this->readFactory,
            ]
        );
    }

    /**
     * Test for getFiles().
     *
     * @return void
     */
    public function testGetFiles()
    {
        $fileContent = 'file_content';
        $filesData = [
            [
                'tmp_name' => 'file_name_temp.txt',
                'name' => 'file_name.txt',
                'size' => 10,
                'type' => 'txt'
            ]
        ];
        $this->request->expects($this->atLeastOnce())->method('getFiles')->with('files')->willReturn($filesData);
        $fileReader = $this->getMockBuilder(\Magento\Framework\Filesystem\File\ReadInterface::class)
            ->disableArgumentCloning()
            ->getMockForAbstractClass();
        $fileReader->expects($this->atLeastOnce())->method('read')->willReturn($fileContent);
        $this->readFactory->expects($this->atLeastOnce())->method('create')->willReturn($fileReader);
        $result = [
            'data' => [
                AttachmentContentInterface::BASE64_ENCODED_DATA => base64_encode($fileContent),
                AttachmentContentInterface::TYPE => $filesData[0]['type'],
                AttachmentContentInterface::NAME => $filesData[0]['name'],
            ]
        ];
        $this->attachmentFactory->expects($this->atLeastOnce())->method('create')->willReturn($result);

        $this->assertEquals([$result], $this->fileProcessor->getFiles());
    }
}
