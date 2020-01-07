<?php
namespace Magento\SharedCatalog\Test\Unit\Block\Adminhtml\SharedCatalog\Messages;

/**
 * Test for block Magento\SharedCatalog\Test\Unit\Block\Adminhtml\SharedCatalog\Messages\Notification.
 */
class NotificationTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\SharedCatalog\Model\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $moduleConfig;

    /**
     * @var \Magento\Framework\AuthorizationInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $authorization;

    /**
     * @var \Magento\Framework\UrlInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $urlBuilder;

    /**
     * @var \Magento\SharedCatalog\Block\Adminhtml\SharedCatalog\Messages\Notification
     */
    private $notification;

    /**
     * Set up.
     *
     * @return void
     */
    public function setUp()
    {
        $this->moduleConfig = $this->getMockBuilder(\Magento\SharedCatalog\Model\Config::class)
            ->disableOriginalConstructor()->getMock();
        $this->authorization = $this->getMockBuilder(\Magento\Framework\AuthorizationInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->urlBuilder = $this->getMockBuilder(\Magento\Framework\UrlInterface::class)
            ->disableOriginalConstructor()->getMock();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->notification = $objectManager->getObject(
            \Magento\SharedCatalog\Block\Adminhtml\SharedCatalog\Messages\Notification::class,
            [
                'moduleConfig' => $this->moduleConfig,
                '_authorization' => $this->authorization,
                '_urlBuilder' => $this->urlBuilder
            ]
        );
    }

    /**
     * Test for isConfigurationAvailable method.
     *
     * @return void
     */
    public function testIsConfigurationAvailable()
    {
        $this->authorization->expects($this->once())
            ->method('isAllowed')->with('Magento_Config::config')->willReturn(true);
        $this->assertTrue($this->notification->isConfigurationAvailable());
    }

    /**
     * Test for getConfigurationUrl method.
     *
     * @return void
     */
    public function testGetConfigurationUrl()
    {
        $url = 'url value';
        $this->urlBuilder->expects($this->once())
            ->method('getUrl')->with('adminhtml/system_config/edit', ['section' => 'btob'])->willReturn($url);
        $this->assertEquals($url, $this->notification->getConfigurationUrl());
    }
}
