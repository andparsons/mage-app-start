<?php

namespace Magento\SharedCatalog\Test\Unit\Block\Adminhtml\SharedCatalog\Wizard;

use \Magento\SharedCatalog\Block\Adminhtml\SharedCatalog\Wizard\Container;

/**
 * Class ContainerTest
 */
class ContainerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Backend\Block\Template\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $context;

    /**
     * @var \Magento\Framework\View\LayoutInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $layout;

    /**
     * @var \Magento\Framework\View\Element\AbstractBlock|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $block;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * @var Container|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $containerMock;

    /**
     * Set up
     */
    protected function setUp()
    {
        $this->layout = $this->getMockForAbstractClass(
            \Magento\Framework\View\LayoutInterface::class,
            [],
            '',
            false,
            false,
            true,
            ['getChildName', 'getBlock']
        );
        $this->block = $this->getMockForAbstractClass(
            \Magento\Framework\View\Element\AbstractBlock::class,
            [],
            '',
            false,
            false,
            true,
            ['setInitData', 'toHtml']
        );
        $this->context = $this->createPartialMock(\Magento\Backend\Block\Template\Context::class, ['getLayout']);
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
    }

    /**
     * Test for getWizard() function
     *
     * @param bool $isLayout
     * @param string $expectedResult
     * @dataProvider getWizardDataProvider
     */
    public function testGetWizard($isLayout, $expectedResult)
    {
        $initData = ['test data'];
        $this->context->expects($this->once())
            ->method('getLayout')
            ->willReturn($this->layout);
        if ($isLayout == true) {
            $name = 'test name';
            $this->layout->expects($this->once())
                ->method('getChildName')
                ->willReturn($name);
            $this->layout->expects($this->once())
                ->method('getBlock')
                ->willReturn($this->block);
            $this->block->expects($this->once())
                ->method('setInitData')
                ->with($initData);
            $this->block->expects($this->once())
                ->method('toHtml')
                ->willReturn($expectedResult);
        }
        $this->containerMock = $this->objectManager->getObject(
            \Magento\SharedCatalog\Block\Adminhtml\SharedCatalog\Wizard\Container::class,
            [
                'context' => $this->context,
                'data' => [],
            ]
        );
        $actualResult = $this->containerMock->getWizard($initData);
        $this->assertEquals($expectedResult, $actualResult);
    }

    /**
     * @return array
     */
    public function getWizardDataProvider()
    {
        return [
            [
                true, 'test html'
            ],
            [
                false, ''
            ]
        ];
    }
}
