<?php

namespace Magento\CatalogRule\Test\Unit\Cron;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class DailyCatalogUpdateTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Processor
     *
     * @var \Magento\CatalogRule\Model\Indexer\Rule\RuleProductProcessor|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $ruleProductProcessor;

    /**
     * Cron object
     *
     * @var \Magento\CatalogRule\Cron\DailyCatalogUpdate
     */
    protected $cron;

    protected function setUp()
    {
        $this->ruleProductProcessor = $this->createMock(
            \Magento\CatalogRule\Model\Indexer\Rule\RuleProductProcessor::class
        );

        $this->cron = (new ObjectManager($this))->getObject(
            \Magento\CatalogRule\Cron\DailyCatalogUpdate::class,
            [
                'ruleProductProcessor' => $this->ruleProductProcessor,
            ]
        );
    }

    public function testDailyCatalogUpdate()
    {
        $this->ruleProductProcessor->expects($this->once())->method('markIndexerAsInvalid');

        $this->cron->execute();
    }
}
