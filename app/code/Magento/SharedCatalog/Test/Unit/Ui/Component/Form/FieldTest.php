<?php

namespace Magento\SharedCatalog\Test\Unit\Ui\Component\Form;

/**
 * Class FieldTest
 */
class FieldTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\View\Element\UiComponent\ContextInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $context;

    /**
     * @var \Magento\Framework\View\Element\UiComponentFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $uiComponentFactory;

    /**
     * @var \Magento\SharedCatalog\Ui\Component\Form\Field|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $fieldMock;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * Set up
     */
    protected function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
    }

    /**
     * Test prepare() method
     */
    public function testPrepare()
    {
        $data = [
            'config'=>
                ['formElement'=>'testElement']
        ];
        $processor = $this->createPartialMock(
            \Magento\Framework\View\Element\UiComponent\Processor::class,
            ['register', 'notify']
        );
        $this->context = $this->getMockForAbstractClass(
            \Magento\Framework\View\Element\UiComponent\ContextInterface::class,
            [],
            '',
            false,
            false,
            true,
            ['getProcessor']
        );
        $this->context->expects($this->atLeastOnce())
            ->method('getProcessor')
            ->willReturn($processor);
        $wrappedComponent = $this->getMockForAbstractClass(
            \Magento\Framework\View\Element\UiComponentInterface::class,
            [],
            '',
            false,
            false,
            true,
            ['setData', 'getContext']
        );
        $wrappedComponent->expects($this->any())
            ->method('getContext')
            ->willReturn($this->context);
        $this->uiComponentFactory = $this->createPartialMock(
            \Magento\Framework\View\Element\UiComponentFactory::class,
            ['create']
        );
        $this->uiComponentFactory->expects($this->any())
            ->method('create')
            ->willReturn($wrappedComponent);
        $this->fieldMock = $this->objectManager->getObject(
            \Magento\SharedCatalog\Ui\Component\Form\Field::class,
            [
                'context' => $this->context,
                'uiComponentFactory' => $this->uiComponentFactory,
                'components' => [],
                'data' => $data,
            ]
        );
        $this->fieldMock->prepare();
    }
}
