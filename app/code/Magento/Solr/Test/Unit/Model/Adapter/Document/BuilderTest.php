<?php

namespace Magento\Solr\Test\Unit\Model\Adapter\Document;

use Magento\Solr\Model\Adapter\Document\Builder;

/**
 * Unit test for Magento\Solr\Model\Adapter\Document\Builder
 */
class BuilderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Solr\Model\Adapter\Document\Builder
     */
    private $builder;

    /**
     * @var \Magento\Solr\Model\Adapter\Document\Factory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $factoryMock;

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        $this->factoryMock = $this->getMockBuilder(\Magento\Solr\Model\Adapter\Document\Factory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->builder = new Builder(
            $this->factoryMock
        );
    }

    /**
     * @return void
     */
    public function testBuildWithSimpleField()
    {
        $document = $this->createDocumentMock();
        $field = 'fieldName';
        $value = 'fieldValue';
        $document->expects($this->at(0))
            ->method('addField')
            ->with($field, $value);

        $this->builder->addField($field, $value);
        $result = $this->builder->build();
        $this->assertEquals($document, $result);
    }

    /**
     * @return void
     */
    public function testBuildWithTwoSimpleFields()
    {
        $document = $this->createDocumentMock();

        $fieldOne = 'fieldNameOne';
        $valueOne = 'fieldValueOne';
        $document->expects($this->at(0))
            ->method('addField')
            ->with($fieldOne, $valueOne);

        $fieldTwo = 'fieldNameTwo';
        $valueTwo = 'fieldValueTwo';
        $document->expects($this->at(1))
            ->method('addField')
            ->with($fieldTwo, $valueTwo);

        $this->builder->addField($fieldOne, $valueOne);
        $this->builder->addField($fieldTwo, $valueTwo);
        $result = $this->builder->build();
        $this->assertEquals($document, $result);
    }

    /**
     * @return void
     */
    public function testBuildWithSimpleFieldAndFieldsArray()
    {
        $document = $this->createDocumentMock();

        $this->builder->addField('fieldNameOne', 'fieldValueOne');
        $this->builder->addField('fieldNameTwo', 'fieldValueTwo');

        $this->builder->addFields(
            [
                'fieldThree' => 'fieldValueThree',
                'fieldNameTwo' => 'changedFieldValueTwo',
                'fieldFour' => 'fieldValueFour',
            ]
        );

        $document->expects($this->at(0))
            ->method('addField')
            ->with('fieldNameOne', 'fieldValueOne');
        $document->expects($this->at(1))
            ->method('addField')
            ->with('fieldNameTwo', 'changedFieldValueTwo');
        $document->expects($this->at(2))
            ->method('addField')
            ->with('fieldThree', 'fieldValueThree');
        $document->expects($this->at(3))
            ->method('addField')
            ->with('fieldFour', 'fieldValueFour');

        $result = $this->builder->build();
        $this->assertEquals($document, $result);
    }

    /**
     * @return void
     */
    public function testBuildWithArrayField()
    {
        $document = $this->createDocumentMock();

        $field = 'nameOfField';
        $values = ['value1', 'value2'];

        $this->builder->addField($field, $values);

        $document->expects($this->at(0))
            ->method('addField')
            ->with($field, $values[0]);
        $document->expects($this->at(1))
            ->method('addField')
            ->with($field, $values[1]);

        $result = $this->builder->build();
        $this->assertEquals($document, $result);
    }

    /**
     * @return void
     */
    public function testBuildTwoDocuments()
    {
        $documentOne = $this->createDocumentMock(0);
        $documentTwo = $this->createDocumentMock(1);

        $docOneFieldOne = 'docOneFieldOne';
        $docOneValueOne = 'docOneValueOne';
        $docOneFieldTwo = 'docOneFieldTwo';
        $docOneValueTwo = 'docOneValueTwo';

        $docTwoFieldOne = 'docTwoFieldOne';
        $docTwoValueOne = 'docTwoValueOne';
        $docTwoFieldTwo = 'docTwoFieldTwo';
        $docTwoValueTwo = 'docTwoValueTwo';

        $documentOne->expects($this->at(0))
            ->method('addField')
            ->with($docOneFieldOne, $docOneValueOne);
        $documentOne->expects($this->at(1))
            ->method('addField')
            ->with($docOneFieldTwo, $docOneValueTwo);
        $documentTwo->expects($this->at(0))
            ->method('addField')
            ->with($docTwoFieldOne, $docTwoValueOne);
        $documentTwo->expects($this->at(1))
            ->method('addField')
            ->with($docTwoFieldTwo, $docTwoValueTwo);

        $this->builder->addField($docOneFieldOne, $docOneValueOne);
        $this->builder->addField($docOneFieldTwo, $docOneValueTwo);
        $resultOne = $this->builder->build();

        $this->builder->addField($docTwoFieldOne, $docTwoValueOne);
        $this->builder->addField($docTwoFieldTwo, $docTwoValueTwo);
        $resultTwo = $this->builder->build();

        $this->assertEquals($documentOne, $resultOne);
        $this->assertEquals($documentTwo, $resultTwo);
    }

    /**
     * @param int $sequence
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function createDocumentMock($sequence = 0)
    {
        $document = $this->getMockBuilder(\Solarium\QueryType\Update\Query\Document\Document::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->factoryMock->expects($this->at($sequence))
            ->method('create')
            ->willReturn($document);
        return $document;
    }
}
