<?php
namespace Magento\NegotiableQuote\Test\Unit\Model\Validator;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Unit test for Customer.
 */
class CustomerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Company\Api\CompanyManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $companyManagement;

    /**
     * @var \Magento\NegotiableQuote\Helper\Company|\PHPUnit_Framework_MockObject_MockObject
     */
    private $companyHelper;

    /**
     * @var \Magento\NegotiableQuote\Model\Validator\ValidatorResultFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $validatorResultFactory;

    /**
     * @var \Magento\NegotiableQuote\Model\Validator\Customer
     */
    private $customer;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->companyManagement = $this->getMockBuilder(\Magento\Company\Api\CompanyManagementInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->companyHelper = $this->getMockBuilder(\Magento\NegotiableQuote\Helper\Company::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->validatorResultFactory = $this
            ->getMockBuilder(\Magento\NegotiableQuote\Model\Validator\ValidatorResultFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->customer = $objectManagerHelper->getObject(
            \Magento\NegotiableQuote\Model\Validator\Customer::class,
            [
                'companyManagement' => $this->companyManagement,
                'companyHelper' => $this->companyHelper,
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
        $companyId = 1;
        $customerId = 2;
        $result = $this->getMockBuilder(\Magento\NegotiableQuote\Model\Validator\ValidatorResult::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->validatorResultFactory->expects($this->atLeastOnce())->method('create')->willReturn($result);
        $companyAttributes = $this->getMockBuilder(\Magento\Company\Api\Data\CompanyCustomerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $companyAttributes->expects($this->atLeastOnce())->method('getCompanyId')->willReturn($companyId);
        $extensionAttributes = $this->getMockBuilder(\Magento\Customer\Api\Data\CustomerExtensionInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCompanyAttributes'])
            ->getMockForAbstractClass();
        $extensionAttributes->expects($this->atLeastOnce())->method('getCompanyAttributes')
            ->willReturn($companyAttributes);
        $customer = $this->getMockBuilder(\Magento\Customer\Api\Data\CustomerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $customer->expects($this->atLeastOnce())->method('getExtensionAttributes')->willReturn($extensionAttributes);
        $customer->expects($this->atLeastOnce())->method('getId')->willReturn($customerId);
        $quote = $this->getMockBuilder(\Magento\Quote\Api\Data\CartInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $quote->expects($this->atLeastOnce())->method('getCustomer')->willReturn($customer);
        $quote->expects($this->atLeastOnce())->method('getId')->willReturn(1);
        $company = $this->getMockBuilder(\Magento\Company\Api\Data\CompanyInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->companyManagement->expects($this->atLeastOnce())->method('getByCustomerId')->with($customerId)
            ->willReturn($company);
        $quoteConfig = $this->getMockBuilder(\Magento\NegotiableQuote\Api\Data\CompanyQuoteConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $quoteConfig->expects($this->atLeastOnce())->method('getIsQuoteEnabled')->willReturn(false);
        $this->companyHelper->expects($this->atLeastOnce())->method('getQuoteConfig')->with($company)
            ->willReturn($quoteConfig);
        $result->expects($this->atLeastOnce())->method('addMessage')->willReturnSelf();

        $this->assertInstanceOf(
            \Magento\NegotiableQuote\Model\Validator\ValidatorResult::class,
            $this->customer->validate(['quote' => $quote])
        );
    }

    /**
     * Test validate() with empty customer company attributes.
     *
     * @return void
     */
    public function testValidateWithEmptyExtensionAttributes()
    {
        $result = $this->getMockBuilder(\Magento\NegotiableQuote\Model\Validator\ValidatorResult::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->validatorResultFactory->expects($this->atLeastOnce())->method('create')->willReturn($result);
        $customer = $this->getMockBuilder(\Magento\Customer\Api\Data\CustomerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $quote = $this->getMockBuilder(\Magento\Quote\Api\Data\CartInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $quote->expects($this->atLeastOnce())->method('getCustomer')->willReturn($customer);
        $quote->expects($this->atLeastOnce())->method('getId')->willReturn(1);
        $customer->expects($this->atLeastOnce())->method('getExtensionAttributes')->willReturn(null);
        $result->expects($this->atLeastOnce())->method('addMessage')->willReturnSelf();
        $this->companyManagement->expects($this->never())->method('getByCustomerId');

        $this->assertInstanceOf(
            \Magento\NegotiableQuote\Model\Validator\ValidatorResult::class,
            $this->customer->validate(['quote' => $quote])
        );
    }

    /**
     * Test validate() with empty quote.
     *
     * @return void
     */
    public function testValidateWithEmptyQuote()
    {
        $result = $this->getMockBuilder(\Magento\NegotiableQuote\Model\Validator\ValidatorResult::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->validatorResultFactory->expects($this->atLeastOnce())->method('create')->willReturn($result);

        $this->assertInstanceOf(
            \Magento\NegotiableQuote\Model\Validator\ValidatorResult::class,
            $this->customer->validate([])
        );
    }
}
