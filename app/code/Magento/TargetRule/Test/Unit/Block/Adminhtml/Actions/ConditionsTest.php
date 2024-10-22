<?php

namespace Magento\TargetRule\Test\Unit\Block\Adminhtml\Actions;

use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\TargetRule\Block\Adminhtml\Actions\Conditions;
use Magento\Rule\Model\Action\Collection;
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
     * @covers \Magento\TargetRule\Block\Adminhtml\Actions\Conditions::render
     * returns Actions->asHtmlRecursive
     */
    public function testRenderReturnAsHtmlRecursive()
    {
        $htmlRecursive = 'HtmlRecursive';
        /* @var \PHPUnit_Framework_MockObject_MockObject $actionsMock */
        $actionsMock = $this->createMock(Collection::class);
        /* @var \PHPUnit_Framework_MockObject_MockObject $ruleMock */
        $ruleMock = $this->createPartialMock(Rule::class, ['getActions']);
        $this->abstractElementMock->expects($this->any())->method('getRule')->willReturn($ruleMock);
        $ruleMock->expects($this->any())->method('getActions')->willReturn($actionsMock);
        $actionsMock->expects($this->any())->method('asHtmlRecursive')->willReturn($htmlRecursive);
        $this->assertEquals($htmlRecursive, $this->conditions->render($this->abstractElementMock));
    }

    /**
     * @covers \Magento\TargetRule\Block\Adminhtml\Actions\Conditions::render
     * returns empty when AbstractElement->getRule is null
     */
    public function testRenderReturnsEmptyWhenGetRuleIsNull()
    {
        $this->abstractElementMock->expects($this->any())->method('getRule')->willReturn(null);
        $this->assertEmpty($this->conditions->render($this->abstractElementMock));
    }

    /**
     * @covers \Magento\TargetRule\Block\Adminhtml\Actions\Conditions::render
     * returns empty when Rule->getActions is null
     */
    public function testRenderReturnsEmptyWhenGetActionsIsNull()
    {
        /* @var \PHPUnit_Framework_MockObject_MockObject $ruleMock */
        $ruleMock = $this->createPartialMock(Rule::class, ['getActions']);
        $this->abstractElementMock->expects($this->any())->method('getRule')->willReturn($ruleMock);
        $ruleMock->expects($this->any())->method('getActions')->willReturn(null);
        $this->assertEmpty($this->conditions->render($this->abstractElementMock));
    }
}
