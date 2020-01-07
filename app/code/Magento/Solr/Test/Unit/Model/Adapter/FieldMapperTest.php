<?php

namespace Magento\Solr\Test\Unit\Model\Adapter;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Solr\SearchAdapter\FieldMapperInterface;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

class FieldMapperTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Solr\Model\Adapter\FieldMapper
     */
    protected $mapper;

    /**
     * @var \Magento\Eav\Model\Config|MockObject
     */
    protected $eavConfig;

    /**
     * @var \Magento\Solr\Model\Adapter\FieldType|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $fieldType;

    protected function setUp()
    {
        $this->eavConfig = $this->getMockBuilder(\Magento\Eav\Model\Config::class)
            ->disableOriginalConstructor()
            ->setMethods(['getEntityType', 'getAttribute'])
            ->getMock();

        $this->fieldType = $this->getMockBuilder(\Magento\Solr\Model\Adapter\FieldType::class)
            ->disableOriginalConstructor()
            ->setMethods(['getFieldType'])
            ->getMock();

        $objectManager = new ObjectManagerHelper($this);

        $this->mapper = $objectManager->getObject(
            \Magento\Solr\Model\Adapter\FieldMapper::class,
            [
                'eavConfig' => $this->eavConfig,
                'fieldType' => $this->fieldType,
            ]
        );
    }

    /**
     * @dataProvider attributeCodeProvider
     * @param $attributeCode
     * @param $fieldName
     * @param $fieldType
     * @param array $context
     */
    public function testGetFieldName($attributeCode, $fieldName, $fieldType, $context = [])
    {
        $attribute = $this->getMockBuilder(\Magento\Catalog\Model\ResourceModel\Eav\Attribute::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->eavConfig->expects($this->any())->method('getAttribute')
            ->with(ProductAttributeInterface::ENTITY_TYPE_CODE, $attributeCode)
            ->willReturn($attribute);

        $this->fieldType->expects($this->any())->method('getFieldType')
            ->with($attribute)
            ->willReturn($fieldType);

        $this->assertEquals(
            $fieldName,
            $this->mapper->getFieldName($attributeCode, $context)
        );
    }

    /**
     * @return array
     */
    public static function attributeCodeProvider()
    {
        return [
            ['id', 'id', ''],
            ['price', 'price_22_66', '', ['customerGroupId' => '22', 'websiteId' => '66']],
            ['position', 'position_category_33', '', ['categoryId' => '33']],
            ['test_code', 'attr_test_code_def', 'string', ['type' => 'text']],
            ['test_code', 'attr_test_code_nb', 'string', ['type' => 'text', 'localeCode' => 'nn_NO']],
            ['test_code', 'attr_test_code_ru', 'string', ['type' => 'text', 'localeCode' => 'ru_RU']],
            ['*', 'fulltext_ru', 'string', ['type' => 'text', 'localeCode' => 'ru_RU']],
            ['test_code', 'attr_sort_string_test_code', 'string', ['type' => 'value']],
            ['spell', 'attr_spell_def', 'string', ['type' => 'text']],
            ['fulltext', 'attr_fulltext_def', 'string', ['type' => 'text']],
            ['filter', 'attr_filter_def', 'string', ['type' => 'default']],
            ['filter', 'attr_filter_def', 'string', ['type' => FieldMapperInterface::TYPE_FILTER]],
        ];
    }
}
