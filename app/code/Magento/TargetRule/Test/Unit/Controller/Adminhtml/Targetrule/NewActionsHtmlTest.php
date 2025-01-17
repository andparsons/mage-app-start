<?php
namespace Magento\TargetRule\Test\Unit\Controller\Adminhtml\Targetrule;

use Magento\Framework\Authorization;
use Magento\TargetRule\Controller\Adminhtml\Targetrule\NewActionsHtml;

class NewActionsHtmlTest extends AbstractTest
{
    /**
     * @var NewActionsHtml
     */
    protected $controller;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $this->controller = new NewActionsHtml(
            $this->contextMock,
            $this->registryMock,
            $this->dateMock
        );
    }

    /**
     * @return void
     */
    public function testExecute()
    {
        $conditionMock = $this->getMockForConditionsHtmlAction(
            \Magento\Rule\Model\Condition\AbstractCondition::class,
            'actions'
        );

        $this->requestMock
            ->expects($this->any())
            ->method('getParam')
            ->willReturnMap([
                ['id', null, 123],
                ['type', null, 'foo|bar'],
                ['form', null, 'form value'],
            ]);

        $conditionMock
            ->expects($this->atLeastOnce())
            ->method('asHtmlRecursive')
            ->willReturn('Result HTML');
        $conditionMock
            ->expects($this->atLeastOnce())
            ->method('setJsFormObject')
            ->with('form value');

        $this->responseMock
            ->expects($this->once())
            ->method('setBody')
            ->with('Result HTML');

        $this->controller->execute();
    }

    /**
     * @return void
     */
    public function testExecuteInvalidType()
    {
        $conditionMock = $this->getMockForConditionsHtmlAction(\Magento\Framework\DataObject::class, 'actions');

        $this->requestMock
            ->expects($this->any())
            ->method('getParam')
            ->willReturnMap([
                ['id', null, 123],
                ['type', null, 'foo|bar'],
                ['form', null, 'form value'],
            ]);

        $conditionMock
            ->expects($this->never())
            ->method('asHtmlRecursive');
        $conditionMock
            ->expects($this->never())
            ->method('setJsFormObject');

        $this->responseMock
            ->expects($this->once())
            ->method('setBody')
            ->with('');

        $this->controller->execute();
    }
}
