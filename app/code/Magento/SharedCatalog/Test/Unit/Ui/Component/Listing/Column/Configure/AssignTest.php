<?php

namespace Magento\SharedCatalog\Test\Unit\Ui\Component\Listing\Column\Configure;

/**
 * Class AssignTest
 */
class AssignTest extends \PHPUnit\Framework\TestCase
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
     * @var \Magento\Framework\View\Element\UiComponent\Processor|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $processor;

    /**
     * @var \Magento\SharedCatalog\Model\Form\Storage\UrlBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $urlBuilder;

    /**
     * @var \Magento\SharedCatalog\Ui\Component\Listing\Column\Configure\Assign|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $assignMock;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * Set up
     */
    protected function setUp()
    {
        $this->uiComponentFactory = $this->createMock(
            \Magento\Framework\View\Element\UiComponentFactory::class
        );
        $this->urlBuilder = $this->createPartialMock(
            \Magento\SharedCatalog\Model\Form\Storage\UrlBuilder::class,
            ['getUrl']
        );
        $this->processor = $this->createPartialMock(
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
            ->willReturn($this->processor);
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
    }

    /**
     * Data provider
     *
     * @return array
     */
    public function prepareDataProvider()
    {
        return [
            [true],
            [false]
        ];
    }

    /**
     * Test prepare() method
     *
     * @param array $dataConfigAtKeySet
     * @dataProvider prepareDataProvider
     */
    public function testPrepare($dataConfigAtKeySet)
    {
        $data = [];
        if ($dataConfigAtKeySet === true) {
            $data['config']['assignClientConfig'] = [true];
            $data['config']['massAssignClientConfig'] = [true];
            $this->urlBuilder->expects($this->at(0))
                ->method('getUrl')
                ->with('shared_catalog/sharedCatalog/configure_product_assign');
            $this->urlBuilder->expects($this->at(1))
                ->method('getUrl')
                ->with('shared_catalog/sharedCatalog/configure_product_massAssign');
        }
        $this->assignMock = $this->objectManager->getObject(
            \Magento\SharedCatalog\Ui\Component\Listing\Column\Configure\Assign::class,
            [
                'context' => $this->context,
                'uiComponentFactory' => $this->uiComponentFactory,
                'urlBuilder' => $this->urlBuilder,
                'components' => [],
                'data' => $data,
            ]
        );
        $this->assignMock->prepare();
    }
}
