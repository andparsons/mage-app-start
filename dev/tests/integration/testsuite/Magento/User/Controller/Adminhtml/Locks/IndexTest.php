<?php

namespace Magento\User\Controller\Adminhtml\Locks;

/**
 * Locked users page test.
 *
 * @magentoAppArea adminhtml
 */
class IndexTest extends \Magento\TestFramework\TestCase\AbstractBackendController
{
    /**
     * Test index action
     *
     * @magentoDbIsolation enabled
     * @magentoDataFixture Magento/User/_files/locked_users.php
     */
    public function testIndexAction()
    {
        $this->dispatch('backend/admin/locks/index');

        $body = $this->getResponse()->getBody();
        $this->assertContains('<h1 class="page-title">Locked Users</h1>', $body);
        $this->assertRegExp(
            '/<td data-column\="username"\s*class\="[^"]*col-name[^"]*col-username[^"]*"\s*>\s*adminUser1\s*<\/td>/',
            $body
        );
        $this->assertRegExp(
            '/<td data-column\="username"\s*class\="[^"]*col-name[^"]*col-username\s*"[^"]*>\s*adminUser2\s*<\/td>/',
            $body
        );
    }
}
