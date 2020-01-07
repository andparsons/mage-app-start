<?php
namespace Magento\QuickOrder\Test\Unit\Plugin\AdvancedCheckout\Block\Customer;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Unit tests for LinkPlugin plugin.
 */
class LinkPluginTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var \Magento\QuickOrder\Plugin\AdvancedCheckout\Block\Customer\LinkPlugin
     */
    private $linkPlugin;

    /**
     * @var \Magento\QuickOrder\Model\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configMock;

    /**
     * Set up.
     *
     * @return void
     */
    public function setUp()
    {
        $this->configMock = $this->getMockBuilder(\Magento\QuickOrder\Model\Config::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->linkPlugin = $this->objectManagerHelper->getObject(
            \Magento\QuickOrder\Plugin\AdvancedCheckout\Block\Customer\LinkPlugin::class,
            [
                'config' => $this->configMock
            ]
        );
    }

    /**
     * Test for aroundToHtml() method if QuickOrder is inactive.
     *
     * @return void
     */
    public function testAroundToHtmlIfConfigInactive()
    {
        $expectedResult = 'test';
        $this->configMock->expects($this->once())->method('isActive')->willReturn(false);

        $subject = $this->getMockBuilder(\Magento\AdvancedCheckout\Block\Customer\Link::class)
            ->disableOriginalConstructor()
            ->getMock();
        $proceed = function () use ($expectedResult) {
            return $expectedResult;
        };

        $this->assertEquals($expectedResult, $this->linkPlugin->aroundToHtml($subject, $proceed));
    }

    /**
     * Test for aroundToHtml() method if QuickOrder is active.
     *
     * @return void
     */
    public function testAroundToHtmlIfConfigActive()
    {
        $this->configMock->expects($this->once())->method('isActive')->willReturn(true);

        $subject = $this->getMockBuilder(\Magento\AdvancedCheckout\Block\Customer\Link::class)
            ->disableOriginalConstructor()
            ->getMock();
        $proceed = function () {
            return;
        };

        $this->assertEquals('', $this->linkPlugin->aroundToHtml($subject, $proceed));
    }
}
