<?php
namespace Magento\Framework\App\Test\Unit\Config;

use Magento\Framework\App\Config\PreProcessorComposite;
use Magento\Framework\App\Config\Spi\PreProcessorInterface;

class PreProcessorCompositeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var PreProcessorComposite
     */
    private $model;

    /**
     * @var PreProcessorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $preProcessorMock;

    protected function setUp()
    {
        $this->preProcessorMock = $this->getMockBuilder(PreProcessorInterface::class)
            ->getMockForAbstractClass();

        $this->model = new PreProcessorComposite([$this->preProcessorMock]);
    }

    public function testProcess()
    {
        $this->preProcessorMock->expects($this->once())
            ->method('process')
            ->with(['test' => 'data'])
            ->willReturn(['test' => 'data2']);

        $this->assertSame(['test' => 'data2'], $this->model->process(['test' => 'data']));
    }
}
