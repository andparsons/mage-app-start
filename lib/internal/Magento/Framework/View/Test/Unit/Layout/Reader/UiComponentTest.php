<?php

/**
 * Test class for \Magento\Framework\View\Layout\Reader\UiComponent
 */
namespace Magento\Framework\View\Test\Unit\Layout\Reader;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Layout\AclCondition;
use Magento\Framework\View\Layout\ConfigCondition;
use Magento\Framework\View\Layout\Reader\Context;
use Magento\Framework\View\Layout\Reader\UiComponent;
use Magento\Framework\View\Layout\Reader\Visibility\Condition;
use Magento\Framework\View\Layout\ScheduledStructure\Helper;
use Magento\Framework\Config\DataInterfaceFactory;
use Magento\Framework\Config\DataInterface;
use Magento\Framework\View\Layout\Element;
use Magento\Framework\View\Layout\ReaderPool;
use Magento\Framework\View\Layout\ScheduledStructure;

class UiComponentTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\View\Layout\Reader\UiComponent
     */
    protected $model;

    /**
     * @var Helper|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $helper;

    /**
     * @var DataInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dataConfigFactory;

    /**
     * @var DataInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dataConfig;

    /**
     * @var ReaderPool|\PHPUnit_Framework_MockObject_MockObject
     */
    private $readerPool;

    /**
     * @var Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $context;

    protected function setUp()
    {
        $this->helper = $this->getMockBuilder(Helper::class)
            ->setMethods(['scheduleStructure'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->context = $this->getMockBuilder(Context::class)
            ->setMethods(['getScheduledStructure', 'setElementToIfconfigList'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->dataConfigFactory = $this->getMockBuilder(DataInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->dataConfig = $this->getMockBuilder(DataInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->readerPool = $this->getMockBuilder(ReaderPool::class)
            ->disableOriginalConstructor()
            ->getMock();
        $objectManager = new ObjectManager($this);
        $condition = $objectManager->getObject(Condition::class);
        $this->model = new UiComponent($this->helper, $condition, $this->dataConfigFactory, $this->readerPool);
    }

    public function testGetSupportedNodes()
    {
        $data[] = UiComponent::TYPE_UI_COMPONENT;
        $this->assertEquals($data, $this->model->getSupportedNodes());
    }

    /**
     * @param Element $element
     *
     * @dataProvider interpretDataProvider
     */
    public function testInterpret($element)
    {
        $scheduleStructure = $this->getMockBuilder(ScheduledStructure::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->context->expects($this->any())->method('getScheduledStructure')->will(
            $this->returnValue($scheduleStructure)
        );
        $this->helper->expects($this->any())->method('scheduleStructure')->with(
            $scheduleStructure,
            $element,
            $element->getParent()
        )->willReturn($element->getAttribute('name'));

        $scheduleStructure->expects($this->once())->method('setStructureElementData')->with(
            $element->getAttribute('name'),
            [
                'attributes' => [
                    'group' => '',
                    'component' => 'listing',
                    'aclResource' => 'test_acl',
                    'visibilityConditions' => [
                        'ifconfig' => [
                            'name' => ConfigCondition::class,
                            'arguments' => [
                                'configPath' => 'config_path'
                            ],
                        ],
                        'acl' => [
                            'name' => AclCondition::class,
                            'arguments' => [
                                'acl' => 'test_acl'
                            ],
                        ],
                    ],
                ],
            ]
        );
        $this->dataConfigFactory->expects($this->once())
            ->method('create')
            ->with(['componentName' => $element->getAttribute('name')])
            ->willReturn($this->dataConfig);
        $xml = '<?xml version="1.0"?>'
            . '<layout xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">'
            . '<block/>'
            . '</layout>';
        $this->dataConfig->expects($this->once())
            ->method('get')
            ->with($element->getAttribute('name'))
            ->willReturn([
                'children' => [
                    'testComponent' => [
                        'arguments' => [
                            'block' => [
                                'layout' => $xml
                            ]
                        ]
                    ]
                ]
            ]);

        $this->readerPool->expects($this->once())
            ->method('interpret')
            ->with($this->context, $this->isInstanceOf(Element::class));

        $this->model->interpret($this->context, $element);
    }

    /**
     * @return array
     */
    public function interpretDataProvider()
    {
        return [
            [
                $this->getElement(
                    '<uiComponent
                        name="cms_block_listing"
                        aclResource="test_acl"
                        component="listing"
                        ifconfig="config_path"
                    ><visibilityCondition name="test_name" className="name"></visibilityCondition></uiComponent>',
                    'uiComponent'
                ),
            ]
        ];
    }

    /**
     * @param string $xml
     * @param string $elementType
     * @return Element
     */
    protected function getElement($xml, $elementType)
    {
        $xml = simplexml_load_string(
            '<parent_element>' . $xml . '</parent_element>',
            Element::class
        );
        return $xml->{$elementType};
    }
}
