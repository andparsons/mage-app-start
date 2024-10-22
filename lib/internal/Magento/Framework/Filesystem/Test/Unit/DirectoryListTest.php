<?php
namespace Magento\Framework\Filesystem\Test\Unit;

use \Magento\Framework\Filesystem\DirectoryList;

class DirectoryListTest extends \PHPUnit\Framework\TestCase
{
    public function testGetDefaultConfig()
    {
        $this->assertArrayHasKey(DirectoryList::SYS_TMP, DirectoryList::getDefaultConfig());
    }

    /**
     * @param array $config
     * @param string $expectedError
     * @dataProvider validateDataProvider
     */
    public function testValidate($config, $expectedError)
    {
        $this->expectException('\InvalidArgumentException');
        $this->expectExceptionMessage($expectedError);
        DirectoryList::validate($config);
    }

    /**
     * @return array
     */
    public function validateDataProvider()
    {
        return [
            ['', 'Unexpected value type.'],
            [1, 'Unexpected value type.'],
            [[DirectoryList::SYS_TMP => ''], 'Unexpected value type.'],
            [[DirectoryList::SYS_TMP => 1], 'Unexpected value type.'],
            [[DirectoryList::SYS_TMP => []], 'Missing required keys at: ' . DirectoryList::SYS_TMP],
        ];
    }

    public function testGetters()
    {
        $customDirs = [DirectoryList::SYS_TMP => [DirectoryList::PATH => '/bar/dir', DirectoryList::URL_PATH => 'bar']];
        $object = new DirectoryList('/root/dir', $customDirs);
        $this->assertEquals('/bar/dir', $object->getPath(DirectoryList::SYS_TMP));
        $this->assertEquals('bar', $object->getUrlPath(DirectoryList::SYS_TMP));
        $this->assertEquals('/root/dir', $object->getRoot());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Unknown type: foo
     */
    public function testUnknownType()
    {
        new DirectoryList('/root/dir', ['foo' => [DirectoryList::PATH => '/foo/dir']]);
    }

    /**
     * @param string $method
     * @expectedException \Magento\Framework\Exception\FileSystemException
     * @expectedExceptionMessage Unknown directory type: 'foo'
     * @dataProvider assertCodeDataProvider
     */
    public function testAssertCode($method)
    {
        $object = new DirectoryList('/root/dir');
        $object->$method('foo');
    }

    /**
     * @return array
     */
    public function assertCodeDataProvider()
    {
        return [['getPath', 'getUrlPath']];
    }

    /**
     * @param array $config
     * @param string|bool $expected
     * @dataProvider getUrlPathDataProvider
     */
    public function testGetUrlPath($config, $expected)
    {
        $object = new DirectoryList('/root/dir', $config);
        $this->assertEquals($expected, $object->getUrlPath(DirectoryList::SYS_TMP));
    }

    /**
     * @return array
     */
    public function getUrlPathDataProvider()
    {
        return [
            [[], false],
            [[DirectoryList::SYS_TMP => [DirectoryList::URL_PATH => 'url/path']], 'url/path'],
        ];
    }

    public function testFilterPath()
    {
        $object = new DirectoryList('/root/dir', [DirectoryList::SYS_TMP => [DirectoryList::PATH => 'C:\Windows\Tmp']]);
        $this->assertEquals('C:/Windows/Tmp', $object->getPath(DirectoryList::SYS_TMP));
    }

    public function testPrependRoot()
    {
        $object = new DirectoryList('/root/dir', [DirectoryList::SYS_TMP => [DirectoryList::PATH => 'tmp']]);
        $this->assertEquals('/root/dir/tmp', $object->getPath(DirectoryList::SYS_TMP));
    }

    /**
     * @param string $value
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage URL path must be relative directory path in lowercase with '/' directory separator:
     * @dataProvider assertUrlPathDataProvider
     */
    public function testAssertUrlPath($value)
    {
        new DirectoryList('/root/dir', [DirectoryList::SYS_TMP => [DirectoryList::URL_PATH => $value]]);
    }

    /**
     * @return array
     */
    public function assertUrlPathDataProvider()
    {
        return [
            ['/'],
            ['//'],
            ['/value'],
            ['value/'],
            ['/value/'],
            ['one\\two'],
            ['../dir'],
            ['./dir'],
            ['one/../two']
        ];
    }
}
