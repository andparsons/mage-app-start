<?php

namespace Magento\SharedCatalog\Test\Unit\Plugin\Source;

/**
 * Unit test for CustomerGroupReportPlugin plugin.
 */
class CustomerGroupReportPluginTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\SharedCatalog\Plugin\Source\CustomerGroupReportPlugin|\PHPUnit_Framework_MockObject_MockObject
     */
    private $sharedCatalogGroupsProcessor;

    /**
     * @var \Magento\SharedCatalog\Plugin\Source\CustomerGroupReportPlugin
     */
    private $customerGroupReportPlugin;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->sharedCatalogGroupsProcessor = $this
            ->getMockBuilder(\Magento\SharedCatalog\Plugin\Source\SharedCatalogGroupsProcessor::class)
            ->disableOriginalConstructor()->getMock();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->customerGroupReportPlugin = $objectManager->getObject(
            \Magento\SharedCatalog\Plugin\Source\CustomerGroupReportPlugin::class,
            [
                'sharedCatalogGroupsProcessor' => $this->sharedCatalogGroupsProcessor,
            ]
        );
    }

    /**
     * Test for afterToOptionArray method.
     *
     * @return void
     */
    public function testAfterToOptionArray()
    {
        $groups = [1 => 'Customer Group 1', 2 => 'Customer Group 2'];
        $source = $this->getMockBuilder(\Magento\Framework\Data\OptionSourceInterface::class)
            ->disableOriginalConstructor()->getMockForAbstractClass();
        $this->sharedCatalogGroupsProcessor->expects($this->once())->method('prepareGroups')->willReturnArgument(0);

        $this->assertEquals(
            [2 => ['label' => 'Customer Group 2', 'value' => 2], 1 => ['label' => 'Customer Group 1', 'value' => 1]],
            $this->customerGroupReportPlugin->afterToOptionArray($source, $groups)
        );
    }
}
