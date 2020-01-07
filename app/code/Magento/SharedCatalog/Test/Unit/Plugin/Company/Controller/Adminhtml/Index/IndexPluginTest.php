<?php

namespace Magento\SharedCatalog\Test\Unit\Plugin\Company\Controller\Adminhtml\Index;

use \Magento\SharedCatalog\Plugin\Company\Controller\Adminhtml\Index\IndexPlugin;

/**
 * Class IndexPluginTest
 */
class IndexPluginTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Message\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $messageManager;

    /**
     * @var \Magento\Framework\UrlInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $urlBuilder;

    /**
     * @var \Magento\SharedCatalog\Api\SharedCatalogManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $sharedCatalogManagement;

    /**
     * @var IndexPlugin|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $indexMock;

    /**
     * Set up
     */
    protected function setUp()
    {
        $publicCatalogExists = false;
        $sharedCatalogCreateUrl = 'test url';
        $this->messageManager = $this->getMockForAbstractClass(
            \Magento\Framework\Message\ManagerInterface::class,
            [],
            '',
            false,
            false,
            true,
            ['addError']
        );
        $this->messageManager->expects($this->once())
            ->method('addError')
            ->with(
                __(
                    'Please <a href="%1">create</a> at least a public shared catalog to proceed.',
                    $sharedCatalogCreateUrl
                )
            );
        $this->urlBuilder = $this->getMockForAbstractClass(
            \Magento\Framework\UrlInterface::class,
            [],
            '',
            false,
            false,
            true,
            ['getUrl']
        );
        $this->urlBuilder->expects($this->once())
            ->method('getUrl')
            ->with('shared_catalog/sharedCatalog/create')
            ->willReturn($sharedCatalogCreateUrl);
        $this->sharedCatalogManagement = $this->getMockForAbstractClass(
            \Magento\SharedCatalog\Api\SharedCatalogManagementInterface::class,
            [],
            '',
            false,
            false,
            true,
            ['isPublicCatalogExist']
        );
        $this->sharedCatalogManagement->expects($this->once())
            ->method('isPublicCatalogExist')
            ->willReturn($publicCatalogExists);
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->indexMock = $objectManager->getObject(
            \Magento\SharedCatalog\Plugin\Company\Controller\Adminhtml\Index\IndexPlugin::class,
            [
                'messageManager' => $this->messageManager,
                'urlBuilder' => $this->urlBuilder,
                'sharedCatalogManagement' => $this->sharedCatalogManagement,
            ]
        );
    }

    /**
     * Test beforeExecute() method
     */
    public function testBeforeExecute()
    {
        $subject = $this->createMock(\Magento\Company\Controller\Adminhtml\Index\Index::class);
        $this->indexMock->beforeExecute($subject);
    }
}
