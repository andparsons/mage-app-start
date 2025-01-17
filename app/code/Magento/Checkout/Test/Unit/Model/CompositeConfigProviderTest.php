<?php
namespace Magento\Checkout\Test\Unit\Model;

class CompositeConfigProviderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $configProviderMock;

    /**
     * @var \Magento\Checkout\Model\CompositeConfigProvider
     */
    protected $model;

    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->configProviderMock = $this->createMock(\Magento\Checkout\Model\ConfigProviderInterface::class);
        $this->model = $objectManager->getObject(
            \Magento\Checkout\Model\CompositeConfigProvider::class,
            ['configProviders' => [$this->configProviderMock]]
        );
    }

    public function testGetConfig()
    {
        $config = ['key' => 'value'];
        $this->configProviderMock->expects($this->once())->method('getConfig')->willReturn($config);
        $this->assertEquals($config, $this->model->getConfig());
    }
}
