<?php
namespace Magento\Banner\Test\Unit\Block\Adminhtml\Widget\Instance\Edit\Chooser;

use Magento\Widget\Test\Unit\Block\Adminhtml\Widget\Instance\Edit\Chooser\AbstractContainerTest;

/**
 * Test for Magento\Widget\Block\Adminhtml\Widget\Instance\Edit\Chooser\Container
 *
 * Defined here because it covers functionality that should not be exposed
 */
class ContainerTest extends AbstractContainerTest
{
    /**
     * @var \Magento\Widget\Block\Adminhtml\Widget\Instance\Edit\Chooser\Container
     */
    protected $containerBlock;

    /**
     * @return void
     */
    protected function setUp()
    {
        parent::setUp();

        $this->containerBlock = $this->objectManagerHelper->getObject(
            \Magento\Widget\Block\Adminhtml\Widget\Instance\Edit\Chooser\Container::class,
            [
                'context' => $this->contextMock,
                'themesFactory' => $this->themeCollectionFactoryMock,
                'layoutProcessorFactory' => $this->layoutProcessorFactoryMock
            ]
        );
    }

    /**
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testToHtmlBannerRotatorAllPages()
    {
        $pageLayoutProcessorContainers = [
            'after.body.start' => 'Page Top',
            'columns.top' => 'Before Main Columns',
            'main' => 'Main Content Container',
            'page.bottom' => 'Before Page Footer Container',
            'before.body.end' => 'Page Bottom',
            'header.container' => 'Page Header Container',
            'page.top' => 'After Page Header',
            'footer-container' => 'Page Footer Container',
            'sidebar.main' => 'Sidebar Main',
            'sidebar.additional' => 'Sidebar Additional'
        ];
        $layoutProcessorContainers = [
            'header.panel' => 'Page Header Panel',
            'header-wrapper' => 'Page Header',
            'top.container' => 'After Page Header Top',
            'content.top' => 'Main Content Top',
            'content' => 'Main Content Area',
            'content.aside' => 'Main Content Aside',
            'content.bottom' => 'Main Content Bottom',
            'page.bottom' => 'Before Page Footer',
            'footer' => 'Page Footer',
            'cms_footer_links_container' => 'CMS Footer Links'
        ];
        $allowedContainers = [];
        $expectedHtml = '<select name="block" id="" class="required-entry select" title="" '
            . 'onchange="WidgetInstance.loadSelectBoxByType(\'block_template\', this.up(\'div.group_container\'), '
            .'this.value)"><option value="" selected="selected" >-- Please Select --</option><option value="page.top" >'
            . 'After Page Header</option><option value="top.container" >After Page Header Top</option>'
            . '<option value="columns.top" >Before Main Columns</option><option value="page.bottom" >Before Page Footer'
            . '</option><option value="cms_footer_links_container" >CMS Footer Links</option><option value="content" >'
            . 'Main Content Area</option><option value="content.aside" >Main Content Aside</option>'
            . '<option value="content.bottom" >Main Content Bottom</option><option value="main" >Main Content Container'
            . '</option><option value="content.top" >Main Content Top</option><option value="before.body.end" >'
            . 'Page Bottom</option><option value="footer" >Page Footer</option><option value="footer-container" >'
            . 'Page Footer Container</option><option value="header-wrapper" >Page Header</option>'
            . '<option value="header.container" >Page Header Container</option><option value="header.panel" >'
            . 'Page Header Panel</option><option value="after.body.start" >Page Top</option>'
            . '<option value="sidebar.additional" >Sidebar Additional</option><option value="sidebar.main" >'
            . 'Sidebar Main</option></select>';

        $this->eventManagerMock->expects($this->exactly(2))->method('dispatch')->willReturn(true);
        $this->scopeConfigMock->expects($this->once())->method('getValue')->willReturn(false);

        $this->themeCollectionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->themeCollectionMock);
        $this->themeCollectionMock->expects($this->once())->method('getItemById')->willReturn($this->themeMock);

        $this->layoutProcessorFactoryMock->expects($this->exactly(2))
            ->method('create')
            ->willReturn($this->layoutMergeMock);
        $this->layoutMergeMock->expects($this->exactly(2))->method('addPageHandles')->willReturn(true);
        $this->layoutMergeMock->expects($this->exactly(2))->method('load')->willReturnSelf();
        $this->layoutMergeMock->expects($this->any())->method('addHandle')->willReturnSelf();
        $this->layoutMergeMock->expects($this->any())->method('getContainers')->willReturnOnConsecutiveCalls(
            $pageLayoutProcessorContainers,
            $layoutProcessorContainers
        );

        $this->containerBlock->setAllowedContainers($allowedContainers);
        $this->containerBlock->setValue('');

        $this->escaperMock->expects($this->any())->method('escapeHtml')->willReturnMap(
            [
                ['', null, ''],
                ['-- Please Select --', null, '-- Please Select --'],
                ['page.top', null, 'page.top'],
                ['After Page Header', null, 'After Page Header'],
                ['top.container', null, 'top.container'],
                ['After Page Header Top', null, 'After Page Header Top'],
                ['columns.top', null, 'columns.top'],
                ['Before Main Columns', null, 'Before Main Columns'],
                ['page.bottom', null, 'page.bottom'],
                ['Before Page Footer', null, 'Before Page Footer'],
                ['cms_footer_links_container', null, 'cms_footer_links_container'],
                ['CMS Footer Links', null, 'CMS Footer Links'],
                ['content', null, 'content'],
                ['Main Content Area', null, 'Main Content Area'],
                ['content.aside', null, 'content.aside'],
                ['Main Content Aside', null, 'Main Content Aside'],
                ['content.bottom', null, 'content.bottom'],
                ['Main Content Bottom', null, 'Main Content Bottom'],
                ['main', null, 'main'],
                ['Main Content Container', null, 'Main Content Container'],
                ['content.top', null, 'content.top'],
                ['Main Content Top', null, 'Main Content Top'],
                ['before.body.end', null, 'before.body.end'],
                ['Page Bottom', null, 'Page Bottom'],
                ['footer', null, 'footer'],
                ['Page Footer', null, 'Page Footer'],
                ['footer-container', null, 'footer-container'],
                ['Page Footer Container', null, 'Page Footer Container'],
                ['header-wrapper', null, 'header-wrapper'],
                ['Page Header', null, 'Page Header'],
                ['header.container', null, 'header.container'],
                ['Page Header Container', null, 'Page Header Container'],
                ['header.panel', null, 'header.panel'],
                ['Page Header Panel', null, 'Page Header Panel'],
                ['after.body.start', null, 'after.body.start'],
                ['Page Top', null, 'Page Top'],
                ['sidebar.additional', null, 'sidebar.additional'],
                ['Sidebar Additional', null, 'Sidebar Additional'],
                ['sidebar.main', null, 'sidebar.main'],
                ['Sidebar Main', null, 'Sidebar Main']
            ]
        );

        $this->assertEquals($expectedHtml, $this->containerBlock->toHtml());
    }
}
