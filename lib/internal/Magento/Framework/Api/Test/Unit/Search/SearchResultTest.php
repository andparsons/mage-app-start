<?php
namespace Magento\Framework\Api\Test\Unit\Search;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Api\Search\SearchResult;
use Magento\Framework\Api\Search\DocumentInterface;

class SearchResultTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var SearchResult
     */
    private $search;

    /**
     * @var DocumentInterface[]
     */
    private $items;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * Set up
     */
    protected function setUp()
    {
        $document1 = $this->createMock(DocumentInterface::class);
        $document2 = $this->createMock(DocumentInterface::class);

        $this->items = [ $document1,  $document2];
        $document1->expects($this->any())
            ->method('getId')
            ->willReturn(1);
        $document2->expects($this->any())
            ->method('getId')
            ->willReturn(2);

        $data = [
            'items' => $this->items
        ];
        $this->objectManager = new ObjectManager($this);
        $this->search = $this->objectManager->getObject(
            SearchResult::class,
            [
                'data' => $data
            ]
        );
    }

    /**
     * Test getItems
     */
    public function testGetItems()
    {
        $this->assertEquals($this->items, $this->search->getItems());
    }
}
