<?php
namespace Magento\NegotiableQuote\Test\Unit\Model\Validator;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface;
use Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterfaceFactory;
use Magento\NegotiableQuote\Model\ResourceModel\NegotiableQuote;
use Magento\NegotiableQuote\Model\Validator\ValidatorResultFactory;
use Magento\NegotiableQuote\Model\Validator\ValidatorResult;

/**
 * Unit test for \Magento\NegotiableQuote\Model\Validator\NegotiableStatusChange class.
 */
class NegotiableStatusChangeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var \Magento\NegotiableQuote\Model\Validator\NegotiableStatusChange
     */
    private $negotiableStatusChange;

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
        $this->negotiableStatusChange = $this->objectManagerHelper->getObject(
            \Magento\NegotiableQuote\Model\Validator\NegotiableStatusChange::class,
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
            $this->negotiableStatusChange->validate([])
        );
    }

    /**
     * Test validate without negotiable quote instance.
     *
     * @return void
     */
    public function testValidateWithoutNegotiableQuote()
    {
        $negotiableQuote = $this->getMockBuilder(\Magento\NegotiableQuote\Model\NegotiableQuote::class)
            ->disableOriginalConstructor()
            ->getMock();
        $negotiableQuote->expects($this->atLeastOnce())
            ->method('getIsRegularQuote')
            ->willReturn(true);
        $quoteMock = $this->buildQuoteMock($negotiableQuote);

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
            $this->negotiableStatusChange->validate([
                'quote' => $quoteMock
            ])
        );
    }

    /**
     * Test validate with incorrect status.
     *
     * @return void
     */
    public function testValidateWithIncorrectStatus()
    {
        $negotiableQuote = $this->getMockBuilder(\Magento\NegotiableQuote\Model\NegotiableQuote::class)
            ->disableOriginalConstructor()
            ->getMock();
        $negotiableQuote->expects($this->atLeastOnce())
            ->method('getIsRegularQuote')
            ->willReturn(true);
        $quoteMock = $this->buildQuoteMock($negotiableQuote);

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
            ->method('getStatus')
            ->willReturn(NegotiableQuoteInterface::STATUS_SUBMITTED_BY_CUSTOMER);
        $negotiableQuote->expects($this->atLeastOnce())
            ->method('hasData')
            ->willReturn(true);
        $negotiableQuote->expects($this->atLeastOnce())
            ->method('getStatus')
            ->willReturn(NegotiableQuoteInterface::STATUS_ORDERED);

        $this->resultMock->expects($this->once())
            ->method('addMessage');

        $this->assertInstanceOf(
            \Magento\NegotiableQuote\Model\Validator\ValidatorResult::class,
            $this->negotiableStatusChange->validate([
                'quote' => $quoteMock
            ])
        );
    }

    /**
     * Build quote mock.
     *
     * @param \PHPUnit_Framework_MockObject_MockObject $negotiableQuoteMock
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function buildQuoteMock(\PHPUnit_Framework_MockObject_MockObject $negotiableQuoteMock)
    {
        $extensionAttributes = $this->getMockBuilder(\Magento\Quote\Api\Data\CartExtensionInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getNegotiableQuote'])
            ->getMockForAbstractClass();
        $extensionAttributes->expects($this->atLeastOnce())
            ->method('getNegotiableQuote')
            ->willReturn($negotiableQuoteMock);
        $quoteMock = $this->getMockBuilder(\Magento\Quote\Api\Data\CartInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $quoteMock->expects($this->atLeastOnce())
            ->method('getExtensionAttributes')
            ->willReturn($extensionAttributes);

        return $quoteMock;
    }
}
