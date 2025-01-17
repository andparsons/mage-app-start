<?php
namespace Magento\ImportExport\Test\Unit\Block\Adminhtml\Export;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * @SuppressWarnings(PHPMD)
 */
class FilterTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Model\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $modelContext;

    /**
     * @var \Magento\Framework\Registry|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $registry;

    /**
     * @var \Magento\Framework\Api\ExtensionAttributesFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $extensionFactory;

    /**
     * @var \Magento\Framework\Api\AttributeValueFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customAttributeFactory;

    /**
     * @var \Magento\Eav\Model\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $eavConfig;

    /**
     * @var \Magento\Eav\Model\Entity\TypeFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $eavTypeFactory;

    /**
     * @var \Magento\Store\Model\StoreManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManager;

    /**
     * @var \Magento\Eav\Model\ResourceModel\Helper|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resourceHelper;

    /**
     * @var \Magento\Framework\Validator\UniversalFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $universalFactory;

    /**
     * @var \Magento\Eav\Api\Data\AttributeOptionInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $optionDataFactory;

    /**
     * @var \Magento\Framework\Reflection\DataObjectProcessor|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $dataObjectProcessor;

    /**
     * @var \Magento\Framework\Api\DataObjectHelper|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $dataObjectHelper;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\Timezone|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $localeDate;

    /**
     * @var \Magento\Catalog\Model\Product\ReservedAttributeList|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $reservedAttributeList;

    /**
     * @var \Magento\Framework\Locale\Resolver|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $localeResolver;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resource;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $resourceCollection;

    /**
     * @var \Magento\Backend\Block\Template\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $context;

    /**
     * @var \Magento\Backend\Helper\Data|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $backendHelper;

    /**
     * @var \Magento\ImportExport\Helper\Data|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $importExportData;

    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    /**
     * @var \Magento\ImportExport\Block\Adminhtml\Export\Filter|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $filter;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTimeFormatterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dateTimeFormatter;

    protected function setUp()
    {
        $this->modelContext = $this->createMock(\Magento\Framework\Model\Context::class);
        $this->registry = $this->createMock(\Magento\Framework\Registry::class);
        $this->extensionFactory = $this->createMock(\Magento\Framework\Api\ExtensionAttributesFactory::class);
        $this->customAttributeFactory = $this->createMock(\Magento\Framework\Api\AttributeValueFactory::class);
        $this->eavConfig = $this->createMock(\Magento\Eav\Model\Config::class);
        $this->eavTypeFactory = $this->createMock(\Magento\Eav\Model\Entity\TypeFactory::class);
        $this->storeManager = $this->createMock(\Magento\Store\Model\StoreManager::class);
        $this->resourceHelper = $this->createMock(\Magento\Eav\Model\ResourceModel\Helper::class);
        $this->universalFactory = $this->createMock(\Magento\Framework\Validator\UniversalFactory::class);
        $this->optionDataFactory = $this->createMock(\Magento\Eav\Api\Data\AttributeOptionInterfaceFactory::class);
        $this->dataObjectProcessor = $this->createMock(\Magento\Framework\Reflection\DataObjectProcessor::class);
        $this->dataObjectHelper = $this->createMock(\Magento\Framework\Api\DataObjectHelper::class);
        $this->localeDate = $this->createMock(\Magento\Framework\Stdlib\DateTime\Timezone::class);
        $this->localeDate->expects($this->any())->method('getDateFormat')->will($this->returnValue('12-12-2012'));
        $this->reservedAttributeList = $this->createMock(\Magento\Catalog\Model\Product\ReservedAttributeList::class);
        $this->localeResolver = $this->createMock(\Magento\Framework\Locale\Resolver::class);
        $this->resource = $this->createMock(\Magento\Catalog\Model\ResourceModel\Product::class);
        $this->resourceCollection = $this->getMockForAbstractClass(
            \Magento\Framework\Data\Collection\AbstractDb::class,
            [],
            '',
            false
        );
        $this->context = $this->createPartialMock(
            \Magento\Backend\Block\Template\Context::class,
            ['getFileSystem', 'getEscaper', 'getLocaleDate', 'getLayout']
        );
        $filesystem = $this->createMock(\Magento\Framework\Filesystem::class);
        $this->context->expects($this->any())->method('getFileSystem')->will($this->returnValue($filesystem));
        $escaper = $this->createPartialMock(\Magento\Framework\Escaper::class, ['escapeHtml']);
        $escaper->expects($this->any())->method('escapeHtml')->will($this->returnValue(''));
        $this->context->expects($this->any())->method('getEscaper')->will($this->returnValue($escaper));
        $timeZone = $this->createMock(\Magento\Framework\Stdlib\DateTime\Timezone::class);
        $timeZone->expects($this->any())->method('getDateFormat')->will($this->returnValue('M/d/yy'));
        $this->context->expects($this->any())->method('getLocaleDate')->will($this->returnValue($timeZone));
        $dateBlock = $this->createPartialMock(
            \Magento\Framework\View\Element\Html\Date::class,
            ['setValue', 'getHtml', 'setId', 'getId']
        );
        $dateBlock->expects($this->any())->method('setValue')->will($this->returnSelf());
        $dateBlock->expects($this->any())->method('getHtml')->will($this->returnValue(''));
        $dateBlock->expects($this->any())->method('setId')->will($this->returnSelf());
        $dateBlock->expects($this->any())->method('getId')->will($this->returnValue(1));
        $layout = $this->createMock(\Magento\Framework\View\Layout::class);
        $layout->expects($this->any())->method('createBlock')->will($this->returnValue($dateBlock));
        $this->context->expects($this->any())->method('getLayout')->will($this->returnValue($layout));
        $this->backendHelper = $this->createMock(\Magento\Backend\Helper\Data::class);
        $this->importExportData = $this->createMock(\Magento\ImportExport\Helper\Data::class);
        $this->dateTimeFormatter = $this->createMock(
            \Magento\Framework\Stdlib\DateTime\DateTimeFormatterInterface::class
        );
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->filter = $this->objectManagerHelper->getObject(
            \Magento\ImportExport\Block\Adminhtml\Export\Filter::class,
            [
                'context' => $this->context,
                'backendHelper' => $this->backendHelper,
                'importExportData' => $this->importExportData
            ]
        );
    }

    /**
     * Test decorateFilter()
     *
     * @param array $attributeData
     * @param string $backendType
     * @param array $columnValue
     * @dataProvider decorateFilterDataProvider
     */
    public function testDecorateFilter($attributeData, $backendType, $columnValue)
    {
        $value = '';
        $attribute = new \Magento\Eav\Model\Entity\Attribute(
            $this->modelContext,
            $this->registry,
            $this->extensionFactory,
            $this->customAttributeFactory,
            $this->eavConfig,
            $this->eavTypeFactory,
            $this->storeManager,
            $this->resourceHelper,
            $this->universalFactory,
            $this->optionDataFactory,
            $this->dataObjectProcessor,
            $this->dataObjectHelper,
            $this->localeDate,
            $this->reservedAttributeList,
            $this->localeResolver,
            $this->dateTimeFormatter,
            $this->resource,
            $this->resourceCollection
        );
        $attribute->setAttributeCode($attributeData['code']);
        $attribute->setFrontendInput($attributeData['input']);
        $attribute->setOptions($attributeData['options']);
        $attribute->setFilterOptions($attributeData['filter_options']);
        $attribute->setBackendType($backendType);
        $column = new \Magento\Framework\DataObject();
        $column->setData($columnValue, 'value');
        $isExport = true;
        $result = $this->filter->decorateFilter($value, $attribute, $column, $isExport);
        $this->assertNotNull($result);
    }

    /**
     * Dataprovider for testDecorateFilter()
     *
     * @return array
     */
    public function decorateFilterDataProvider()
    {
        return [
            [
                'attributeCode' => [
                    'code' =>'updated_at',
                    'input' => '',
                    'options' => [],
                    'filter_options' => []
                ],
                'backendType' => 'datetime',
                'columnValue' => ['values' => ['updated_at' => '12/12/12']]
            ],
            [
                'attributeCode' => [
                    'code' => 'category_ids',
                    'input' => '',
                    'options' => [],
                    'filter_options' => []
                ],
                'backendType' => 'varchar',
                'columnValue' => ['values' => ['category_ids' => '1']]
            ],
            [
                'attributeCode' => [
                    'code' => 'cost',
                    'input' => '',
                    'options' => [],
                    'filter_options' => []
                ],
                'backendType' => 'decimal',
                'columnValue' => ['values' => ['cost' => 'cost']]
            ],
            [
                'attributeCode' => [
                    'code' => 'color',
                    'input' => 'select',
                    'options' => ['red' => 'red'],
                    'filter_options' => ['opt' => 'val']
                ],
                'backendType' => 'select',
                'columnValue' => ['values' => ['color' => 'red']]
            ]
        ];
    }

    /**
     * Test for protected method prepareForm()
     *
     * @todo to implement it.
     */
    public function testPrepareForm()
    {
        $this->markTestIncomplete('This test has not been implemented yet.');
    }
}
