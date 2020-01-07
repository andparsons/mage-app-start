<?php

namespace Magento\SharedCatalog\Test\Unit\Block\Widget\Grid\Column\Renderer;

/**
 * Test for block CustomerGroup.
 */
class CustomerGroupTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Backend\Block\Widget\Grid\Column|\PHPUnit_Framework_MockObject_MockObject
     */
    private $column;

    /**
     * @var \Magento\Framework\Escaper|\PHPUnit_Framework_MockObject_MockObject
     */
    private $escaper;

    /**
     * @var \Magento\SharedCatalog\Block\Widget\Grid\Column\Renderer\CustomerGroup
     */
    private $customerGroup;

    /**
     * @inheritdoc
     */
    public function setUp()
    {
        $this->column = $this->getMockBuilder(\Magento\Backend\Block\Widget\Grid\Column::class)
            ->disableOriginalConstructor()
            ->setMethods(['getOptions', 'getIndex', 'getShowMissingOptionValues'])
            ->getMock();
        $this->escaper = $this->getMockBuilder(\Magento\Framework\Escaper::class)
            ->disableOriginalConstructor()->getMock();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->customerGroup = $objectManager->getObject(
            \Magento\SharedCatalog\Block\Widget\Grid\Column\Renderer\CustomerGroup::class,
            [
                'escaper' => $this->escaper
            ]
        );
        $this->customerGroup->setColumn($this->column);
    }

    /**
     * Test for render method.
     *
     * @return void
     */
    public function testRender()
    {
        $options = [
            [
                'label' => 'Group',
                'value' => [
                    [
                        'value' => 1,
                        'label' => 'Custom group'
                    ]
                ]
            ]
        ];
        $this->column->expects($this->atLeastOnce())->method('getOptions')->willReturn($options);
        $this->column->expects($this->atLeastOnce())->method('getIndex')->willReturn('group');
        $this->escaper->expects($this->atLeastOnce())->method('escapeHtml')->willReturnArgument(0);

        $row = new \Magento\Framework\DataObject(['group' => 1]);
        $this->assertEquals(
            'Custom group',
            $this->customerGroup->render($row)
        );
    }
}
