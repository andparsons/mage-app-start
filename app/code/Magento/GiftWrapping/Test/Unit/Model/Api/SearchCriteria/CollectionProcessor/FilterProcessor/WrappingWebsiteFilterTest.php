<?php
namespace Magento\GiftWrapping\Test\Unit\Model\Api\SearchCriteria\CollectionProcessor\FilterProcessor;

use Magento\GiftWrapping\Model\Api\SearchCriteria\CollectionProcessor\FilterProcessor\WrappingWebsitesFilter;

/**
 * Class StatusFilterTest
 * @package Magento\GiftWrapping\Test\Unit\Model\Api\SearchCriteria\CollectionProcessor\FilterProcessor
 */
class WrappingWebsiteFilterTest extends \PHPUnit\Framework\TestCase
{
    /** @var  WrappingWebsitesFilter */
    private $model;

    public function setUp()
    {
        $this->model = new WrappingWebsitesFilter();
    }

    public function testApply()
    {
        $collectionMock = $this->getMockBuilder(\Magento\GiftWrapping\Model\ResourceModel\Wrapping\Collection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $filterMock = $this->getMockBuilder(\Magento\Framework\Api\Filter::class)
            ->disableOriginalConstructor()
            ->getMock();
        $filterMock->expects($this->once())
            ->method('getValue')
            ->willReturn('1');
        $collectionMock->expects($this->once())
            ->method('applyWebsiteFilter')
            ->with('1');
        $this->assertTrue($this->model->apply($filterMock, $collectionMock));
    }
}
