<?php

namespace Magento\QuickOrder\Test\Unit\Plugin\CatalogPermissions\Observer;

use \Magento\QuickOrder\Plugin\CatalogPermissions\Observer\CheckQuotePermissionsObserverPlugin;

/**
 * Class CheckQuotePermissionsObserverPluginTest
 */
class CheckQuotePermissionsObserverPluginTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    private $objectManagerHelper;

    /**
     * @var CheckQuotePermissionsObserverPlugin|\PHPUnit_Framework_MockObject_MockObject
     */
    private $checkQuotePermissionsObserverPlugin;

    /**
     * @var \Magento\QuickOrder\Model\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configMock;

    /**
     * Set up
     *
     * @return void
     */
    public function setUp()
    {
        $this->configMock = $this->getMockBuilder(\Magento\QuickOrder\Model\Config::class)
            ->disableOriginalConstructor()
            ->setMethods(['isActive'])
            ->getMock();

        $this->objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->checkQuotePermissionsObserverPlugin = $this->objectManagerHelper->getObject(
            CheckQuotePermissionsObserverPlugin::class,
            [
                'config' => $this->configMock
            ]
        );
    }

    /**
     * Test for aroundExecute() method
     *
     * @return void
     */
    public function testAroundExecute()
    {
        $this->configMock->expects($this->any())
            ->method('isActive')
            ->willReturn(true);

        $observerMock = $this->getMockBuilder(\Magento\Framework\Event\Observer::class)
            ->disableOriginalConstructor()
            ->getMock();

        $subject = $this->getMockBuilder(\Magento\CatalogPermissions\Observer\CheckQuotePermissionsObserver::class)
            ->disableOriginalConstructor()
            ->getMock();
        $proceed = function ($observer) {
            return $observer;
        };

        $this->assertInstanceOf(
            \Magento\CatalogPermissions\Observer\CheckQuotePermissionsObserver::class,
            $this->checkQuotePermissionsObserverPlugin->aroundExecute($subject, $proceed, $observerMock)
        );
    }

    /**
     * Test for aroundExecute() method when our extension is inactive
     *
     * @return void
     */
    public function testAroundExecuteInactive()
    {
        $this->configMock->expects($this->any())
            ->method('isActive')
            ->willReturn(false);

        $observerMock = $this->getMockBuilder(\Magento\Framework\Event\Observer::class)
            ->disableOriginalConstructor()
            ->getMock();

        $subject = $this->getMockBuilder(\Magento\CatalogPermissions\Observer\CheckQuotePermissionsObserver::class)
            ->disableOriginalConstructor()
            ->getMock();
        $proceed = function ($observer) {
            return $observer;
        };

        $this->assertInstanceOf(
            \Magento\Framework\Event\Observer::class,
            $this->checkQuotePermissionsObserverPlugin->aroundExecute($subject, $proceed, $observerMock)
        );
    }
}
