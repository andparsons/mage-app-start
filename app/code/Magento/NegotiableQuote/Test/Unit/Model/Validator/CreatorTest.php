<?php
namespace Magento\NegotiableQuote\Test\Unit\Model\Validator;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterfaceFactory;
use Magento\NegotiableQuote\Model\ResourceModel\NegotiableQuote;
use Magento\NegotiableQuote\Model\Validator\ValidatorResultFactory;
use Magento\NegotiableQuote\Model\Validator\ValidatorResult;

/**
 * Unit test for \Magento\NegotiableQuote\Model\Validator\Creator class.
 */
class CreatorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var \Magento\NegotiableQuote\Model\Validator\Creator
     */
    private $creator;

    /**
     * @var NegotiableQuoteInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $negotiableQuoteFactoryMock;

    /**
     * @var NegotiableQuote|\PHPUnit_Framework_MockObject_MockObject
     */
    private $negotiableQuoteResourceMock;

    /**
     * @var ValidatorResultFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $validatorResultFactoryMock;

    /**
     * @var ValidatorResult|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultMock;

    /**
     * @inheritdoc
     */
    public function setUp()
    {
        $this->negotiableQuoteFactoryMock = $this->getMockBuilder(NegotiableQuoteInterfaceFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->negotiableQuoteResourceMock = $this->getMockBuilder(NegotiableQuote::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->validatorResultFactoryMock = $this->getMockBuilder(ValidatorResultFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->resultMock = $this->getMockBuilder(ValidatorResult::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->validatorResultFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->resultMock);

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->creator = $this->objectManagerHelper->getObject(
            \Magento\NegotiableQuote\Model\Validator\Creator::class,
            [
                'negotiableQuoteFactory' => $this->negotiableQuoteFactoryMock,
                'negotiableQuoteResource' => $this->negotiableQuoteResourceMock,
                'validatorResultFactory' => $this->validatorResultFactoryMock
            ]
        );
    }

    /**
     * Test validate without any quote.
     *
     * @return void
     */
    public function testValidateWithoutAnyQuote()
    {
        $this->resultMock->expects($this->never())
            ->method('addMessage');
        $this->assertInstanceOf(
            \Magento\NegotiableQuote\Model\Validator\ValidatorResult::class,
            $this->creator->validate([])
        );
    }

    /**
     * Test validate without negotiable quote instance.
     *
     * @return void
     */
    public function testValidateWithoutNegotiableQuote()
    {
        $quoteMock = $this->buildQuoteMock([]);
        $oldNegotiableQuote = $this->getMockBuilder(\Magento\NegotiableQuote\Model\NegotiableQuote::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->negotiableQuoteFactoryMock->expects($this->atLeastOnce())
            ->method('create')
            ->willReturn($oldNegotiableQuote);
        $this->negotiableQuoteResourceMock->expects($this->atLeastOnce())
            ->method('load')
            ->willReturn($oldNegotiableQuote);

        $this->resultMock->expects($this->never())
            ->method('addMessage');

        $this->assertInstanceOf(
            \Magento\NegotiableQuote\Model\Validator\ValidatorResult::class,
            $this->creator->validate([
                'quote' => $quoteMock
            ])
        );
    }

    /**
     * Test validate with errors.
     *
     * @return void
     */
    public function testValidateWithErrors()
    {
        $quoteMock = $this->buildQuoteMock([
            'creator_id' => 1,
            'creator_type' => 2
        ]);
        $oldNegotiableQuote = $this->getMockBuilder(\Magento\NegotiableQuote\Model\NegotiableQuote::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->negotiableQuoteFactoryMock->expects($this->atLeastOnce())
            ->method('create')
            ->willReturn($oldNegotiableQuote);
        $this->negotiableQuoteResourceMock->expects($this->atLeastOnce())
            ->method('load')
            ->willReturn($oldNegotiableQuote);

        $oldNegotiableQuote->expects($this->atLeastOnce())
            ->method('getCreatorId')
            ->willReturn(2);
        $oldNegotiableQuote->expects($this->atLeastOnce())
            ->method('getCreatorType')
            ->willReturn(3);

        $this->resultMock->expects($this->exactly(2))
            ->method('addMessage');

        $this->assertInstanceOf(
            \Magento\NegotiableQuote\Model\Validator\ValidatorResult::class,
            $this->creator->validate([
                'quote' => $quoteMock
            ])
        );
    }

    /**
     * Build quote mock.
     *
     * @param array $negotiableQuoteData
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function buildQuoteMock(array $negotiableQuoteData)
    {
        $negotiableQuote = $this->getMockBuilder(\Magento\NegotiableQuote\Model\NegotiableQuote::class)
            ->disableOriginalConstructor()
            ->getMock();
        $negotiableQuote->expects($this->atLeastOnce())
            ->method('getIsRegularQuote')
            ->willReturn(true);
        $negotiableQuote->expects($this->atLeastOnce())
            ->method('getData')
            ->willReturn($negotiableQuoteData);
        $extensionAttributes = $this->getMockBuilder(\Magento\Quote\Api\Data\CartExtensionInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getNegotiableQuote'])
            ->getMockForAbstractClass();
        $extensionAttributes->expects($this->atLeastOnce())
            ->method('getNegotiableQuote')
            ->willReturn($negotiableQuote);
        $quoteMock = $this->getMockBuilder(\Magento\Quote\Api\Data\CartInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $quoteMock->expects($this->atLeastOnce())
            ->method('getExtensionAttributes')
            ->willReturn($extensionAttributes);

        return $quoteMock;
    }
}
