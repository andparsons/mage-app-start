<?php
namespace Magento\NegotiableQuote\Test\Unit\Model\Validator;

use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterfaceFactory;
use Magento\NegotiableQuote\Model\Expiration;
use Magento\NegotiableQuote\Model\ResourceModel\NegotiableQuote;
use Magento\NegotiableQuote\Model\Validator\ValidatorResultFactory;
use Magento\NegotiableQuote\Model\Validator\ValidatorResult;

/**
 * Unit test for \Magento\NegotiableQuote\Model\Validator\ExpirationDate class.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ExpirationDateTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var \Magento\NegotiableQuote\Model\Validator\ExpirationDate
     */
    private $expirationDate;

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
     * @var TimezoneInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $localeDateMock;

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
        $this->localeDateMock = $this->getMockBuilder(TimezoneInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->resultMock = $this->getMockBuilder(ValidatorResult::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->validatorResultFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->resultMock);

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->expirationDate = $this->objectManagerHelper->getObject(
            \Magento\NegotiableQuote\Model\Validator\ExpirationDate::class,
            [
                'negotiableQuoteFactory' => $this->negotiableQuoteFactoryMock,
                'negotiableQuoteResource' => $this->negotiableQuoteResourceMock,
                'validatorResultFactory' => $this->validatorResultFactoryMock,
                'localeDate' => $this->localeDateMock
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
            $this->expirationDate->validate([])
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
            $this->expirationDate->validate([
                'quote' => $quoteMock
            ])
        );
    }

    /**
     * Test validate with incorrect date format.
     *
     * @return void
     */
    public function testValidateWithIncorrectDateFormat()
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
            ->method('getExpirationPeriod')
            ->willReturn(date('c'));
        $negotiableQuote->expects($this->atLeastOnce())
            ->method('getExpirationPeriod')
            ->willReturn('111111');

        $this->resultMock->expects($this->once())
            ->method('addMessage');

        $this->assertInstanceOf(
            \Magento\NegotiableQuote\Model\Validator\ValidatorResult::class,
            $this->expirationDate->validate([
                'quote' => $quoteMock
            ])
        );
    }

    /**
     * Test validate with never expire date.
     *
     * @return void
     */
    public function testValidateWithNeverExpireDate()
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
            ->method('getExpirationPeriod')
            ->willReturn(date('c'));
        $negotiableQuote->expects($this->atLeastOnce())
            ->method('getExpirationPeriod')
            ->willReturn(Expiration::DATE_QUOTE_NEVER_EXPIRES);

        $this->resultMock->expects($this->never())
            ->method('addMessage');

        $this->assertEquals(
            $this->resultMock,
            $this->expirationDate->validate([
                'quote' => $quoteMock
            ])
        );
    }

    /**
     * Test validate with incorrect date.
     *
     * @return void
     */
    public function testValidateWithIncorrectDate()
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
            ->method('getExpirationPeriod')
            ->willReturn('2000-1-1');
        $negotiableQuote->expects($this->atLeastOnce())
            ->method('getExpirationPeriod')
            ->willReturn('2000-2-1');

        $this->localeDateMock->expects($this->once())
            ->method('date')
            ->willReturn(new \DateTime('2000-03-01 00:00:00'));

        $this->resultMock->expects($this->once())
            ->method('addMessage');

        $this->assertInstanceOf(
            \Magento\NegotiableQuote\Model\Validator\ValidatorResult::class,
            $this->expirationDate->validate([
                'quote' => $quoteMock
            ])
        );
    }

    /**
     * Test validate.
     *
     * @return void
     */
    public function testValidate()
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
            ->method('getExpirationPeriod')
            ->willReturn('2000-1-1');
        $negotiableQuote->expects($this->atLeastOnce())
            ->method('getExpirationPeriod')
            ->willReturn('2000-2-1');

        $this->localeDateMock->expects($this->once())
            ->method('date')
            ->willReturn(new \DateTime('2000-02-01 00:00:00'));

        $this->resultMock->expects($this->never())
            ->method('addMessage');

        $this->assertInstanceOf(
            \Magento\NegotiableQuote\Model\Validator\ValidatorResult::class,
            $this->expirationDate->validate([
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
