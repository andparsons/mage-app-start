<?php
namespace Magento\Directory\Test\Unit\Model\Country\Postcode;

class ConfigTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $dataStorageMock;

    protected function setUp()
    {
        $this->dataStorageMock = $this->createMock(\Magento\Directory\Model\Country\Postcode\Config\Data::class);
    }

    public function testGet()
    {
        $expected = ['US' => ['pattern_01' => 'pattern_01', 'pattern_02' => 'pattern_02']];
        $this->dataStorageMock->expects($this->once())->method('get')->willReturn($expected);
        $configData = new \Magento\Directory\Model\Country\Postcode\Config($this->dataStorageMock);
        $this->assertEquals($expected, $configData->getPostCodes());
    }
}
