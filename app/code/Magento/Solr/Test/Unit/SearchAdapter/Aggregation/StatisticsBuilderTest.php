<?php
namespace Magento\Solr\Test\Unit\SearchAdapter\Aggregation;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Search\Request\BucketInterface;
use Magento\Framework\Search\RequestInterface;
use Magento\Solr\SearchAdapter\Aggregation\StatisticsBuilder;
use Magento\Solr\SearchAdapter\FieldMapperInterface;
use Solarium\QueryType\Select\Query\Component\Facet\Field;
use Solarium\QueryType\Select\Query\Component\FacetSet;
use Solarium\QueryType\Select\Query\Component\Stats\Stats;
use Solarium\QueryType\Select\Query\Query;

class StatisticsBuilderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var FieldMapperInterface
     */
    private $fieldMapper;

    /** @var StatisticsBuilder */
    private $statisticsBuilder;

    protected function setUp()
    {
        $helper = new ObjectManager($this);

        $this->fieldMapper = $this->getMockBuilder(FieldMapperInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->statisticsBuilder = $helper->getObject(
            StatisticsBuilder::class,
            [
                'fieldMapper' => $this->fieldMapper
            ]
        );
    }

    public function testBuildTerm()
    {
        $bucket = $this->getMockBuilder(BucketInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $bucket->expects($this->any())
            ->method('getType')
            ->willReturn(BucketInterface::TYPE_TERM);

        $request = $this->getMockBuilder(RequestInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $request->expects($this->any())
            ->method('getAggregation')
            ->willReturn([$bucket]);

        $query = $this->getMockBuilder(Query::class)
            ->disableOriginalConstructor()
            ->getMock();

        $facetSet = $this->getMockBuilder(FacetSet::class)
            ->disableOriginalConstructor()
            ->getMock();
        $field = $this->getMockBuilder(Field::class)
            ->disableOriginalConstructor()
            ->getMock();

        $query->expects($this->once())->method('getFacetSet')->willReturn($facetSet);
        $facetSet->expects($this->once())->method('createFacetField')->willReturn($field);

        $this->statisticsBuilder->build($request, $query);
    }

    public function testBuildDynamic()
    {
        $bucket = $this->getMockBuilder(BucketInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $bucket->expects($this->any())
            ->method('getType')
            ->willReturn(BucketInterface::TYPE_DYNAMIC);

        $request = $this->getMockBuilder(RequestInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $request->expects($this->any())
            ->method('getAggregation')
            ->willReturn([$bucket]);

        $query = $this->getMockBuilder(Query::class)
            ->disableOriginalConstructor()
            ->getMock();

        $stats = $this->getMockBuilder(Stats::class)
            ->disableOriginalConstructor()
            ->getMock();
        $field = $this->getMockBuilder(Field::class)
            ->disableOriginalConstructor()
            ->getMock();

        $query->expects($this->once())->method('getStats')->willReturn($stats);
        $stats->expects($this->once())->method('createField')->willReturn($field);

        $this->statisticsBuilder->build($request, $query);
    }
}
