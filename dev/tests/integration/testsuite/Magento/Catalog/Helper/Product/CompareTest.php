<?php
namespace Magento\Catalog\Helper\Product;

class CompareTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Catalog\Helper\Product\Compare
     */
    protected $_helper;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    protected function setUp()
    {
        $this->_objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->_helper = $this->_objectManager->get(\Magento\Catalog\Helper\Product\Compare::class);
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/multiple_products.php
     * @magentoDbIsolation disabled
     */
    public function testGetListUrl()
    {
        /** @var $empty \Magento\Catalog\Helper\Product\Compare */
        $empty = $this->_objectManager->create(\Magento\Catalog\Helper\Product\Compare::class);
        $this->assertContains('/catalog/product_compare/index/', $empty->getListUrl());

        $this->_populateCompareList();
        $productRepository = $this->_objectManager->create(\Magento\Catalog\Api\ProductRepositoryInterface::class);
        $id1 = $productRepository->get('simple1')->getId();
        $id2 = $productRepository->get('simple2')->getId();
        $this->assertRegExp(
            '#/catalog/product_compare/index/items/(?:' . $id1 . '%2C' . $id2 . '|' . $id2 . '%2C' . $id1. ')/#',
            $this->_helper->getListUrl()
        );
    }

    public function testGetAddUrl()
    {
        $this->_testGetProductUrl('getAddUrl', '/catalog/product_compare/add/');
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testGetAddToWishlistParams()
    {
        $product = $this->_objectManager->create(\Magento\Catalog\Model\Product::class);
        $product->setId(10);
        $json = $this->_helper->getAddToWishlistParams($product);
        $params = (array)json_decode($json);
        $data = (array)$params['data'];
        $this->assertEquals('10', $data['product']);
        $this->assertArrayHasKey('uenc', $data);
        $this->assertStringEndsWith(
            'wishlist/index/add/',
            $params['action']
        );
    }

    public function testGetAddToCartUrl()
    {
        $this->_testGetProductUrl('getAddToCartUrl', '/checkout/cart/add/');
    }

    public function testGetRemoveUrl()
    {
        $url = $this->_helper->getRemoveUrl();
        $this->assertContains('/catalog/product_compare/remove/', $url);
    }

    public function testGetClearListUrl()
    {
        $this->assertContains('\/catalog\/product_compare\/clear\/', $this->_helper->getPostDataClearList());
    }

    /**
     * @see testGetListUrl() for coverage of customer case
     */
    public function testGetItemCollection()
    {
        $this->assertInstanceOf(
            \Magento\Catalog\Model\ResourceModel\Product\Compare\Item\Collection::class,
            $this->_helper->getItemCollection()
        );
    }

    /**
     * calculate()
     * getItemCount()
     * hasItems()
     * @magentoDataFixture Magento/Catalog/_files/multiple_products.php
     * @magentoDbIsolation disabled
     */
    public function testCalculate()
    {
        /** @var \Magento\Catalog\Model\Session $session */
        $session = $this->_objectManager->get(\Magento\Catalog\Model\Session::class);
        try {
            $session->unsCatalogCompareItemsCount();
            $this->assertFalse($this->_helper->hasItems());
            $this->assertEquals(0, $session->getCatalogCompareItemsCount());

            $this->_populateCompareList();
            $this->_helper->calculate();
            $this->assertEquals(2, $session->getCatalogCompareItemsCount());
            $this->assertTrue($this->_helper->hasItems());

            $session->unsCatalogCompareItemsCount();
        } catch (\Exception $e) {
            $session->unsCatalogCompareItemsCount();
            throw $e;
        }
    }

    public function testSetGetAllowUsedFlat()
    {
        $this->assertTrue($this->_helper->getAllowUsedFlat());
        $this->_helper->setAllowUsedFlat(false);
        $this->assertFalse($this->_helper->getAllowUsedFlat());
    }

    protected function _testGetProductUrl($method, $expectedFullAction)
    {
        $product = $this->_objectManager->create(\Magento\Catalog\Model\Product::class);
        $product->setId(10);
        $url = $this->_helper->{$method}($product);
        $this->assertContains($expectedFullAction, $url);
    }

    /**
     * Add products from fixture to compare list
     */
    protected function _populateCompareList()
    {
        $productRepository = $this->_objectManager->create(\Magento\Catalog\Api\ProductRepositoryInterface::class);
        $productOne = $productRepository->get('simple1');
        $productTwo = $productRepository->get('simple2');
        /** @var $compareList \Magento\Catalog\Model\Product\Compare\ListCompare */
        $compareList = $this->_objectManager->create(\Magento\Catalog\Model\Product\Compare\ListCompare::class);
        $compareList->addProduct($productOne)->addProduct($productTwo);
    }
}
