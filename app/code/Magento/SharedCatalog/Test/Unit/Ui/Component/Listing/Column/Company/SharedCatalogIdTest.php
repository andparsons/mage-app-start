<?php
namespace Magento\SharedCatalog\Test\Unit\Ui\Component\Listing\Column\Company;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\View\Element\UiComponent\DataProvider\DataProviderInterface;

/**
 * Unit tests for SharedCatalogId UI listing company column component.
 */
class SharedCatalogIdTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var \Magento\SharedCatalog\Ui\Component\Listing\Column\Company\SharedCatalogId
     */
    private $sharedCatalogId;

    /**
     * @var \Magento\Framework\View\Element\UiComponent\ContextInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $contextMock;

    /**
     * Set up.
     *
     * @return void
     */
    public function setUp()
    {
        $processorMock = $this->getMockBuilder(\Magento\Framework\View\Element\UiComponent\Processor::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->contextMock = $this->getMockBuilder(\Magento\Framework\View\Element\UiComponent\ContextInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->contextMock->expects($this->never())->method('getProcessor')->willReturn($processorMock);

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->sharedCatalogId = $this->objectManagerHelper->getObject(
            \Magento\SharedCatalog\Ui\Component\Listing\Column\Company\SharedCatalogId::class,
            [
                'context' => $this->contextMock
            ]
        );
    }

    /**
     * @return void
     */
    public function testApplySorting()
    {
        $fieldName = 'test';
        $direction = 'direction';
        $sorting = [
            'field' => $fieldName,
            'direction' => $direction
        ];
        $this->contextMock->expects($this->once())->method('getRequestParam')->with('sorting')->willReturn($sorting);
        $this->sharedCatalogId->setData('config/sortable', true);
        $this->sharedCatalogId->setData('name', $fieldName);
        $dataProviderMock = $this->getMockBuilder(DataProviderInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->contextMock->expects($this->once())->method('getDataProvider')->willReturn($dataProviderMock);
        $dataProviderMock->expects($this->once())->method('addOrder')->with(
            'shared_catalog_name',
            strtoupper($direction)
        );

        $this->sharedCatalogId->applySorting();
    }
}
