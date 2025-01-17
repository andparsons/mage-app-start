<?php
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Cron;

use Magento\Catalog\Cron\DeleteAbandonedStoreFlatTables;
use Magento\Catalog\Helper\Product\Flat\Indexer;

/**
 * @covers \Magento\Catalog\Cron\DeleteAbandonedStoreFlatTables
 */
class DeleteAbandonedStoreFlatTablesTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Testable Object
     *
     * @var DeleteAbandonedStoreFlatTables
     */
    private $deleteAbandonedStoreFlatTables;

    /**
     * @var Indexer|\PHPUnit_Framework_MockObject_MockObject
     */
    private $indexerMock;

    /**
     * Set Up
     *
     * @return void
     */
    protected function setUp()
    {
        $this->indexerMock = $this->createMock(Indexer::class);
        $this->deleteAbandonedStoreFlatTables = new DeleteAbandonedStoreFlatTables($this->indexerMock);
    }

    /**
     * Test execute method
     *
     * @return void
     */
    public function testExecute()
    {
        $this->indexerMock->expects($this->once())->method('deleteAbandonedStoreFlatTables');
        $this->deleteAbandonedStoreFlatTables->execute();
    }
}
