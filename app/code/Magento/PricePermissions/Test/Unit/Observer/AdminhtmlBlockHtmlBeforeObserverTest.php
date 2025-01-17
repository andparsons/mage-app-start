<?php

namespace Magento\PricePermissions\Test\Unit\Observer;

class AdminhtmlBlockHtmlBeforeObserverTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\PricePermissions\Observer\AdminhtmlBlockHtmlBeforeObserver
     */
    protected $_observer;

    /**
     * @var \Magento\Framework\Event\Observer
     */
    protected $_varienObserver;

    /**
     * @var \Magento\Backend\Block\Widget\Grid\Extended
     */
    protected $_block;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_registry;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_request;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\PricePermissions\Observer\ObserverData|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $observerData;

    protected function setUp()
    {
        $this->_registry = $this->createPartialMock(\Magento\Framework\Registry::class, ['registry']);
        $this->_request = $this->createMock(\Magento\Framework\App\RequestInterface::class);
        $this->_storeManager = $this->createMock(\Magento\Store\Model\StoreManagerInterface::class);

        $this->observerData = $this->createPartialMock(
            \Magento\PricePermissions\Observer\ObserverData::class,
            ['canEditProductStatus', 'isCanEditProductPrice', 'isCanReadProductPrice', 'getDefaultProductPriceString']
        );
        $this->observerData->expects($this->any())->method('isCanEditProductPrice')->willReturn(false);
        $this->observerData->expects($this->any())->method('isCanReadProductPrice')->willReturn(false);
        $this->observerData->expects($this->any())->method('canEditProductStatus')->willReturn(false);
        $this->observerData->expects($this->any())->method('getDefaultProductPriceString')->willReturn('default');

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $constructArguments = $objectManager->getConstructArguments(
            \Magento\PricePermissions\Observer\AdminhtmlBlockHtmlBeforeObserver::class,
            [
                'coreRegistry' => $this->_registry,
                'request' => $this->_request,
                'storeManager' => $this->_storeManager,
                'observerData' => $this->observerData,
            ]
        );

        $this->_observer = $this->getMockBuilder(
            \Magento\PricePermissions\Observer\AdminhtmlBlockHtmlBeforeObserver::class
        )->setMethods(['_removeColumnFromGrid'])
            ->setConstructorArgs($constructArguments)
            ->getMock();
        $this->_block = $this->createPartialMock(
            \Magento\Backend\Block\Widget\Grid::class,
            [
                'getNameInLayout',
                'getMassactionBlock',
                'setCanReadPrice',
                'setCanEditPrice',
                'setTabData',
                'getChildBlock',
                'getParentBlock',
                'setDefaultProductPrice',
                'getForm',
                'getGroup',
            ]
        );
        $this->_varienObserver = $this->createPartialMock(
            \Magento\Framework\Event\Observer::class,
            ['getBlock', 'getEvent']
        );
        $this->_varienObserver->expects($this->any())->method('getBlock')->willReturn($this->_block);
    }

    /**
     * @param $blockName string
     * @dataProvider productGridMassactionDataProvider
     */
    public function testAdminhtmlBlockHtmlBeforeProductGridMassaction($blockName)
    {
        $this->_setGetNameInLayoutExpects($blockName);
        $this->_assertPriceColumnRemove();

        $this->_observer->execute($this->_varienObserver);
    }

    /**
     * @param $blockName string
     * @dataProvider gridCategoryProductGridDataProvider
     */
    public function testAdminhtmlBlockHtmlBeforeGridCategoryProductGrid($blockName)
    {
        $this->_setGetNameInLayoutExpects($blockName);

        $this->_assertPriceColumnRemove();
        $this->_observer->execute($this->_varienObserver);
    }

    public function testAdminhtmlBlockHtmlBeforeCustomerViewCart()
    {
        $this->_setGetNameInLayoutExpects('admin.customer.view.cart');

        $this->_observer->expects(
            $this->exactly(2)
        )->method(
            '_removeColumnFromGrid'
        )->with(
            $this->isInstanceOf(\Magento\Backend\Block\Widget\Grid::class),
            $this->logicalOr($this->equalTo('price'), $this->equalTo('total'))
        );
        $this->_observer->execute($this->_varienObserver);
    }

    /**
     * @param $blockName string
     * @dataProvider checkoutAccordionDataProvider
     */
    public function testAdminhtmlBlockHtmlBeforeCheckoutAccordion($blockName)
    {
        $this->_setGetNameInLayoutExpects($blockName);

        $this->_assertPriceColumnRemove();
        $this->_observer->execute($this->_varienObserver);
    }

    /**
     * @param $blockName string
     * @dataProvider checkoutItemsDataProvider
     */
    public function testAdminhtmlBlockHtmlBeforeItems($blockName)
    {
        $this->_setGetNameInLayoutExpects($blockName);
        $this->_block->expects($this->once())->method('setCanReadPrice')->with($this->equalTo(false));
        $this->_observer->execute($this->_varienObserver);
    }

    public function testAdminhtmlBlockHtmlBeforeDownloadableLinks()
    {
        $this->_setGetNameInLayoutExpects('catalog.product.edit.tab.downloadable.links');
        $this->_block->expects($this->once())->method('setCanReadPrice')->with($this->equalTo(false));
        $this->_block->expects($this->once())->method('setCanEditPrice')->with($this->equalTo(false));
        $this->_observer->execute($this->_varienObserver);
    }

    public function testAdminhtmlBlockHtmlBeforeSuperConfigGrid()
    {
        $this->_setGetNameInLayoutExpects('admin.product.edit.tab.super.config.grid');
        $this->_assertPriceColumnRemove();
        $this->_observer->execute($this->_varienObserver);
    }

    public function testAdminhtmlBlockHtmlBeforeProductOptions()
    {
        $this->_setGetNameInLayoutExpects('admin.product.options');

        $childBlock = $this->createPartialMock(
            \Magento\Backend\Block\Template::class,
            ['setCanEditPrice', 'setCanReadPrice']
        );
        $childBlock->expects($this->once())->method('setCanEditPrice')->with($this->equalTo(false));
        $childBlock->expects($this->once())->method('setCanReadPrice')->with($this->equalTo(false));

        $this->_block->expects(
            $this->once()
        )->method(
            'getChildBlock'
        )->with(
            $this->equalTo('options_box')
        )->will(
            $this->returnValue($childBlock)
        );

        $this->_observer->execute($this->_varienObserver);
    }

    public function testAdminhtmlBlockHtmlBeforeBundlePrice()
    {
        $this->_setGetNameInLayoutExpects('adminhtml.catalog.product.bundle.edit.tab.attributes.price');
        $this->_block->expects($this->once())->method('setCanReadPrice')->with($this->equalTo(false));
        $this->_block->expects($this->once())->method('setCanEditPrice')->with($this->equalTo(false));
        $this->_block->expects($this->once())->method('setDefaultProductPrice')->with($this->equalTo('default'));
        $this->_observer->execute($this->_varienObserver);
    }

    public function testAdminhtmlBlockHtmlBeforeBundleOpt()
    {
        $childBlock = $this->createPartialMock(
            \Magento\Backend\Block\Template::class,
            ['setCanEditPrice', 'setCanReadPrice']
        );
        $this->_setGetNameInLayoutExpects('adminhtml.catalog.product.edit.tab.bundle.option');
        $childBlock->expects($this->once())->method('setCanReadPrice')->with($this->equalTo(false));
        $childBlock->expects($this->once())->method('setCanEditPrice')->with($this->equalTo(false));
        $this->_block->expects($this->once())->method('setCanReadPrice')->with($this->equalTo(false));
        $this->_block->expects($this->once())->method('setCanEditPrice')->with($this->equalTo(false));
        $this->_block->expects($this->once())->method('getChildBlock')->willReturn($childBlock);
        $this->_observer->execute($this->_varienObserver);
    }

    public function testAdminhtmlBlockHtmlBeforeCustomerCart()
    {
        $parentBlock = $this->createPartialMock(\Magento\Backend\Block\Template::class, ['getNameInLayout']);
        $parentBlock->expects(
            $this->once()
        )->method(
            'getNameInLayout'
        )->will(
            $this->returnValue('admin.customer.carts')
        );

        $this->_setGetNameInLayoutExpects('customer_cart_');
        $this->_block->expects($this->once())->method('getParentBlock')->willReturn($parentBlock);

        $this->_observer->expects(
            $this->exactly(2)
        )->method(
            '_removeColumnFromGrid'
        )->with(
            $this->isInstanceOf(\Magento\Backend\Block\Widget\Grid::class),
            $this->logicalOr($this->equalTo('price'), $this->equalTo('total'))
        );

        $this->_observer->execute($this->_varienObserver);
    }

    protected function _assertPriceColumnRemove()
    {
        $this->_observer->expects(
            $this->once()
        )->method(
            '_removeColumnFromGrid'
        )->with(
            $this->isInstanceOf(\Magento\Backend\Block\Widget\Grid::class),
            $this->equalTo('price')
        );
    }

    protected function _setGetNameInLayoutExpects($blockName)
    {
        $this->_block->expects($this->exactly(2))->method('getNameInLayout')->willReturn($blockName);
    }

    /**
     * @return array
     */
    public function productGridMassactionDataProvider()
    {
        return [['product.grid'], ['admin.product.grid']];
    }

    /**
     * @return array
     */
    public function gridCategoryProductGridDataProvider()
    {
        return [
            ['category.product.grid']
        ];
    }

    /*
     * @return array
     */
    public function checkoutAccordionDataProvider()
    {
        return [
            ['products'],
            ['wishlist'],
            ['compared'],
            ['rcompared'],
            ['rviewed'],
            ['ordered'],
            ['checkout.accordion.products'],
            ['checkout.accordion.wishlist'],
            ['checkout.accordion.compared'],
            ['checkout.accordion.rcompared'],
            ['checkout.accordion.rviewed'],
            ['checkout.accordion.ordered']
        ];
    }

    /**
     * @return array
     */
    public function checkoutItemsDataProvider()
    {
        return [['checkout.items'], ['items']];
    }
}
