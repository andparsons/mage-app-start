<?php
namespace Magento\CatalogRule\Test\Unit\Plugin\Indexer\Product\Save;

use Magento\CatalogRule\Plugin\Indexer\Product\Save\ApplyRulesAfterReindex;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\CatalogRule\Model\Indexer\Product\ProductRuleProcessor;
use Magento\Catalog\Model\Product;

class ApplyRulesAfterReindexTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ApplyRulesAfterReindex
     */
    private $plugin;

    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var ProductRuleProcessor|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productRuleProcessorMock;

    /**
     * @var Product|\PHPUnit_Framework_MockObject_MockObject
     */
    private $subjectMock;

    protected function setUp()
    {
        $this->productRuleProcessorMock = $this->getMockBuilder(ProductRuleProcessor::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->subjectMock = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->plugin = $this->objectManagerHelper->getObject(
            ApplyRulesAfterReindex::class,
            ['productRuleProcessor' => $this->productRuleProcessorMock]
        );
    }

    public function testAfterReindex()
    {
        $id = 'test_id';

        $this->subjectMock->expects(static::any())
            ->method('getId')
            ->willReturn($id);
        $this->productRuleProcessorMock->expects(static::once())
            ->method('reindexRow')
            ->with($id, false);

        $this->plugin->afterReindex($this->subjectMock);
    }
}
