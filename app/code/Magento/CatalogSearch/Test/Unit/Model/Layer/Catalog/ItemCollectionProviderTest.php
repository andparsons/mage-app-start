<?php

namespace Magento\CatalogSearch\Test\Unit\Model\Layer\Catalog;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class ItemCollectionProviderTest extends \PHPUnit\Framework\TestCase
{
    public function testGetCollection()
    {
        $categoryMock = $this->createMock(\Magento\Catalog\Model\Category::class);

        $collectionMock = $this->createMock(\Magento\Catalog\Model\ResourceModel\Product\Collection::class);
        $collectionMock->expects($this->once())->method('addCategoryFilter')->with($categoryMock);

        $collectionFactoryMock = $this->createPartialMock(
            \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory::class,
            ['create']
        );
        $collectionFactoryMock->expects($this->any())->method('create')->will($this->returnValue($collectionMock));

        $objectManager = new ObjectManagerHelper($this);
        $provider = $objectManager->getObject(
            \Magento\CatalogSearch\Model\Layer\Category\ItemCollectionProvider::class,
            ['collectionFactory' => $collectionFactoryMock]
        );

        $provider->getCollection($categoryMock);
    }
}
