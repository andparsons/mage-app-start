<?php
namespace Magento\Theme\Test\Unit\Model\Design\Config;

use Magento\Theme\Model\Design\Config\ValueProcessor;

class ValueProcessorTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\Theme\Model\Design\BackendModelFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $backendModelFactory;

    /** @var \Magento\Framework\App\Config\Value|\PHPUnit_Framework_MockObject_MockObject */
    protected $backendModel;

    /** @var ValueProcessor */
    protected $valueProcessor;

    public function setUp()
    {
        $this->backendModelFactory = $this->getMockBuilder(\Magento\Theme\Model\Design\BackendModelFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->backendModel = $this->getMockBuilder(\Magento\Framework\App\Config\Value::class)
            ->disableOriginalConstructor()
            ->setMethods(['getValue', 'afterLoad'])
            ->getMock();

        $this->valueProcessor = new ValueProcessor($this->backendModelFactory);
    }

    public function testProcess()
    {
        $path = 'design/head/logo';
        $value = 'path/to/logo';
        $scope = 'websites';
        $scopeId = 1;

        $this->backendModelFactory->expects($this->once())
            ->method('createByPath')
            ->with(
                $path,
                [
                    'value' => $value,
                    'field_config' => ['path' => $path],
                    'scope' => $scope,
                    'scope_id' => $scopeId
                ]
            )
            ->willReturn($this->backendModel);
        $this->backendModel->expects($this->once())
            ->method('afterLoad');
        $this->backendModel->expects($this->once())
            ->method('getValue')
            ->willReturn($value);
        $this->assertEquals($value, $this->valueProcessor->process($value, $scope, $scopeId, ['path' => $path]));
    }
}
