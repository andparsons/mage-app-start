<?php
namespace Magento\SharedCatalog\Test\Unit\Ui\Component\Listing;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Unit tests for columns UI component.
 */
class ColumnsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    private $objectManagerHelper;

    /**
     * @var \Magento\SharedCatalog\Ui\Component\Listing\Columns
     */
    private $columns;

    /**
     * @var \Magento\Framework\View\Element\UiComponent\ContextInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $contextMock;

    /**
     * @var \Magento\SharedCatalog\Ui\Component\Listing\ColumnFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $columnFactoryMock;

    /**
     * @var \Magento\Catalog\Ui\Component\Listing\Attribute\RepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $attributeRepositoryMock;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->contextMock = $this->getMockBuilder(\Magento\Framework\View\Element\UiComponent\ContextInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->columnFactoryMock = $this->getMockBuilder(
            \Magento\SharedCatalog\Ui\Component\Listing\ColumnFactory::class
        )
            ->disableOriginalConstructor()
            ->getMock();
        $this->attributeRepositoryMock = $this->getMockBuilder(
            \Magento\Catalog\Ui\Component\Listing\Attribute\RepositoryInterface::class
        )
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->columns = $this->objectManagerHelper->getObject(
            \Magento\SharedCatalog\Ui\Component\Listing\Columns::class,
            [
                'context' => $this->contextMock,
                'columnFactory' => $this->columnFactoryMock,
                'attributeRepository' => $this->attributeRepositoryMock
            ]
        );
    }

    /**
     * Test for prepare() method.
     *
     * @return void
     */
    public function testPrepare()
    {
        $processorMock = $this->getMockBuilder(\Magento\Framework\View\Element\UiComponent\Processor::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->contextMock->expects($this->atLeastOnce())->method('getProcessor')->willReturn($processorMock);
        $attributeMock = $this->getMockBuilder(\Magento\Catalog\Api\Data\ProductAttributeInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $attributeMock->expects($this->any())->method('getAttributeCode')->willReturn('testAttributeCode');
        $attributeMock->expects($this->once())->method('getIsFilterableInGrid')->willReturn(true);
        $this->attributeRepositoryMock->expects($this->once())->method('getList')->willReturn([$attributeMock]);
        $columnMock = $this->getMockBuilder(\Magento\Ui\Component\Listing\Columns\ColumnInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $columnMock->expects($this->once())->method('prepare');
        $this->columnFactoryMock->expects($this->once())->method('create')->willReturn($columnMock);

        $this->columns->prepare();
    }
}
