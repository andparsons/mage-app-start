<?php
namespace Magento\Solr\Test\Unit\SearchAdapter\Query\Builder;

use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Solr\SearchAdapter\ConditionManager;
use Magento\Solr\SearchAdapter\Query\Builder\Match;
use Magento\Solr\SearchAdapter\FieldMapperInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class MatchTest extends \PHPUnit\Framework\TestCase
{

    /**
     * @var ConditionManager|MockObject
     */
    private $conditionManager;

    /**
     * @var FieldMapperInterface|MockObject
     */
    private $mapper;

    /**
     * @var Match
     */
    private $matchBuilder;

    protected function setUp()
    {
        $helper = new ObjectManager($this);

        $this->conditionManager = $this->getMockBuilder(\Magento\Solr\SearchAdapter\ConditionManager::class)
            ->setMethods(['wrapBrackets', 'combineQueries'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->mapper = $this->getMockBuilder(\Magento\Solr\SearchAdapter\FieldMapperInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->matchBuilder = $helper->getObject(
            \Magento\Solr\SearchAdapter\Query\Builder\Match::class,
            [
                'conditionManager' => $this->conditionManager,
                'mapper' => $this->mapper
            ]
        );
    }

    public function testBuildQuery()
    {
        $select = $this->getMockBuilder(\Solarium\QueryType\Select\Query\Query::class)
            ->setMethods(['getQuery', 'setQuery'])
            ->disableOriginalConstructor()
            ->getMock();

        $query = $this->getMockBuilder(\Magento\Framework\Search\Request\Query\Match::class)
            ->setMethods(['getValue', 'getMatches'])
            ->disableOriginalConstructor()
            ->getMock();

        $query->expects($this->once())->method('getValue')->willReturn('query_value');
        $select->expects($this->any())->method('getQuery')->willReturn(Match::DEFAULT_CONDITION);
        $query->expects($this->once())->method('getMatches')->willReturn([['field' => 'some_field']]);
        $this->mapper->expects($this->once())
            ->method('getFieldName')
            ->with('some_field', ['type' => 'text'])
            ->willReturn('some_field_name');
        $this->conditionManager->expects($this->at(0))->method('wrapBrackets')->willReturn('(wrapedConditions)');
        $this->conditionManager->expects($this->at(1))
            ->method('wrapBrackets')
            ->with('(wrapedConditions)')
            ->willReturn('(wrapedConditions)');
        $this->conditionManager->expects($this->any())->method('combineQueries')->willReturn('combined_query');
        $select->expects($this->once())->method('setQuery')->willReturnSelf();

        $this->assertEquals($select, $this->matchBuilder->build($select, $query, 'not'));
    }
}
