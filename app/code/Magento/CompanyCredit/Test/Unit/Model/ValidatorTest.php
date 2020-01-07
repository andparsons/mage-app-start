<?php

namespace Magento\CompanyCredit\Test\Unit\Model;

use Magento\CompanyCredit\Api\CreditLimitManagementInterface;
use Magento\CompanyCredit\Model\Validator;
use Magento\CompanyCredit\Model\WebsiteCurrency;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Unit test for Validator model.
 */
class ValidatorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var Validator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $validator;

    /**
     * @var WebsiteCurrency|\PHPUnit_Framework_MockObject_MockObject
     */
    private $websiteCurrencyMock;

    /**
     * @var CreditLimitManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $creditLimitManagementMock;

    /**
     * @inheritdoc
     */
    public function setUp()
    {
        $this->websiteCurrencyMock = $this->getMockBuilder(WebsiteCurrency::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->creditLimitManagementMock = $this->getMockBuilder(CreditLimitManagementInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->validator = $this->objectManagerHelper->getObject(
            Validator::class,
            [
                'websiteCurrency' => $this->websiteCurrencyMock,
                'creditLimitManagement' => $this->creditLimitManagementMock
            ]
        );
    }

    /**
     * Test for validateCreditData method.
     *
     * @return void
     */
    public function testValidateCreditData()
    {
        $creditData = [
            'entity_id' => 1,
            'company_id' => 2,
            'currency_code' => 'USD',
            'credit_limit' => 500,
        ];
        $creditLimit = $this->getMockBuilder(\Magento\CompanyCredit\Api\Data\CreditLimitInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->creditLimitManagementMock->expects($this->once())
            ->method('getCreditByCompanyId')->with($creditData['company_id'])->willReturn($creditLimit);
        $creditLimit->expects($this->once())->method('getId')->willReturn($creditData['entity_id']);
        $this->websiteCurrencyMock->expects($this->once())
            ->method('isCreditCurrencyEnabled')->with($creditData['currency_code'])->willReturn(true);
        $this->validator->validateCreditData($creditData);
    }

    /**
     * Test for validateCreditData method without company id.
     *
     * @return void
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage "company_id" is required. Enter and try again.
     */
    public function testValidateCreditDataWithoutCompanyId()
    {
        $this->validator->validateCreditData([]);
    }

    /**
     * Test for validateCreditData method without currency code.
     *
     * @return void
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage "currency_code" is required. Enter and try again.
     */
    public function testValidateCreditDataWithoutCurrencyCode()
    {
        $this->validator->validateCreditData(['company_id' => 1]);
    }

    /**
     * Test for validateCreditData method with different id.
     *
     * @return void
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage Invalid value of "2" provided for the company_id field.
     */
    public function testValidateCreditDataWithDifferentId()
    {
        $creditData = [
            'entity_id' => 1,
            'company_id' => 2,
            'currency_code' => 'USD',
        ];
        $creditLimit = $this->getMockBuilder(\Magento\CompanyCredit\Api\Data\CreditLimitInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->creditLimitManagementMock->expects($this->once())
            ->method('getCreditByCompanyId')->with($creditData['company_id'])->willReturn($creditLimit);
        $creditLimit->expects($this->once())->method('getId')->willReturn(2);
        $this->validator->validateCreditData($creditData);
    }

    /**
     * Test for validateCreditData method with inactive currency.
     *
     * @return void
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage Invalid attribute value. Row ID: currency_code = USD.
     */
    public function testValidateCreditDataWithInactiveCurrency()
    {
        $creditData = [
            'entity_id' => 1,
            'company_id' => 2,
            'currency_code' => 'USD',
        ];
        $creditLimit = $this->getMockBuilder(\Magento\CompanyCredit\Api\Data\CreditLimitInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->creditLimitManagementMock->expects($this->once())
            ->method('getCreditByCompanyId')->with($creditData['company_id'])->willReturn($creditLimit);
        $creditLimit->expects($this->once())->method('getId')->willReturn($creditData['entity_id']);
        $this->websiteCurrencyMock->expects($this->once())
            ->method('isCreditCurrencyEnabled')->with($creditData['currency_code'])->willReturn(false);
        $this->validator->validateCreditData($creditData);
    }

    /**
     * Test for validateCreditData method with invalid limit.
     *
     * @return void
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage Invalid attribute value. Row ID: credit_limit = -100.
     */
    public function testValidateCreditDataWithInvalidLimit()
    {
        $creditData = [
            'entity_id' => 1,
            'company_id' => 2,
            'currency_code' => 'USD',
            'credit_limit' => -100,
        ];
        $creditLimit = $this->getMockBuilder(\Magento\CompanyCredit\Api\Data\CreditLimitInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->creditLimitManagementMock->expects($this->once())
            ->method('getCreditByCompanyId')->with($creditData['company_id'])->willReturn($creditLimit);
        $creditLimit->expects($this->once())->method('getId')->willReturn($creditData['entity_id']);
        $this->websiteCurrencyMock->expects($this->once())
            ->method('isCreditCurrencyEnabled')->with($creditData['currency_code'])->willReturn(true);
        $this->validator->validateCreditData($creditData);
    }

    /**
     * Test for checkCompanyCreditExist method.
     *
     * @return void
     */
    public function testCheckCompanyCreditExist()
    {
        $creditLimit = $this->getMockBuilder(\Magento\CompanyCredit\Api\Data\CreditLimitInterface::class)
            ->disableOriginalConstructor()->getMock();
        $creditLimit->expects($this->once())->method('getId')->willReturn(1);
        $this->validator->checkCompanyCreditExist($creditLimit, 1);
    }

    /**
     * Test for checkCompanyCreditExist method with exception.
     *
     * @return void
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage Requested company is not found. Row ID: CompanyCreditID = 1.
     */
    public function testCheckCompanyCreditExistWithException()
    {
        $creditLimit = $this->getMockBuilder(\Magento\CompanyCredit\Api\Data\CreditLimitInterface::class)
            ->disableOriginalConstructor()->getMock();
        $creditLimit->expects($this->once())->method('getId')->willReturn(null);
        $this->validator->checkCompanyCreditExist($creditLimit, 1);
    }
}
