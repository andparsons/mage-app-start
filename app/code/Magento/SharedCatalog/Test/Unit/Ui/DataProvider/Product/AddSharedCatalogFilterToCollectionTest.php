<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SharedCatalog\Test\Unit\Ui\DataProvider\Product;

/**
 * Unit test for Magento\SharedCatalog\Ui\DataProvider\Product\AddSharedCatalogFilterToCollection class.
 */
class AddSharedCatalogFilterToCollectionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\SharedCatalog\Ui\DataProvider\Product\AddSharedCatalogFilterToCollection
     */
    private $dataProvider;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->dataProvider = $objectManager->getObject(
            \Magento\SharedCatalog\Ui\DataProvider\Product\AddSharedCatalogFilterToCollection::class
        );
    }

    /**
     * Test addFilter method.
     *
     * @return void
     */
    public function testAddFilter()
    {
        $field = 'entity_id';
        $condition = ['in' => [4, 9]];
        $collection = $this->getMockBuilder(\Magento\Framework\Data\Collection::class)
            ->disableOriginalConstructor()
            ->setMethods(['getSelect', 'getTable', 'distinct'])
            ->getMock();
        $select = $this->getMockBuilder(\Magento\Framework\DB\Select::class)
            ->setMethods(['joinInner', 'where'])
            ->disableOriginalConstructor()->getMock();
        $collection->expects($this->atLeastOnce())->method('getSelect')->willReturn($select);
        $collection->expects($this->atLeastOnce())
            ->method('getTable')
            ->withConsecutive(['shared_catalog_product_item'], ['shared_catalog'])
            ->willReturnOnConsecutiveCalls('shared_catalog_product_item', 'shared_catalog');
        $select->expects($this->atLeastOnce())
            ->method('joinInner')
            ->withConsecutive(
                [
                    ['scpi' => 'shared_catalog_product_item'],
                    'scpi.sku=e.sku',
                    []
                ],
                [
                    ['sc' => 'shared_catalog'],
                    'sc.customer_group_id=scpi.customer_group_id',
                    []
                ]
            )
            ->willReturnSelf();
        $select->expects($this->once())
            ->method('where')
            ->with('sc.entity_id IN (?)', $condition['in'])
            ->willReturnSelf();
        $collection->expects($this->once())->method('distinct')->with(true)->willReturnSelf();
        $this->dataProvider->addFilter($collection, $field, $condition);
    }
}
