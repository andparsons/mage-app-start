<?php

namespace Magento\NegotiableQuote\Test\Unit\Model\Purged;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Class HandlerTest.
 */
class HandlerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var \Magento\NegotiableQuote\Model\Purged\Handler|\PHPUnit_Framework_MockObject_MockObject
     */
    private $handler;

    /**
     * @var \Magento\NegotiableQuote\Api\NegotiableQuoteRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $negotiableQuoteRepository;

    /**
     * @var \Magento\NegotiableQuote\Api\NegotiableQuoteManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $negotiableQuoteManagement;

    /**
     * @var \Magento\NegotiableQuote\Model\ResourceModel\PurgedContent|\PHPUnit_Framework_MockObject_MockObject
     */
    private $purgedContentResource;

    /**
     * @var \Magento\NegotiableQuote\Model\PurgedContentFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $purgedContentFactory;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->negotiableQuoteRepository = $this
            ->getMockBuilder(\Magento\NegotiableQuote\Api\NegotiableQuoteRepositoryInterface::class)
            ->setMethods(['getListByCustomerId'])
            ->disableOriginalConstructor()->getMockForAbstractClass();

        $this->negotiableQuoteManagement = $this
            ->getMockBuilder(\Magento\NegotiableQuote\Api\NegotiableQuoteManagementInterface::class)
            ->setMethods(['close'])
            ->disableOriginalConstructor()->getMockForAbstractClass();

        $this->purgedContentResource = $this
            ->getMockBuilder(\Magento\NegotiableQuote\Model\ResourceModel\PurgedContent::class)
            ->setMethods(['save'])
            ->disableOriginalConstructor()->getMock();

        $this->purgedContentFactory = $this->getMockBuilder(\Magento\NegotiableQuote\Model\PurgedContentFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()->getMock();

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->handler = $this->objectManagerHelper->getObject(
            \Magento\NegotiableQuote\Model\Purged\Handler::class,
            [
                'negotiableQuoteRepository' => $this->negotiableQuoteRepository,
                'negotiableQuoteManagement' => $this->negotiableQuoteManagement,
                'purgedContentResource' => $this->purgedContentResource,
                'purgedContentFactory' => $this->purgedContentFactory
            ]
        );
    }

    /**
     * Test process method.
     *
     * @return void
     */
    public function testProcess()
    {
        $contentToStore = [1, 2, 3];
        $userId = 45;
        $closeQuoteAfterProcessing = true;

        $quoteId = 23;
        $quote = $this->getMockBuilder(\Magento\Framework\DataObject::class)
            ->setMethods(['getId'])
            ->disableOriginalConstructor()->getMock();
        $quote->expects($this->exactly(3))->method('getId')->willReturn($quoteId);
        $quoteList = [$quote];

        $this->negotiableQuoteRepository->expects($this->exactly(1))
            ->method('getListByCustomerId')->willReturn($quoteList);

        $toParse = '{"test": "test"}';
        $purgedContent = $this->getMockBuilder(\Magento\NegotiableQuote\Model\PurgedContent::class)
            ->setMethods([
                'load',
                'getQuoteId',
                'setQuoteId',
                'getPurgedData',
                'setPurgedData'
            ])
            ->disableOriginalConstructor()->getMock();
        $purgedContent->expects($this->exactly(1))->method('load')->willReturnSelf();
        $purgedContent->expects($this->exactly(1))->method('getQuoteId')->willReturn(null);
        $purgedContent->expects($this->exactly(1))->method('setQuoteId')->willReturnSelf();
        $purgedContent->expects($this->exactly(2))->method('getPurgedData')->willReturn($toParse);
        $purgedContent->expects($this->exactly(1))->method('setPurgedData')->willReturnSelf();

        $this->purgedContentFactory->expects($this->exactly(1))->method('create')->willReturn($purgedContent);

        $this->purgedContentResource->expects($this->exactly(1))->method('save')->willReturnSelf();

        $closeStatus = true;
        $this->negotiableQuoteManagement->expects($this->exactly(1))->method('close')->willReturn($closeStatus);

        $this->handler->process($contentToStore, $userId, $closeQuoteAfterProcessing);
    }
}
