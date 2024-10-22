<?php

namespace Magento\Setup\Test\Unit\Controller;

use \Magento\Setup\Controller\Navigation;
use Magento\Setup\Model\Navigation as NavModel;
use Magento\Setup\Model\ObjectManagerProvider;

class NavigationTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Setup\Model\Navigation
     */
    private $navigationModel;

    /**
     * @var \Magento\Setup\Controller\Navigation
     */
    private $controller;

    /**
     * @var \Magento\Setup\Model\Cron\Status|\PHPUnit_Framework_MockObject_MockObject
     */
    private $status;

    /**
     * @var \Magento\Setup\Model\ObjectManagerProvider|\PHPUnit_Framework_MockObject_MockObject
     */
    private $objectManagerProvider;

    public function setUp()
    {
        $this->navigationModel = $this->createMock(\Magento\Setup\Model\Navigation::class);
        $this->status = $this->createMock(\Magento\Setup\Model\Cron\Status::class);
        $this->objectManagerProvider =
            $this->createMock(\Magento\Setup\Model\ObjectManagerProvider::class);
        $this->controller = new Navigation($this->navigationModel, $this->status, $this->objectManagerProvider);
    }

    public function testIndexAction()
    {
        $this->navigationModel->expects($this->once())->method('getData')->willReturn('some data');
        $viewModel = $this->controller->indexAction();

        $this->assertInstanceOf(\Zend\View\Model\JsonModel::class, $viewModel);
        $this->assertArrayHasKey('nav', $viewModel->getVariables());
    }

    public function testMenuActionUpdater()
    {
        $viewModel = $this->controller->menuAction();
        $this->assertInstanceOf(\Zend\View\Model\ViewModel::class, $viewModel);
        $variables = $viewModel->getVariables();
        $this->assertArrayHasKey('menu', $variables);
        $this->assertArrayHasKey('main', $variables);
        $this->assertTrue($viewModel->terminate());
        $this->assertSame('/magento/setup/navigation/menu.phtml', $viewModel->getTemplate());
    }

    public function testMenuActionInstaller()
    {
        $viewModel = $this->controller->menuAction();
        $this->assertInstanceOf(\Zend\View\Model\ViewModel::class, $viewModel);
        $variables = $viewModel->getVariables();
        $this->assertArrayHasKey('menu', $variables);
        $this->assertArrayHasKey('main', $variables);
        $this->assertTrue($viewModel->terminate());
        $this->assertSame('/magento/setup/navigation/menu.phtml', $viewModel->getTemplate());
    }

    public function testHeaderBarInstaller()
    {
        $this->navigationModel->expects($this->once())->method('getType')->willReturn(NavModel::NAV_INSTALLER);
        $viewModel = $this->controller->headerBarAction();
        $this->assertInstanceOf(\Zend\View\Model\ViewModel::class, $viewModel);
        $variables = $viewModel->getVariables();
        $this->assertArrayHasKey('menu', $variables);
        $this->assertArrayHasKey('main', $variables);
        $this->assertTrue($viewModel->terminate());
        $this->assertSame('/magento/setup/navigation/header-bar.phtml', $viewModel->getTemplate());
    }

    public function testHeaderBarUpdater()
    {
        $this->navigationModel->expects($this->once())->method('getType')->willReturn(NavModel::NAV_UPDATER);
        $viewModel = $this->controller->headerBarAction();
        $this->assertInstanceOf(\Zend\View\Model\ViewModel::class, $viewModel);
        $variables = $viewModel->getVariables();
        $this->assertArrayHasKey('menu', $variables);
        $this->assertArrayHasKey('main', $variables);
        $this->assertTrue($viewModel->terminate());
        $this->assertSame('/magento/setup/navigation/header-bar.phtml', $viewModel->getTemplate());
    }
}
