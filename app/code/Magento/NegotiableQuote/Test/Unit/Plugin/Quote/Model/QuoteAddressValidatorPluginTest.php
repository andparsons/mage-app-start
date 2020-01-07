<?php

namespace Magento\NegotiableQuote\Test\Unit\Plugin\Quote\Model;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Class QuoteAddressValidatorPluginTest.
 */
class QuoteAddressValidatorPluginTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var \Magento\NegotiableQuote\Plugin\Quote\Model\QuoteAddressValidatorPlugin
     * |\PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteAddressValidatorPlugin;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerRepository;

    /**
     * @var \Magento\Quote\Model\QuoteAddressValidator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $subject;

    /**
     * @var \Magento\Quote\Api\Data\AddressInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $addressData;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->customerRepository = $this->getMockBuilder(\Magento\Customer\Api\CustomerRepositoryInterface::class)
            ->setMethods(['getById'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->subject = $this->getMockBuilder(\Magento\Quote\Model\QuoteAddressValidator::class)
            ->disableOriginalConstructor()->getMock();

        $this->addressData = $this->getMockBuilder(\Magento\Quote\Api\Data\AddressInterface::class)
            ->setMethods(['getCustomerId'])
            ->disableOriginalConstructor()->getMockForAbstractClass();

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->quoteAddressValidatorPlugin = $this->objectManagerHelper->getObject(
            \Magento\NegotiableQuote\Plugin\Quote\Model\QuoteAddressValidatorPlugin::class,
            [
                'customerRepository' => $this->customerRepository
            ]
        );
    }

    /**
     * Test aroundValidate method.
     *
     * @return void
     */
    public function testAroundValidate()
    {
        $expected = false;

        $closure = function () use ($expected) {
            return $expected;
        };

        $customerId = 364;
        $this->addressData->expects($this->exactly(1))->method('getCustomerId')->willReturn($customerId);

        $customer = $this->getMockBuilder(\Magento\Customer\Api\Data\CustomerInterface::class)
            ->disableOriginalConstructor()->getMockForAbstractClass();

        $this->customerRepository->expects($this->exactly(1))->method('getById')->willReturn($customer);

        $methodCallResult = $this->quoteAddressValidatorPlugin
            ->aroundValidate($this->subject, $closure, $this->addressData);

        $this->assertEquals($expected, $methodCallResult);
    }

    /**
     * Test aroundValidate method with Exception.
     *
     * @return void
     */
    public function testAroundValidateWithException()
    {
        $expected = true;

        $closure = function () {
        };

        $phrase = new \Magento\Framework\Phrase('message');
        $exception = new \Magento\Framework\Exception\NoSuchEntityException($phrase);
        $this->customerRepository->expects($this->exactly(1))->method('getById')->willThrowException($exception);

        $methodCallResult = $this->quoteAddressValidatorPlugin
            ->aroundValidate($this->subject, $closure, $this->addressData);

        $this->assertEquals($expected, $methodCallResult);
    }
}
