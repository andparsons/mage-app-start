<?php

namespace Magento\Solr\Test\Unit\SearchAdapter;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class ResponseFactoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Solr\SearchAdapter\ResponseFactory
     */
    private $factory;

    /**
     * @var \Magento\Solr\SearchAdapter\DocumentFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $documentFactory;

    /**
     * @var \Magento\Solr\SearchAdapter\AggregationFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $aggregationFactory;

    /**
     * @var \Magento\Framework\ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $objectManager;

    protected function setUp()
    {
        $helper = new ObjectManager($this);

        $this->documentFactory = $this->getMockBuilder(\Magento\Solr\SearchAdapter\DocumentFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->aggregationFactory = $this->getMockBuilder(\Magento\Solr\SearchAdapter\AggregationFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManager = $this->createMock(\Magento\Framework\ObjectManagerInterface::class);

        $this->factory = $helper->getObject(
            \Magento\Solr\SearchAdapter\ResponseFactory::class,
            [
                'documentFactory' => $this->documentFactory,
                'aggregationFactory' => $this->aggregationFactory,
                'objectManager' => $this->objectManager
            ]
        );
    }

    public function testCreate()
    {
        $documents = [
            ['title' => 'oneTitle', 'description' => 'oneDescription'],
            ['title' => 'twoTitle', 'description' => 'twoDescription'],
        ];
        $aggregations = [
            'aggregation1' => [
                'itemOne' => 10,
                'itemTwo' => 20,
            ],
            'aggregation2' => [
                'itemOne' => 5,
                'itemTwo' => 45,
            ]
        ];
        $rawResponse = ['documents' => $documents, 'aggregations' => $aggregations];

        $exceptedResponse = [
            'documents' => [
                [
                    ['name' => 'title', 'value' => 'oneTitle'],
                    ['name' => 'description', 'value' => 'oneDescription'],
                ],
                [
                    ['name' => 'title', 'value' => 'twoTitle'],
                    ['name' => 'description', 'value' => 'twoDescription'],
                ],
            ],
            'aggregations' => [
                'aggregation1' => [
                    'itemOne' => 10,
                    'itemTwo' => 20
                ],
                'aggregation2' => [
                    'itemOne' => 5,
                    'itemTwo' => 45
                ],
            ],
        ];

        $this->documentFactory->expects($this->at(0))->method('create')
            ->with($this->equalTo($documents[0]))
            ->will($this->returnValue('document1'));
        $this->documentFactory->expects($this->at(1))->method('create')
            ->with($documents[1])
            ->will($this->returnValue('document2'));

        $this->aggregationFactory->expects($this->at(0))->method('create')
            ->with($this->equalTo($exceptedResponse['aggregations']))
            ->will($this->returnValue('aggregationsData'));

        $this->objectManager->expects($this->once())->method('create')
            ->with(
                $this->equalTo(\Magento\Framework\Search\Response\QueryResponse::class),
                $this->equalTo(['documents' => ['document1', 'document2'], 'aggregations' => 'aggregationsData'])
            )
            ->will($this->returnValue('QueryResponseObject'));

        $result = $this->factory->create($rawResponse);
        $this->assertEquals('QueryResponseObject', $result);
    }
}
