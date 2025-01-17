<?php
namespace Magento\Framework\File\Test\Unit;

class MimeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\File\Mime
     */
    private $object;

    protected function setUp()
    {
        $this->object = new \Magento\Framework\File\Mime();
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage File 'nonexistent.file' doesn't exist
     */
    public function testGetMimeTypeNonexistentFileException()
    {
        $file = 'nonexistent.file';
        $this->object->getMimeType($file);
    }

    /**
     * @param string $file
     * @param string $expectedType
     *
     * @dataProvider getMimeTypeDataProvider
     */
    public function testGetMimeType($file, $expectedType)
    {
        $actualType = $this->object->getMimeType($file);
        $this->assertSame($expectedType, $actualType);
    }

    /**
     * @return array
     */
    public function getMimeTypeDataProvider()
    {
        return [
            'javascript' => [__DIR__ . '/_files/javascript.js', 'application/javascript'],
            'weird extension' => [__DIR__ . '/_files/file.weird', 'application/octet-stream'],
            'weird uppercase extension' => [__DIR__ . '/_files/UPPERCASE.WEIRD', 'application/octet-stream'],
        ];
    }
}
