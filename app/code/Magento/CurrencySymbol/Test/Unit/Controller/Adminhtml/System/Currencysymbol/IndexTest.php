<?php
namespace Magento\CurrencySymbol\Test\Unit\Controller\Adminhtml\System\Currencysymbol;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Class IndexTest
 */
class IndexTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\CurrencySymbol\Controller\Adminhtml\System\Currencysymbol\Index
     */
    protected $action;

    /**
     * @var \Magento\Framework\App\ViewInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $viewMock;

    /**
     * @var \Magento\Framework\View\Layout|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $layoutMock;

    /**
     * @var \Magento\Framework\View\Element\BlockInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $blockMock;

    /**
     * @var \Magento\Backend\Model\Menu|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $menuMock;

    /**
     * @var \Magento\Backend\Model\Menu\Item|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $menuItemMock;

    /**
     * @var \Magento\Framework\View\Result\Page|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $pageMock;

    /**
     * @var \Magento\Framework\View\Page\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $pageConfigMock;

    /**
     * @var \Magento\Framework\View\Page\Title|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $titleMock;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);

        $this->menuItemMock = $this->createMock(\Magento\Backend\Model\Menu\Item::class);
        $this->menuMock = $this->createMock(\Magento\Backend\Model\Menu::class);

        $this->titleMock = $this->createMock(\Magento\Framework\View\Page\Title::class);

        $this->pageConfigMock = $this->createMock(\Magento\Framework\View\Page\Config::class);

        $this->pageMock = $this->createMock(\Magento\Framework\View\Result\Page::class);

        $this->blockMock = $this->getMockForAbstractClass(
            \Magento\Framework\View\Element\BlockInterface::class,
            [],
            '',
            false,
            false,
            true,
            ['addLink', 'setActive', 'getMenuModel']
        );

        $this->layoutMock = $this->createMock(\Magento\Framework\View\Layout::class);

        $this->viewMock = $this->createMock(\Magento\Framework\App\ViewInterface::class);

        $this->action = $objectManager->getObject(
            \Magento\CurrencySymbol\Controller\Adminhtml\System\Currencysymbol\Index::class,
            [
                'view' => $this->viewMock
            ]
        );
    }

    public function testExecute()
    {
        $this->menuMock->expects($this->once())->method('getParentItems')->willReturn([$this->menuItemMock]);
        $this->titleMock->expects($this->atLeastOnce())->method('prepend');
        $this->pageConfigMock->expects($this->atLeastOnce())->method('getTitle')->willReturn($this->titleMock);
        $this->pageMock->expects($this->atLeastOnce())->method('getConfig')->willReturn($this->pageConfigMock);
        $this->blockMock->expects($this->atLeastOnce())->method('addLink');
        $this->blockMock->expects($this->once())->method('setActive');
        $this->blockMock->expects($this->once())->method('getMenuModel')->willReturn($this->menuMock);
        $this->layoutMock->expects($this->atLeastOnce())->method('getBlock')->willReturn($this->blockMock);
        $this->viewMock->expects($this->once())->method('loadLayout')->willReturnSelf();
        $this->viewMock->expects($this->atLeastOnce())->method('getLayout')->willReturn($this->layoutMock);
        $this->viewMock->expects($this->atLeastOnce())->method('getPage')->willReturn($this->pageMock);

        $this->action->execute();
    }
}
