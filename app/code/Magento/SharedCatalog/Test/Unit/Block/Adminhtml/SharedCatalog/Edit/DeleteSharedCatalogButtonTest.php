<?php

namespace Magento\SharedCatalog\Test\Unit\Block\Adminhtml\SharedCatalog\Edit;

use Magento\SharedCatalog\Block\Adminhtml\SharedCatalog\Edit\DeleteSharedCatalogButton;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for Block Adminhtml\SharedCatalog\Edit\DeleteSharedCatalogButton.
 */
class DeleteSharedCatalogButtonTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var DeleteSharedCatalogButton
     */
    private $deleteSharedCatalogButton;

    /**
     * @var \Magento\Framework\UrlInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $urlBuilder;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $request;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    private $objectManager;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->request = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->urlBuilder = $this->getMockBuilder(\Magento\Framework\UrlInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->objectManager = new ObjectManager($this);
        $this->deleteSharedCatalogButton = $this->objectManager->getObject(
            DeleteSharedCatalogButton::class,
            [
                '_request' => $this->request,
                'urlBuilder' => $this->urlBuilder
            ]
        );
    }

    /**
     * Test for getButtonData().
     *
     * @return void
     */
    public function testGetButtonData()
    {
        $deleteUrl = 'url/delete';
        $onClickFunction = 'deleteConfirm(\'' .
            __('This action cannot be undone. Are you sure you want to delete this catalog?') .
            '\', \'' . $deleteUrl . '\')';
        $expected = [
            'label' => __('Delete'),
            'class' => 'delete',
            'id' => 'shared-catalog-edit-delete-button',
            'on_click' => $onClickFunction,
            'sort_order' => 50,
        ];
        $this->request->expects($this->once())->method('getActionName')->willReturn('edit');
        $this->prepareGetDeleteUrlMethod($deleteUrl);

        $this->assertEquals($expected, $this->deleteSharedCatalogButton->getButtonData());
    }

    /**
     * Prepare getDeleteUrl().
     *
     * @param string $deleteUrl
     * @return void
     */
    private function prepareGetDeleteUrlMethod($deleteUrl)
    {
        $sharedCatalogParam = \Magento\SharedCatalog\Api\Data\SharedCatalogInterface::SHARED_CATALOG_ID_URL_PARAM;
        $sharedCatalogId = 4676;
        $this->request->expects($this->once())->method('getParam')->with($sharedCatalogParam)
            ->willReturn($sharedCatalogId);

        $route = '*/*/delete';
        $routeParams = [$sharedCatalogParam => $sharedCatalogId];
        $this->urlBuilder->expects($this->once())->method('getUrl')->with($route, $routeParams)
            ->willReturn($deleteUrl);
    }

    /**
     * Test for getDeleteUrl().
     *
     * @return void
     */
    public function testGetDeleteUrl()
    {
        $duplicateUrl = 'url/delete';
        $this->prepareGetDeleteUrlMethod($duplicateUrl);

        $this->assertEquals($duplicateUrl, $this->deleteSharedCatalogButton->getDeleteUrl());
    }
}
