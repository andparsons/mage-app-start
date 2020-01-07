<?php

namespace Magento\Setup\Test\Unit\Controller;

use \Magento\Setup\Controller\CreateBackup;

class CreateBackupTest extends \PHPUnit\Framework\TestCase
{
    public function testIndexAction()
    {
        /** @var $controller CreateBackup */
        $controller = new CreateBackup();
        $viewModel = $controller->indexAction();
        $this->assertInstanceOf(\Zend\View\Model\ViewModel::class, $viewModel);
        $this->assertTrue($viewModel->terminate());
    }
}
