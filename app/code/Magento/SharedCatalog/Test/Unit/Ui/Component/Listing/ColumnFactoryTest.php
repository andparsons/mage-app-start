<?php
namespace Magento\SharedCatalog\Test\Unit\Ui\Component\Listing;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Unit tests for columns factory.
 */
class ColumnFactoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    private $objectManagerHelper;

    /**
     * @var \Magento\SharedCatalog\Ui\Component\Listing\ColumnFactory
     */
    private $columnFactory;

    /**
     * @var \Magento\Framework\View\Element\UiComponentFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $componentFactoryMock;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->componentFactoryMock = $this->getMockBuilder(\Magento\Framework\View\Element\UiComponentFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->columnFactory = $this->objectManagerHelper->getObject(
            \Magento\SharedCatalog\Ui\Component\Listing\ColumnFactory::class,
            [
                'componentFactory' => $this->componentFactoryMock
            ]
        );
    }

    /**
     * Test for create() method.
     *
     * @return void
     */
    public function testCreate()
    {
        $attributeMock = $this->getMockBuilder(\Magento\Catalog\Api\Data\ProductAttributeInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getAttributeCode',
                'getDefaultFrontendLabel',
                'getFrontendInput',
                'getIsFilterableInGrid',
                'usesSource',
                'getSource'
            ])
            ->getMockForAbstractClass();
        $attributeMock->expects($this->once())->method('getAttributeCode')->willReturn('testAttributeCode');
        $attributeMock->expects($this->once())->method('getDefaultFrontendLabel')->willReturn('Test Attribute Label');
        $attributeMock->expects($this->any())->method('getFrontendInput')->willReturn('default');
        $attributeMock->expects($this->once())->method('getIsFilterableInGrid')->willReturn(true);
        $attributeMock->expects($this->once())->method('usesSource')->willReturn(true);
        $source = $this->getMockBuilder(\Magento\Eav\Model\Entity\Attribute\Source\AbstractSource::class)
            ->disableOriginalConstructor()
            ->getMock();
        $source->expects($this->once())->method('getAllOptions')->willReturn(['options']);
        $attributeMock->expects($this->once())->method('getSource')->willReturn($source);
        $contextMock = $this->getMockBuilder(\Magento\Framework\View\Element\UiComponent\ContextInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $uiComponentMock = $this->getMockBuilder(\Magento\Framework\View\Element\UiComponentInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->componentFactoryMock->expects($this->once())->method('create')->willReturn($uiComponentMock);

        $result = $this->columnFactory->create($attributeMock, $contextMock);
        $this->assertInstanceOf(\Magento\Framework\View\Element\UiComponentInterface::class, $result);
    }
}
