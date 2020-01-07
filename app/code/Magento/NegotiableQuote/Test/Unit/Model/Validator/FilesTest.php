<?php
namespace Magento\NegotiableQuote\Test\Unit\Model\Validator;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Unit test for Files.
 */
class FilesTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\NegotiableQuote\Model\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $config;

    /**
     * @var \Magento\NegotiableQuote\Model\Validator\ValidatorResultFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $validatorResultFactory;

    /**
     * @var \Magento\NegotiableQuote\Model\Validator\Files
     */
    private $files;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->config = $this->getMockBuilder(\Magento\NegotiableQuote\Model\Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->validatorResultFactory = $this
            ->getMockBuilder(\Magento\NegotiableQuote\Model\Validator\ValidatorResultFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->files = $objectManagerHelper->getObject(
            \Magento\NegotiableQuote\Model\Validator\Files::class,
            [
                'config' => $this->config,
                'validatorResultFactory' => $this->validatorResultFactory,
            ]
        );
    }

    /**
     * Test for validate().
     *
     * @return void
     */
    public function testValidate()
    {
        $result = $this->getMockBuilder(\Magento\NegotiableQuote\Model\Validator\ValidatorResult::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->validatorResultFactory->expects($this->atLeastOnce())->method('create')->willReturn($result);
        $this->config->expects($this->atLeastOnce())->method('getAllowedExtensions')->willReturn('txt,doc');
        $this->config->expects($this->atLeastOnce())->method('getMaxFileSize')->willReturn(10);
        $file = $this->getMockBuilder(\Magento\NegotiableQuote\Api\Data\AttachmentContentInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $file->expects($this->atLeastOnce())->method('getName')->willReturn('attachment.txt');
        $file->expects($this->atLeastOnce())->method('getBase64EncodedData')->willReturn(base64_encode('content'));
        $result->expects($this->never())->method('addMessage')->willReturnSelf();
        $data = ['files' => [$file]];

        $this->assertInstanceOf(
            \Magento\NegotiableQuote\Model\Validator\ValidatorResult::class,
            $this->files->validate($data)
        );
    }

    /**
     * Test for validate() with message.
     *
     * @param string $fileName
     * @param int|float $allowedSize
     * @return void
     * @dataProvider validateWithMessageDataProvider
     */
    public function testValidateWithMessage($fileName, $allowedSize)
    {
        $result = $this->getMockBuilder(\Magento\NegotiableQuote\Model\Validator\ValidatorResult::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->validatorResultFactory->expects($this->atLeastOnce())->method('create')->willReturn($result);
        $this->config->expects($this->atLeastOnce())->method('getAllowedExtensions')->willReturn('txt,doc');
        $this->config->expects($this->atLeastOnce())->method('getMaxFileSize')->willReturn($allowedSize);
        $file = $this->getMockBuilder(\Magento\NegotiableQuote\Api\Data\AttachmentContentInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $file->expects($this->atLeastOnce())->method('getName')->willReturn($fileName);
        $file->expects($this->atLeastOnce())->method('getBase64EncodedData')->willReturn(base64_encode('content'));
        $result->expects($this->atLeastOnce())->method('addMessage')->willReturnSelf();
        $data = ['files' => [$file]];

        $this->assertInstanceOf(
            \Magento\NegotiableQuote\Model\Validator\ValidatorResult::class,
            $this->files->validate($data)
        );
    }

    /**
     * Test for validate() without files.
     *
     * @return void
     */
    public function testValidateWithoutFiles()
    {
        $result = $this->getMockBuilder(\Magento\NegotiableQuote\Model\Validator\ValidatorResult::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->validatorResultFactory->expects($this->atLeastOnce())->method('create')->willReturn($result);
        $this->config->expects($this->never())->method('getAllowedExtensions');

        $this->assertInstanceOf(
            \Magento\NegotiableQuote\Model\Validator\ValidatorResult::class,
            $this->files->validate([])
        );
    }

    /**
     * Test for validate() with excessive files amount.
     *
     * @return void
     */
    public function testValidateWithExcessiveFilesAmount()
    {
        $result = $this->getMockBuilder(\Magento\NegotiableQuote\Model\Validator\ValidatorResult::class)
            ->disableOriginalConstructor()
            ->getMock();
        $result->expects($this->atLeastOnce())->method('addMessage')->willReturnSelf();
        $this->validatorResultFactory->expects($this->atLeastOnce())->method('create')->willReturn($result);
        $this->config->expects($this->never())->method('getAllowedExtensions');
        $data = ['files' => ['a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k']];

        $this->assertInstanceOf(
            \Magento\NegotiableQuote\Model\Validator\ValidatorResult::class,
            $this->files->validate($data)
        );
    }

    /**
     * DataProvider for validateWithMessage().
     *
     * @return array
     */
    public function validateWithMessageDataProvider()
    {
        return [
            ['attachment.jpg', 10],
            ['#attachment.jpg', 10],
            ['attachment.jpg', 0.00000001]
        ];
    }
}
