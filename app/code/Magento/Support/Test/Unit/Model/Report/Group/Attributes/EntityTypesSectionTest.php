<?php
namespace Magento\Support\Test\Unit\Model\Report\Group\Attributes;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class EntityTypesSectionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Support\Model\Report\Group\Attributes\EntityTypesSection
     */
    protected $entityTypesSection;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManagerHelper;

    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Type\CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $entityTypeCollectionFactoryMock;

    /**
     * @var \Magento\Support\Model\Report\Group\Attributes\DataFormatter|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $dataFormatterMock;

    protected function setUp()
    {
        $this->entityTypeCollectionFactoryMock = $this
            ->getMockBuilder(\Magento\Eav\Model\ResourceModel\Entity\Type\CollectionFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->dataFormatterMock = $this->getMockBuilder(
            \Magento\Support\Model\Report\Group\Attributes\DataFormatter::class
        )
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->entityTypesSection = $this->objectManagerHelper->getObject(
            \Magento\Support\Model\Report\Group\Attributes\EntityTypesSection::class,
            [
                'entityTypeCollectionFactory' => $this->entityTypeCollectionFactoryMock,
                'dataFormatter' => $this->dataFormatterMock
            ]
        );
    }

    public function testGenerate()
    {
        $data = [
            $this->createEntityTypeMock(
                [
                    'entity_type_id' => '1',
                    'entity_type_code' => 'code1',
                    'entity_model' => 'First\Entity\Model',
                    'attribute_model' => 'First\Attribute\Model',
                    'increment_model' => null,
                    'entity_table' => 'entity_table1',
                    'additional_attribute_table' => 'additional_table1'
                ]
            ),
            $this->createEntityTypeMock(
                [
                    'entity_type_id' => '2',
                    'entity_type_code' => 'code2',
                    'entity_model' => 'Second\Entity\Model',
                    'attribute_model' => 'Second\Attribute\Model',
                    'increment_model' => 'Second\Increment\Model',
                    'entity_table' => 'entity_table2',
                    'additional_attribute_table' => 'null'
                ]
            )
        ];
        $expectedResult = [
            (string)__('Entity Types') => [
                'headers' => [
                    __('ID'), __('Code'), __('Model'), __('Attribute Model'), __('Increment Model'),
                    __('Main Table'), __('Additional Attribute Table')
                ],
                'data' => [
                    [
                        '1',
                        'code1',
                        'First\Entity\Model' . "\n" . '{First/Entity/Model.php}',
                        'First\Attribute\Model' . "\n" . '{First/Attribute/Model.php}',
                        '',
                        'entity_table1',
                        'additional_table1'
                    ],
                    [
                        '2',
                        'code2',
                        'Second\Entity\Model' . "\n" . '{Second/Entity/Model.php}',
                        'Second\Attribute\Model' . "\n" . '{Second/Attribute/Model.php}',
                        'Second\Increment\Model' . "\n" . '{Second/Increment/Model.php}',
                        'entity_table2',
                        'null'
                    ]
                ]
            ]
        ];

        $this->entityTypeCollectionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->createEntityTypeCollectionMock($data));
        $this->dataFormatterMock->expects($this->any())
            ->method('prepareModelValue')
            ->willReturnMap(
                [
                    ['First\Entity\Model', 'First\Entity\Model' . "\n" . '{First/Entity/Model.php}'],
                    ['First\Attribute\Model', 'First\Attribute\Model' . "\n" . '{First/Attribute/Model.php}'],
                    ['Second\Entity\Model', 'Second\Entity\Model' . "\n" . '{Second/Entity/Model.php}'],
                    ['Second\Attribute\Model', 'Second\Attribute\Model' . "\n" . '{Second/Attribute/Model.php}'],
                    ['Second\Increment\Model', 'Second\Increment\Model' . "\n" . '{Second/Increment/Model.php}']
                ]
            );

        $this->assertEquals($expectedResult, $this->entityTypesSection->generate());
    }

    /**
     * Create entity type collection mock object
     *
     * @param array $data
     * @return \Magento\Eav\Model\ResourceModel\Entity\Type\Collection|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function createEntityTypeCollectionMock(array $data)
    {
        $entityTypeCollectionMock = $this->getMockBuilder(
            \Magento\Eav\Model\ResourceModel\Entity\Type\Collection::class
        )
            ->disableOriginalConstructor()
            ->getMock();

        $entityTypeCollectionMock->expects($this->any())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator($data));

        return $entityTypeCollectionMock;
    }

    /**
     * Create entity type mock object
     *
     * @param array $data
     * @return \Magento\Eav\Model\Entity\Type|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function createEntityTypeMock(array $data)
    {
        $entityTypeMock = $this->getMockBuilder(\Magento\Eav\Model\Entity\Type::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'getEntityTypeId', 'getEntityTypeCode', 'getEntityModel', 'getAttributeModel',
                    'getIncrementModel', 'getEntityTable', 'getAdditionalAttributeTable'
                ]
            )
            ->getMock();

        $entityTypeMock->expects($this->any())
            ->method('getEntityTypeId')
            ->willReturn($data['entity_type_id']);
        $entityTypeMock->expects($this->any())
            ->method('getEntityTypeCode')
            ->willReturn($data['entity_type_code']);
        $entityTypeMock->expects($this->any())
            ->method('getEntityModel')
            ->willReturn($data['entity_model']);
        $entityTypeMock->expects($this->any())
            ->method('getAttributeModel')
            ->willReturn($data['attribute_model']);
        $entityTypeMock->expects($this->any())
            ->method('getIncrementModel')
            ->willReturn($data['increment_model']);
        $entityTypeMock->expects($this->any())
            ->method('getEntityTable')
            ->willReturn($data['entity_table']);
        $entityTypeMock->expects($this->any())
            ->method('getAdditionalAttributeTable')
            ->willReturn($data['additional_attribute_table']);

        return $entityTypeMock;
    }
}
