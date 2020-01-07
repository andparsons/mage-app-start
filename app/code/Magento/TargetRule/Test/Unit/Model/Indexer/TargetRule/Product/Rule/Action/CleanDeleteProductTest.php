<?php
/**
 * @category    Magento
 * @package     Magento_TargetRule
 * @subpackage  unit_tests
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\TargetRule\Test\Unit\Model\Indexer\TargetRule\Product\Rule\Action;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;

/**
 * Class for test clean deleted product
 */
class CleanDeleteProductTest extends \PHPUnit\Framework\TestCase
{
    /** @var ObjectManager */
    private $objectManager;

    /**
     * @var \Magento\TargetRule\Model\Indexer\TargetRule\Product\Rule\Action\CleanDeleteProduct
     */
    protected $_model;

    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);
        $this->_model = $this->objectManager->getObject(
            \Magento\TargetRule\Model\Indexer\TargetRule\Product\Rule\Action\CleanDeleteProduct::class
        );
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage We can't rebuild the index for an undefined product.
     */
    public function testEmptyIds()
    {
        $this->_model->execute(null);
    }

    /**
     * Test clean deleted product
     */
    public function testCleanDeleteProduct()
    {
        $ruleFactoryMock = $this->createPartialMock(\Magento\TargetRule\Model\RuleFactory::class, ['create']);

        $collectionFactoryMock = $this->createPartialMock(
            \Magento\TargetRule\Model\ResourceModel\Rule\CollectionFactory::class,
            ['create']
        );

        $productCollectionFactoryMock = $this->getMockBuilder(ProductCollectionFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $resourceMock = $this->createMock(\Magento\TargetRule\Model\ResourceModel\Index::class);

        $resourceMock->expects($this->once())
            ->method('deleteProductFromIndex')
            ->will($this->returnValue(1));

        $storeManagerMock = $this->getMockForAbstractClass(\Magento\Store\Model\StoreManagerInterface::class);
        $timezoneMock = $this->getMockForAbstractClass(\Magento\Framework\Stdlib\DateTime\TimezoneInterface::class);

        $model = $this->objectManager->getObject(
            \Magento\TargetRule\Model\Indexer\TargetRule\Product\Rule\Action\CleanDeleteProduct::class,
            [
                'ruleFactory' => $ruleFactoryMock,
                'ruleCollectionFactory' => $collectionFactoryMock,
                'resource' => $resourceMock,
                'storeManager' => $storeManagerMock,
                'localeDate' => $timezoneMock,
                'productCollectionFactory' => $productCollectionFactoryMock
            ]
        );

        $model->execute(2);
    }
}
