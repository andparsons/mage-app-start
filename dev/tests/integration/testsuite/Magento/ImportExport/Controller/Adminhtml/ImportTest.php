<?php
namespace Magento\ImportExport\Controller\Adminhtml;

use Magento\Framework\Filesystem\DirectoryList;

/**
 * @magentoAppArea adminhtml
 */
class ImportTest extends \Magento\TestFramework\TestCase\AbstractBackendController
{
    public function testGetFilterAction()
    {
        $this->dispatch('backend/admin/import/index');
        $body = $this->getResponse()->getBody();
        $this->assertContains(
            (string)\Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
                \Magento\ImportExport\Helper\Data::class
            )->getMaxUploadSizeMessage(),
            $body
        );
    }
}
