<?php
namespace Magento\NegotiableQuote\Test\Unit\Block\Adminhtml\Quote\View\Errors;

use Magento\AdvancedCheckout\Block\Adminhtml\Sku\Errors\Grid\ColumnSet\SkuErrors;
use Magento\Framework\View\LayoutInterface;
use Magento\NegotiableQuote\Model\ResourceModel\Sku\Errors\Grid\Collection;

/**
 * Unit test for \Magento\NegotiableQuote\Block\Adminhtml\Quote\View\Errors\Grid class.
 */
class GridTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\NegotiableQuote\Block\Adminhtml\Quote\View\Errors\Grid
     */
    private $grid;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteRepository;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $request;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->request = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['has'])
            ->getMockForAbstractClass();

        $this->quoteRepository = $this->getMockBuilder(\Magento\Quote\Api\CartRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->grid = $objectManager->getObject(
            \Magento\NegotiableQuote\Block\Adminhtml\Quote\View\Errors\Grid::class,
            [
                '_request' => $this->request,
                'quoteRepository' => $this->quoteRepository,
            ]
        );
    }

    /**
     * Tests getPreparedCollection() method.
     *
     * @return void
     */
    public function testGetPreparedCollection()
    {
        $collection = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $layout = $this->getMockBuilder(LayoutInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $layout->expects($this->once())
            ->method('getChildName')
            ->willReturn('child');
        $child = $this->getMockBuilder(SkuErrors::class)
            ->disableOriginalConstructor()
            ->getMock();
        $layout->expects($this->once())
            ->method('getBlock')
            ->willReturn($child);
        $this->request->expects($this->once())
            ->method('getParam')
            ->willReturn(1);

        $quote = $this->getMockBuilder(\Magento\Quote\Api\Data\CartInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCustomerGroupId'])
            ->getMockForAbstractClass();
        $quote->expects($this->once())
            ->method('getCustomerGroupId')
            ->willReturn(2);
        $this->quoteRepository->expects($this->once())
            ->method('get')
            ->willReturn($quote);

        $this->grid->setCollection($collection);
        $this->grid->setLayout($layout);
        $this->grid->getPreparedCollection();
    }
}
