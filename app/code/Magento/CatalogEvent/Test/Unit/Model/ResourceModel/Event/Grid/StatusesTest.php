<?php

namespace Magento\CatalogEvent\Test\Unit\Model\ResourceModel\Event\Grid;

use Magento\CatalogEvent\Model\ResourceModel\Event\Grid\Statuses;
use Magento\Framework\Phrase;

/**
 * Unit test for Magento\CatalogEvent\Model\ResourceModel\Event\Grid\Statuses
 */
class StatusesTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\CatalogEvent\Model\ResourceModel\Event\Grid\Statuses
     */
    protected $statuses;

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        $this->statuses = new Statuses();
    }

    /**
     * @return void
     */
    public function testToOptionArray()
    {
        foreach ($this->statuses->toOptionArray() as $item) {
            $this->assertTrue($item instanceof Phrase || is_string($item));
        }
    }
}
