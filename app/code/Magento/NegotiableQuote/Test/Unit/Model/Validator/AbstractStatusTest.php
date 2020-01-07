<?php
namespace Magento\NegotiableQuote\Test\Unit\Model\Validator;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Base class for validators unit tests.
 */
abstract class AbstractStatusTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\NegotiableQuote\Model\Restriction\RestrictionInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $restriction;

    /**
     * @var \Magento\NegotiableQuote\Model\Validator\ValidatorResultFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $validatorResultFactory;

    /**
     * @var \Magento\NegotiableQuote\Model\Validator\CheckoutStatus
     */
    protected $status;

    /**
     * @var string
     */
    protected $statusClass;

    /**
     * @var array
     */
    protected $data = [];

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->restriction = $this
            ->getMockBuilder(\Magento\NegotiableQuote\Model\Restriction\RestrictionInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->validatorResultFactory = $this
            ->getMockBuilder(\Magento\NegotiableQuote\Model\Validator\ValidatorResultFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->status = $objectManagerHelper->getObject(
            $this->statusClass,
            [
                'restriction' => $this->restriction,
                'validatorResultFactory' => $this->validatorResultFactory,
            ]
        );
    }

    /**
     * Prepare mocks for validate().
     *
     * @return void
     */
    protected function prepareMocksForValidate()
    {
        $result = $this->getMockBuilder(\Magento\NegotiableQuote\Model\Validator\ValidatorResult::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->validatorResultFactory->expects($this->atLeastOnce())->method('create')->willReturn($result);
        $quote = $this->getMockBuilder(\Magento\Quote\Api\Data\CartInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $quote->expects($this->atLeastOnce())->method('getId')->willReturn(1);
        $this->data = ['quote' => $quote];

        $this->assertInstanceOf(
            \Magento\NegotiableQuote\Model\Validator\ValidatorResult::class,
            $this->status->validate($this->data)
        );
    }

    /**
     * Prepare mocks for validate() with empty quote.
     *
     * @return void
     */
    protected function prepareMocksForValidateWithEmptyQuoteData()
    {
        $result = $this->getMockBuilder(\Magento\NegotiableQuote\Model\Validator\ValidatorResult::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->validatorResultFactory->expects($this->atLeastOnce())->method('create')->willReturn($result);
    }
}
