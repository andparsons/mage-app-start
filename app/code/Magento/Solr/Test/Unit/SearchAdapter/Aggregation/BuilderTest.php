<?php
namespace Magento\Solr\Test\Unit\SearchAdapter\Aggregation;

use Magento\Framework\Search\Request\BucketInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Search\Dynamic\DataProviderInterface;
use Magento\Framework\Search\RequestInterface;
use Magento\Solr\SearchAdapter\Aggregation\Builder;
use Magento\Solr\SearchAdapter\Aggregation\Builder\BucketBuilderInterface;
use Solarium\Core\Query\Result\ResultInterface;

class BuilderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var BucketBuilderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $bucket;

    /**
     * @var DataProviderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dataProvider;

    /**
     * @var BucketBuilderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $bucketBuilder;

    /**
     * @var \Magento\Solr\SearchAdapter\Aggregation\Builder
     */
    private $builder;

    /**
     * SetUP method
     */
    protected function setUp()
    {
        $helper = new ObjectManager($this);

        $this->bucket = $this->getMockBuilder(BucketInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->bucketBuilder = $this->getMockBuilder(
            BucketBuilderInterface::class
        )
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->dataProvider = $this->getMockBuilder(
            DataProviderInterface::class
        )
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->builder = $helper->getObject(
            Builder::class,
            [
                'dataProviderContainer' => ['test' => $this->dataProvider],
                'aggregationContainer' => ['type' => $this->bucketBuilder]
            ]
        );
    }

    /**
     * Test for method "build"
     */
    public function testBuild()
    {
        $fetchResult = ['name' => ['some', 'result']];

        $this->bucket->expects($this->once())->method('getType')->willReturn('type');
        $this->bucket->expects($this->once())->method('getName')->willReturn('name');

        $request = $this->getMockBuilder(RequestInterface::class)->getMock();
        $request->expects($this->any())->method('getIndex')->willReturn('test');
        $request->expects($this->once())->method('getAggregation')->willReturn([$this->bucket]);
        $request->expects($this->once())->method('getDimensions')->willReturn([]);

        $this->bucketBuilder->expects($this->once())->method('build')->willReturn($fetchResult['name']);

        $result = $this->getMockBuilder(ResultInterface::class)->getMock();

        $result = $this->builder->build($request, $result);

        $this->assertEquals($result, $fetchResult);
    }
}
