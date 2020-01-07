<?php
namespace Magento\NegotiableQuote\Test\Unit\Model;

use Magento\NegotiableQuote\Model\Config;
use Magento\Framework\App\Config\ConfigResource\ConfigInterface as ConfigResource;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Class ConfigTest
 */
class ConfigTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var ConfigResource|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configResource;

    /**
     * @var ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $scopeConfig;

    /**
     * @var string
     */
    private $string = 'Thisisateststringwithoutspaces';

    /**
     * Set up
     *
     * @return void
     */
    protected function setUp()
    {
        $this->configResource = $this->createMock(
            ConfigResource::class
        );
        $this->scopeConfig = $this->getMockForAbstractClass(
            ScopeConfigInterface::class,
            [],
            '',
            false
        );

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->config = $objectManager->getObject(
            Config::class,
            [
                'configResource' => $this->configResource,
                'scopeConfig' => $this->scopeConfig,
            ]
        );
    }

    /**
     * Test for method isActive.
     *
     * @return void
     */
    public function testIsActive()
    {
        $this->scopeConfig->expects($this->once())->method('isSetFlag')->willReturn(true);

        $this->assertTrue($this->config->isActive());
    }

    /**
     * Test for method setIsActive.
     *
     * @return void
     */
    public function testSetIsActive()
    {
        $this->configResource->expects($this->once())->method('saveConfig')->willReturnSelf();

        $this->config->setIsActive(true);
    }

    /**
     * Test for method getAllowedExtensions.
     *
     * @return void
     */
    public function testGetAllowedExtensions()
    {
        $this->scopeConfig->expects($this->once())->method('getValue')->willReturn($this->string);

        $this->assertEquals($this->string, $this->config->getAllowedExtensions());
    }

    /**
     * Test for method getMaxFileSize.
     *
     * @return void
     */
    public function testGetMaxFileSize()
    {
        $this->scopeConfig->expects($this->once())->method('getValue')->willReturn($this->string);

        $this->assertEquals($this->string, $this->config->getMaxFileSize());
    }
}
