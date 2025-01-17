<?php
namespace Magento\Customer\Test\Unit\Block;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class SectionConfigTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\Customer\Block\block */
    protected $block;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var \Magento\Framework\View\Element\Template\Context|\PHPUnit_Framework_MockObject_MockObject */
    protected $context;

    /** @var \Magento\Framework\Config\DataInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $sectionConfig;

    /** @var \Magento\Framework\Json\EncoderInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $encoder;

    protected function setUp()
    {
        $this->context = $this->createMock(\Magento\Framework\View\Element\Template\Context::class);
        $this->sectionConfig = $this->createMock(\Magento\Framework\Config\DataInterface::class);
        $this->encoder = $this->createMock(\Magento\Framework\Json\EncoderInterface::class);

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->block = $this->objectManagerHelper->getObject(
            \Magento\Customer\Block\SectionConfig::class,
            [
                'context' => $this->context,
                'sectionConfig' => $this->sectionConfig
            ]
        );
    }

    public function testGetSections()
    {
        $this->sectionConfig->expects($this->once())->method('get')->with('sections')->willReturn(['data']);

        $this->assertEquals(['data'], $this->block->getSections());
    }
}
