<?php
namespace Magento\GiftMessage\Test\Unit\Model;

class CompositeConfigProviderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\GiftMessage\Model\CompositeConfigProvider
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $configProviderMock;

    protected function setUp()
    {
        $this->configProviderMock = $this->createMock(\Magento\Checkout\Model\ConfigProviderInterface::class);
        $this->model = new \Magento\GiftMessage\Model\CompositeConfigProvider([$this->configProviderMock]);
    }

    public function testGetConfig()
    {
        $configMock = ['configuration' => ['option_1' => 'enabled']];
        $this->configProviderMock->expects($this->once())->method('getConfig')->willReturn($configMock);

        $this->assertSame($configMock, $this->model->getConfig());
    }
}
