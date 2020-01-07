<?php

namespace Magento\NegotiableQuote\Test\Unit\Model\Webapi;

use Magento\Authorization\Model\UserContextInterface;

/**
 * Class CustomerCartValidatorTest
 */
class CustomerCartValidatorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Authorization\Model\UserContextInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $userContext;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteRepository;

    /**
     * @var \Magento\NegotiableQuote\Model\Webapi\CustomerCartValidator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerCartValidator;

    /**
     * Set up
     */
    protected function setUp()
    {
        $this->userContext = $this->createMock(\Magento\Authorization\Model\UserContextInterface::class);
        $this->quoteRepository = $this->createMock(\Magento\Quote\Api\CartRepositoryInterface::class);
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->customerCartValidator = $objectManager->getObject(
            \Magento\NegotiableQuote\Model\Webapi\CustomerCartValidator::class,
            [
                'userContext' => $this->userContext,
                'quoteRepository' => $this->quoteRepository
            ]
        );
    }

    /**
     * Test validate
     *
     * @param int $customerId
     * @param int $userType
     * @param int $quoteCustomerId
     * @dataProvider validateDataProvider
     */
    public function testValidate($customerId, $userType, $quoteCustomerId)
    {
        $this->prepareMockData($customerId, $userType, $quoteCustomerId);

        $this->assertNull($this->customerCartValidator->validate(1));
    }

    /**
     * Test validateWithException
     *
     * @param int $customerId
     * @param int $userType
     * @param int $quoteCustomerId
     * @dataProvider validateWithExceptionDataProvider
     * @expectedException \Magento\Framework\Exception\SecurityViolationException
     */
    public function testValidateWithException($customerId, $userType, $quoteCustomerId)
    {
        $this->prepareMockData($customerId, $userType, $quoteCustomerId);

        $this->customerCartValidator->validate(1);
    }

    /**
     * validate dataProvider
     *
     * @return array
     */
    public function validateDataProvider()
    {
        return [
            [1, UserContextInterface::USER_TYPE_CUSTOMER, 1]
        ];
    }

    /**
     * testValidateWithException dataProvider
     *
     * @return array
     */
    public function validateWithExceptionDataProvider()
    {
        return [
            [1, UserContextInterface::USER_TYPE_CUSTOMER, 2],
            [1, UserContextInterface::USER_TYPE_GUEST, 1],
            [1, UserContextInterface::USER_TYPE_GUEST, 2]
        ];
    }

    /**
     * @param int $customerId
     * @param int $userType
     * @param int $quoteCustomerId
     */
    private function prepareMockData($customerId, $userType, $quoteCustomerId)
    {
        $this->userContext->expects($this->any())->method('getUserType')->willReturn($userType);
        $this->userContext->expects($this->any())->method('getUserId')->willReturn($customerId);
        $customer = $this->createMock(\Magento\Customer\Api\Data\CustomerInterface::class);
        $customer->expects($this->any())->method('getId')->willReturn($quoteCustomerId);
        $quote = $this->createMock(\Magento\Quote\Api\Data\CartInterface::class);
        $quote->expects($this->any())->method('getCustomer')->willReturn($customer);
        $this->quoteRepository->expects($this->any())->method('get')->willReturn($quote);
    }
}
