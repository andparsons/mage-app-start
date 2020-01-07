<?php
namespace Magento\NegotiableQuote\Test\Unit\Model;

use Magento\NegotiableQuote\Model\NegotiableQuoteItemRepository;
use Magento\NegotiableQuote\Model\ResourceModel\NegotiableQuoteItem as NegotiableQuoteItemResource;
use Magento\NegotiableQuote\Model\NegotiableQuoteItem;
use Magento\Framework\Exception\CouldNotSaveException;

/**
 * Class NegotiableQuoteItemRepositoryTest.
 */
class NegotiableQuoteItemRepositoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var NegotiableQuoteItemRepository
     */
    private $model;

    /**
     * @var NegotiableQuoteItemResource|\PHPUnit_Framework_MockObject_MockObject
     */
    private $negotiableQuoteItemResource;

    /**
     * @var NegotiableQuoteItem|\PHPUnit_Framework_MockObject_MockObject
     */
    private $negotiateQuoteItem;

    /**
     * Set up.
     */
    protected function setUp()
    {
        $this->negotiableQuoteItemResource = $this->createPartialMock(NegotiableQuoteItemResource::class, ['save']);

        $this->negotiateQuoteItem = $this->createMock(NegotiableQuoteItem::class);

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $objectManager->getObject(
            NegotiableQuoteItemRepository::class,
            [
                'negotiableQuoteItemResource' => $this->negotiableQuoteItemResource,
            ]
        );
    }

    /**
     * Test for method save.
     */
    public function testSave()
    {
        $this->negotiableQuoteItemResource->expects($this->once())
            ->method('save')
            ->willReturn($this->negotiateQuoteItem);

        $this->assertTrue($this->model->save($this->negotiateQuoteItem));
    }

    /**
     * Test for method save throwing exception.
     */
    public function testSaveException()
    {
        $exception = new \Exception();
        $this->expectException(CouldNotSaveException::class);
        $this->negotiableQuoteItemResource->expects($this->once())->method('save')
            ->with($this->negotiateQuoteItem)->willThrowException($exception);

        $this->assertInstanceOf(\Exception::class, $this->model->save($this->negotiateQuoteItem));
    }
}
