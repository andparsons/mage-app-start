<?php

namespace Magento\SharedCatalog\Test\Unit\Block\Adminhtml\SharedCatalog\Edit;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Test for Block Adminhtml\SharedCatalog\Edit\DuplicateSharedCatalogButtonTest.
 */
class DuplicateSharedCatalogButtonTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var \Magento\SharedCatalog\Block\Adminhtml\SharedCatalog\Edit\DuplicateSharedCatalogButton
     * |\PHPUnit_Framework_MockObject_MockObject
     */
    private $duplicateSharedCatalogButton;

    /**
     * @var \Magento\Backend\Block\Widget\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    private $context;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $request;

    /**
     * @var \Magento\Framework\UrlInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $urlBuilder;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->request = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->setMethods(['getParam', 'getActionName'])
            ->disableOriginalConstructor()->getMockForAbstractClass();
        $action = 'edit';
        $this->request->expects($this->exactly(1))->method('getActionName')->willReturn($action);

        $this->context = $this->getMockBuilder(\Magento\Backend\Block\Widget\Context::class)
            ->setMethods(['getRequest'])
            ->disableOriginalConstructor()->getMock();
        $this->context->expects($this->exactly(1))->method('getRequest')->willReturn($this->request);

        $this->urlBuilder = $this->getMockBuilder(\Magento\Framework\UrlInterface::class)
            ->setMethods(['getUrl'])
            ->disableOriginalConstructor()->getMockForAbstractClass();

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->duplicateSharedCatalogButton = $this->objectManagerHelper->getObject(
            \Magento\SharedCatalog\Block\Adminhtml\SharedCatalog\Edit\DuplicateSharedCatalogButton::class,
            [
                'context' => $this->context,
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
        $duplicateUrl = 'url/duplicate';
        $expected = [
            'label' => __('Duplicate'),
            'class' => 'duplicate',
            'data_attribute' => [
                'mage-init' => [
                    'redirectionUrl' => ['url' => $duplicateUrl],
                ]
            ],
            'sort_order' => 50,
        ];

        $this->prepareGetDuplicateUrlMethod($duplicateUrl);

        $result = $this->duplicateSharedCatalogButton->getButtonData();
        $this->assertEquals($expected, $result);
    }

    /**
     * Prepare getDuplicateUrl().
     *
     * @param $duplicateUrl
     */
    private function prepareGetDuplicateUrlMethod($duplicateUrl)
    {
        $sharedCatalogParam = \Magento\SharedCatalog\Api\Data\SharedCatalogInterface::SHARED_CATALOG_ID_URL_PARAM;
        $sharedCatalogId = 4676;
        $this->request->expects($this->exactly(1))->method('getParam')->with($sharedCatalogParam)
            ->willReturn($sharedCatalogId);

        $route = '*/*/duplicate';
        $routeParams = [$sharedCatalogParam => $sharedCatalogId];
        $this->urlBuilder->expects($this->exactly(1))->method('getUrl')->with($route, $routeParams)
            ->willReturn($duplicateUrl);
    }
}
