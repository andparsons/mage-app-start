<?php

namespace Magento\Setup\Test\Unit\Model;

use Magento\Setup\Model\ThemeDependencyCheckerFactory;

class ThemeDependencyCheckerFactoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ThemeDependencyCheckerFactory
     */
    private $themeDependencyCheckerFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Setup\Model\ObjectManagerProvider
     */
    private $objectManagerProvider;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    public function setUp()
    {
        $this->objectManagerProvider = $this->createMock(\Magento\Setup\Model\ObjectManagerProvider::class);
        $this->objectManager = $this->getMockForAbstractClass(
            \Magento\Framework\ObjectManagerInterface::class,
            [],
            '',
            false
        );
    }

    public function testCreate()
    {
        $this->objectManagerProvider->expects($this->once())->method('get')->willReturn($this->objectManager);
        $this->objectManager->expects($this->once())
            ->method('get')
            ->with(\Magento\Theme\Model\Theme\ThemeDependencyChecker::class);
        $this->themeDependencyCheckerFactory = new ThemeDependencyCheckerFactory($this->objectManagerProvider);
        $this->themeDependencyCheckerFactory->create();
    }
}
