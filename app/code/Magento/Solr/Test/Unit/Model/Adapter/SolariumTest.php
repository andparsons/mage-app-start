<?php

namespace Magento\Solr\Test\Unit\Model\Adapter;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Class SolariumTest
 *
 * @see \Magento\Solr\Model\Adapter\Solarium
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SolariumTest extends \PHPUnit\Framework\TestCase
{
    /** @var  \Magento\AdvancedSearch\Model\ResourceModel\Index|\PHPUnit_Framework_MockObject_MockObject */
    private $resourceIndex;

    /** @var  \Magento\Solr\Model\Adapter\Container\Attribute|\PHPUnit_Framework_MockObject_MockObject */
    private $attributeContainer;

    /** @var  \Magento\Solr\Model\Adapter\DocumentDataMapper|\PHPUnit_Framework_MockObject_MockObject */
    private $documentDataMapper;

    /** @var  \Magento\Solr\Helper\Data|\PHPUnit_Framework_MockObject_MockObject */
    private $clientHelper;

    /** @var  \Magento\Solr\Model\Client\Solarium|\PHPUnit_Framework_MockObject_MockObject */
    private $client;

    /**
     * @var \Magento\Solr\Model\Adapter\Solarium
     */
    private $target;

    /**
     * Setup test function
     *
     * @return void
     */
    protected function setUp()
    {
        $clientOptions = ['asdf', 1234, '4312'];
        $this->client = $this->getMockBuilder(\Magento\Solr\Model\Client\Solarium::class)
            ->disableOriginalConstructor()
            ->setMethods(['selectQuery', 'ping', 'optimize', 'addDocuments', 'deleteByQueries'])
            ->getMock();
        $this->clientHelper = $this->getMockBuilder(\Magento\Solr\Helper\Data::class)
            ->disableOriginalConstructor()
            ->setMethods(['prepareClientOptions'])
            ->getMockForAbstractClass();
        $this->clientHelper->expects($this->once())
            ->method('prepareClientOptions')
            ->willReturn($clientOptions);
        $clientFactory = $this->getMockBuilder(\Magento\AdvancedSearch\Model\Client\ClientFactoryInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMockForAbstractClass();
        $clientFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->client);
        $objectManagerHelper = new ObjectManagerHelper($this);

        $this->documentDataMapper = $this->getMockBuilder(\Magento\Solr\Model\Adapter\DocumentDataMapper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->attributeContainer = $this->getMockBuilder(\Magento\Solr\Model\Adapter\Container\Attribute::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->resourceIndex = $this->getMockBuilder(\Magento\AdvancedSearch\Model\ResourceModel\Index::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->target = $objectManagerHelper->getObject(
            \Magento\Solr\Model\Adapter\Solarium::class,
            [
                'clientFactory' => $clientFactory,
                'clientHelper' => $this->clientHelper,
                'attributeContainer' => $this->attributeContainer,
                'documentDataMapper' => $this->documentDataMapper,
                'resourceIndex' => $this->resourceIndex,
            ]
        );
    }

    /**
     * @return void
     */
    public function testPing()
    {
        $this->assertTrue($this->target->ping());
    }

    /**
     * @return void
     */
    public function testOptimize()
    {
        $value = 21346576543;
        $this->client->expects($this->once())
            ->method('optimize')
            ->willReturn($value);
        $this->assertEquals($value, $this->target->optimize());
    }

    /**
     * @return void
     */
    public function testDeleteByQueries()
    {
        $value = 'asdfasdfasdf';
        $rawQueries = ['rawQueries' => 'here'];
        $fromPending = false;
        $fromCommited = false;
        $timeout = 100500;
        $this->client->expects($this->once())
            ->method('deleteByQueries')
            ->with($rawQueries, 'false', 'false', 100500)
            ->willReturn($value);
        $this->assertEquals($value, $this->target->deleteByQueries($rawQueries, $fromPending, $fromCommited, $timeout));
    }

    /**
     * @return void
     */
    public function testAddDocuments()
    {
        $value = 'some test value';
        $docs = [$this->createDocument([123]), $this->createDocument(['asdfas' => 123123])];
        $overwrite = false;
        $commitWithin = 123;
        $this->client->expects($this->once())
            ->method('addDocuments')
            ->with($docs, $overwrite, $commitWithin)
            ->willReturn($value);
        $this->assertEquals($value, $this->target->addDocuments($docs, $overwrite, $commitWithin));
    }

    /**
     * @return void
     */
    public function testPrepareDocsPerStore()
    {

        $docs = [
            1 => [
                'visibilityId' => [1 => 1]
            ],
            2 => [
                'visibilityId' => [2 => 1]
            ],
            3 => [
                'visibilityId' => [3 => 1]
            ],
        ];

        $this->resourceIndex->method('getCategoryProductIndexData')->willReturn([]);

        $this->attributeContainer->method('getAttributeIdByCode')
            ->with('visibility')
            ->willReturn('visibilityId');

        $this->documentDataMapper->expects($this->any())
            ->method('map')
            ->willReturnArgument(0);

        $this->assertEquals(array_values($docs), $this->target->prepareDocsPerStore($docs, 100500));
    }

    /**
     * @return string
     */
    public function returnGetFieldName()
    {
        $args = func_get_args();
        switch ($args[0]) {
            case 'spell':
                return 'attr_spell_def';
                break;
            case 'title':
                return 'attr_nav_title';
                break;
            case 'name':
                return 'attr_name';
                break;
            case 'size':
                return 'attr_nav_multi_size';
                break;
            case 'cost':
                return 'attr_nav_cost';
                break;
            case 'fulltext':
                return 'attr_fulltext_def';
                break;
            default:
                return 'unknown';
                break;
        }
    }

    /**
     * @param mixed $attributeCode
     * @param mixed $sourceValue
     * @param mixed $frontendInput
     * @param null $backendType
     * @param bool $isSearchable
     * @return \Magento\Catalog\Model\ResourceModel\Eav\Attribute|MockObject
     */
    protected function createAttribute(
        $attributeCode,
        $sourceValue = false,
        $frontendInput = null,
        $backendType = null,
        $isSearchable = false
    ) {
        /** @var \PHPUnit_Framework_MockObject_MockObject $attribute */
        $attribute = $this->getMockBuilder(\Magento\Catalog\Model\ResourceModel\Eav\Attribute::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'getAttributeCode',
                    'getIsSearchable',
                    'getIsFilterableInSearch',
                    'getIsFilterable',
                    'getUsedForSortBy',
                    'usesSource',
                    'getSource',
                    'getFrontendInput',
                    'getBackendType',
                    'getSearchWeight'
                ]
            )
            ->getMock();
        $attribute->expects($this->any())
            ->method('getAttributeCode')
            ->willReturn($attributeCode);
        $attribute->expects($this->any())
            ->method('getIsSearchable')
            ->willReturn($isSearchable);
        $attribute->expects($this->any())
            ->method('getIsFilterableInSearch')
            ->willReturn(true);
        $attribute->expects($this->any())
            ->method('getIsFilterable')
            ->willReturn(true);
        $attribute->expects($this->any())
            ->method('getIsFilterableInSearch')
            ->willReturn(true);
        $attribute->expects($this->any())
            ->method('getUsedForSortBy')
            ->willReturn(true);
        $attribute->expects($this->any())
            ->method('usesSource')
            ->willReturn((bool)$sourceValue);
        $source = $this->getMockBuilder(\Magento\Eav\Model\Entity\Attribute\Source\AbstractSource::class)
            ->disableOriginalConstructor()
            ->setMethods(['getIndexOptionText'])
            ->getMockForAbstractClass();
        $source->expects($this->any())
            ->method('getIndexOptionText')
            ->willReturn($sourceValue);
        $attribute->expects($this->any())
            ->method('getSource')
            ->willReturn($source);
        $attribute->expects($this->any())
            ->method('getFrontendInput')
            ->willReturn($frontendInput);
        $attribute->expects($this->any())
            ->method('getBackendType')
            ->willReturn($backendType);
        $attribute->expects($this->any())
            ->method('getSearchWeight')
            ->willReturn(1000);

        return $attribute;
    }

    /**
     * @param mixed $fields
     * @param array $expectedFields Expected fields for addField method in format: [$sequence => [$name, $value]]
     * @return MockObject|\Solarium\QueryType\Select\Result\Document
     */
    protected function createDocument($fields, array $expectedFields = [])
    {
        $documentMock = $this->getMockBuilder(\Solarium\QueryType\Select\Result\Document::class)
            ->disableOriginalConstructor()
            ->setMethods(['getFields', 'addField'])
            ->getMock();
        $documentMock->expects($this->any())
            ->method('getFields')
            ->willReturn($fields);
        foreach ($expectedFields as $sequence => $field) {
            list($fieldName, $fieldValue) = $field;
            $documentMock->expects($this->at($sequence))
                ->method('addField')
                ->with($fieldName, $fieldValue)
                ->willReturnSelf();
        }

        return $documentMock;
    }
}
