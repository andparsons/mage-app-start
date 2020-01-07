<?php
namespace Magento\QuickOrder\Test\Unit\Plugin\CatalogSearch\Model\ResourceModel;

use Magento\Catalog\Model\Product\Visibility;
use Magento\QuickOrder\Plugin\CatalogSearch\Model\ResourceModel\EngineInterfacePlugin;

/**
 * Unit tests for EngineInterfacePlugin.
 */
class EngineInterfacePluginTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    private $objectManagerHelper;

    /**
     * @var EngineInterfacePlugin
     */
    private $engineInterfacePlugin;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->engineInterfacePlugin = $this->objectManagerHelper->getObject(EngineInterfacePlugin::class);
    }

    /**
     * @param array $result
     * @param array $expected
     * @dataProvider visibilityFixtureDataProvider
     * @see EngineInterfacePlugin::afterGetAllowedVisibility
     * @return void
     */
    public function testAfterGetAllowedVisibility($result, $expected)
    {
        $subject = $this->getMockBuilder(\Magento\CatalogSearch\Model\ResourceModel\EngineInterface::class)
            ->getMock();

        $this->assertSame($expected, $this->engineInterfacePlugin->afterGetAllowedVisibility($subject, $result));
    }

    /**
     * Data provider for testAfterGetAllowedVisibility.
     *
     * @return array
     */
    public function visibilityFixtureDataProvider()
    {
        return [
            [[Visibility::VISIBILITY_NOT_VISIBLE], [Visibility::VISIBILITY_NOT_VISIBLE]],
            [[], [Visibility::VISIBILITY_NOT_VISIBLE]]
        ];
    }
}
