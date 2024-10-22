<?php

namespace Magento\TargetRule\Test\Unit\Block\Adminhtml\Rule;

use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\TargetRule\Block\Adminhtml\Rule\Conditions;
use Magento\Rule\Model\Condition\Combine;
use Magento\TargetRule\Model\Rule;

class ConditionsTest extends \PHPUnit\Framework\TestCase
{
    /** @var Conditions */
    private $conditions;

    /** @var \PHPUnit_Framework_MockObject_MockObject AbstractElement */
    private $abstractElementMock;

    protected function setUp()
    {
        $this->conditions = new Conditions();
        $this->abstractElementMock = $this->getMockForAbstractClass(
            AbstractElement::class,
            [],
            '',
            false,
            false,
            true,
            ['getRule']
        );
    }

    /**
     * @covers \Magento\TargetRule\Block\Adminhtml\Rule\Conditions::render
     * returns conditions->asHtmlRecursive
     */
    public function testRenderReturnAsHtmlRecursive()
    {
        $htmlRecursive = 'HtmlRecursive';
        /* @var \PHPUnit_Framework_MockObject_MockObject $actionsMock */
        $conditionsMock = $this->createMock(Combine::class);
        /* @var \PHPUnit_Framework_MockObject_MockObject $ruleMock */
        $ruleMock = $this->createPartialMock(Rule::class, ['getConditions']);
        $this->abstractElementMock->expects($this->any())->method('getRule')->willReturn($ruleMock);
        $ruleMock->expects($this->any())->method('getConditions')->willReturn($conditionsMock);
        $conditionsMock->expects($this->any())->method('asHtmlRecursive')->willReturn($htmlRecursive);
        $this->assertEquals($htmlRecursive, $this->conditions->render($this->abstractElementMock));
    }

    /**
     * @covers \Magento\TargetRule\Block\Adminhtml\Rule\Conditions::render
     * returns empty when AbstractElement->getRule is null
     */
    public function testRenderReturnsEmptyWhenGetRuleIsNull()
    {
        $this->abstractElementMock->expects($this->any())->method('getRule')->willReturn(null);
        $this->assertEmpty($this->conditions->render($this->abstractElementMock));
    }

    /**
     * @covers \Magento\TargetRule\Block\Adminhtml\Rule\Conditions::render
     * returns empty when Rule->getConditions is null
     */
    public function testRenderReturnsEmptyWhenGetConditionsIsNull()
    {
        $ruleMock = $this->createPartialMock(Rule::class, ['getConditions']);
        $this->abstractElementMock->expects($this->any())->method('getRule')->willReturn($ruleMock);
        $ruleMock->expects($this->any())->method('getConditions')->willReturn(null);
        $this->assertEmpty($this->conditions->render($this->abstractElementMock));
    }
}
