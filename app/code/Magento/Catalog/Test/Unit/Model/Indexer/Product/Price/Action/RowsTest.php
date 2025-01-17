<?php
namespace Magento\Catalog\Test\Unit\Model\Indexer\Product\Price\Action;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class RowsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Price\Action\Rows
     */
    protected $_model;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->_model = $objectManager->getObject(\Magento\Catalog\Model\Indexer\Product\Price\Action\Rows::class);
    }

    /**
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage Bad value was supplied.
     */
    public function testEmptyIds()
    {
        $this->_model->execute(null);
    }
}
