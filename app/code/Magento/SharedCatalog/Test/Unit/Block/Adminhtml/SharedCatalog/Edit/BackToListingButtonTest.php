<?php

namespace Magento\SharedCatalog\Test\Unit\Block\Adminhtml\SharedCatalog\Edit;

use Magento\SharedCatalog\Block\Adminhtml\SharedCatalog\Edit\BackToListingButton;

/**
 * Class BackToListingButtonTest
 */
class BackToListingButtonTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Backend\Block\Widget\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $context;

    /**
     * @var BackToListingButton|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $backToListingButtonMock;

    /**
     * @var \Magento\Framework\UrlInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $urlBuilder;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * Set up
     */
    protected function setUp()
    {
        $this->urlBuilder = $this->getMockForAbstractClass(
            \Magento\Framework\UrlInterface::class,
            [],
            '',
            false,
            false,
            true,
            ['getUrl']
        );
        $this->context = $this->createPartialMock(\Magento\Backend\Block\Widget\Context::class, ['getUrlBuilder']);
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
    }

    public function testGetButtonData()
    {
        $route = '*/*/';
        $backUrl = 'test url';
        $expectedResult = [
            'label' => __('Back'),
            'on_click' => sprintf("location.href = '%s';", $backUrl),
            'class' => 'back',
            'sort_order' => 10
        ];
        $this->context->expects($this->once())
            ->method('getUrlBuilder')
            ->willReturn($this->urlBuilder);
        $this->urlBuilder->expects($this->once())
            ->method('getUrl')
            ->with($route)
            ->willReturn($backUrl);
        $this->backToListingButtonMock = $this->objectManager->getObject(
            \Magento\SharedCatalog\Block\Adminhtml\SharedCatalog\Edit\BackToListingButton::class,
            [
                'context' => $this->context,
            ]
        );
        $actualResult = $this->backToListingButtonMock->getButtonData();
        $this->assertEquals($expectedResult, $actualResult);
    }
}
