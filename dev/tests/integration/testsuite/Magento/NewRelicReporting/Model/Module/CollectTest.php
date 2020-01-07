<?php
namespace Magento\NewRelicReporting\Model\Module;

use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/***
 * Class CollectTest
 */
class CollectTest extends TestCase
{
    /**
     * @var Collect
     */
    private $collect;

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        $this->collect = Bootstrap::getObjectManager()->create(Collect::class);
    }

    /**
     * @return void
     */
    public function testReport()
    {
        $this->collect->getModuleData();
        $moduleData = $this->collect->getModuleData();
        $this->assertEmpty($moduleData['changes']);
    }
}
