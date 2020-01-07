<?php
namespace Magento\NegotiableQuote\Test\Unit\Model\Validator;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Unit test for NewQuote.
 */
class NewQuoteTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\NegotiableQuote\Model\Validator\ValidatorResultFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $validatorResultFactory;

    /**
     * @var \Magento\NegotiableQuote\Model\Validator\NewQuote
     */
    private $newQuote;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->validatorResultFactory = $this
            ->getMockBuilder(\Magento\NegotiableQuote\Model\Validator\ValidatorResultFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->newQuote = $objectManagerHelper->getObject(
            \Magento\NegotiableQuote\Model\Validator\NewQuote::class,
            [
                'validatorResultFactory' => $this->validatorResultFactory,
            ]
        );
    }

    /**
     * Test for validate().
     *
     * @return void
     */
    public function testValidate()
    {
        $result = $this->getMockBuilder(\Magento\NegotiableQuote\Model\Validator\ValidatorResult::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->validatorResultFactory->expects($this->atLeastOnce())->method('create')->willReturn($result);
        $negotiableQuote = $this->getMockBuilder(\Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $negotiableQuote->expects($this->atLeastOnce())->method('getIsRegularQuote')->willReturn(true);
        $extensionAttributes = $this->getMockBuilder(\Magento\Quote\Api\Data\CartExtensionInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getNegotiableQuote'])
            ->getMockForAbstractClass();
        $extensionAttributes->expects($this->atLeastOnce())->method('getNegotiableQuote')
            ->willReturn($negotiableQuote);
        $quote = $this->getMockBuilder(\Magento\Quote\Api\Data\CartInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $quote->expects($this->atLeastOnce())->method('getExtensionAttributes')->willReturn($extensionAttributes);
        $result->expects($this->atLeastOnce())->method('addMessage')->willReturnSelf();

        $this->assertInstanceOf(
            \Magento\NegotiableQuote\Model\Validator\ValidatorResult::class,
            $this->newQuote->validate(['quote' => $quote])
        );
    }

    /**
     * Test for validate() with empty quote data.
     *
     * @return void
     */
    public function testValidateWithEmptyQuote()
    {
        $result = $this->getMockBuilder(\Magento\NegotiableQuote\Model\Validator\ValidatorResult::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->validatorResultFactory->expects($this->atLeastOnce())->method('create')->willReturn($result);
        $quote = $this->getMockBuilder(\Magento\Quote\Api\Data\CartInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $quote->expects($this->never())->method('getExtensionAttributes');

        $this->assertInstanceOf(
            \Magento\NegotiableQuote\Model\Validator\ValidatorResult::class,
            $this->newQuote->validate([])
        );
    }
}
