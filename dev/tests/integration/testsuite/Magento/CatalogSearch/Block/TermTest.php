<?php
namespace Magento\CatalogSearch\Block;

class TermTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Search\Block\Term
     */
    protected $_block;

    protected function setUp()
    {
        $this->_block = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Framework\View\LayoutInterface::class
        )->createBlock(
            \Magento\Search\Block\Term::class
        );
    }

    public function testGetSearchUrl()
    {
        $query = uniqid();
        $obj = new \Magento\Framework\DataObject(['query_text' => $query]);
        $this->assertStringEndsWith("/catalogsearch/result/?q={$query}", $this->_block->getSearchUrl($obj));
    }
}
