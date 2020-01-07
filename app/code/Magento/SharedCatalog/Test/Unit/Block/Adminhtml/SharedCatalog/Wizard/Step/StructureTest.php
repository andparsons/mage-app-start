<?php

namespace Magento\SharedCatalog\Test\Unit\Block\Adminhtml\SharedCatalog\Wizard\Step;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Test for Block Adminhtml\SharedCatalog\Wizard\Step\Structure.
 */
class StructureTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var \Magento\SharedCatalog\Block\Adminhtml\SharedCatalog\Wizard\Step\Structure
     * |\PHPUnit_Framework_MockObject_MockObject
     */
    private $structure;

    /**
     * @var \Magento\Framework\View\Element\Template\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    private $contextMock;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->contextMock = $this->getMockBuilder(\Magento\Framework\View\Element\Template\Context::class)
            ->disableOriginalConstructor()->getMock();

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->structure = $this->objectManagerHelper->getObject(
            \Magento\SharedCatalog\Block\Adminhtml\SharedCatalog\Wizard\Step\Structure::class,
            [
                'context' => $this->contextMock
            ]
        );
    }

    /**
     * Test for getCaption().
     *
     * @return void
     */
    public function testGetCaption()
    {
        $expects = __('Products');
        $this->assertEquals($expects, $this->structure->getCaption());
    }
}
