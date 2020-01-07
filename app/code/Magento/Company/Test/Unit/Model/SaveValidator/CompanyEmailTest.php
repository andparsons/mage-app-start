<?php

namespace Magento\Company\Test\Unit\Model\SaveValidator;

/**
 * Unit test for company email validator.
 */
class CompanyEmailTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Company\Api\Data\CompanyInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $company;

    /**
     * @var \Magento\Framework\Exception\InputException|\PHPUnit_Framework_MockObject_MockObject
     */
    private $exception;

    /**
     * @var \Zend\Validator\EmailAddress|\PHPUnit_Framework_MockObject_MockObject
     */
    private $emailValidator;

    /**
     * @var \Magento\Company\Model\ResourceModel\Company\CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $companyCollectionFactory;

    /**
     * @var \Magento\Company\Api\Data\CompanyInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $initialCompany;

    /**
     * @var \Magento\Company\Model\SaveValidator\CompanyEmail
     */
    private $companyEmail;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->company = $this->getMockBuilder(\Magento\Company\Api\Data\CompanyInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->exception = $this->getMockBuilder(\Magento\Framework\Exception\InputException::class)
            ->disableOriginalConstructor()->getMock();
        $this->emailValidator = $this->getMockBuilder(\Zend\Validator\EmailAddress::class)
            ->disableOriginalConstructor()->getMock();
        $this->companyCollectionFactory = $this
            ->getMockBuilder(\Magento\Company\Model\ResourceModel\Company\CollectionFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()->getMock();
        $this->initialCompany = $this->getMockBuilder(\Magento\Company\Api\Data\CompanyInterface::class)
            ->disableOriginalConstructor()->getMock();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->companyEmail = $objectManager->getObject(
            \Magento\Company\Model\SaveValidator\CompanyEmail::class,
            [
                'company' => $this->company,
                'exception' => $this->exception,
                'emailValidator' => $this->emailValidator,
                'companyCollectionFactory' => $this->companyCollectionFactory,
                'initialCompany' => $this->initialCompany,
            ]
        );
    }

    /**
     * Test for execute method.
     *
     * @return void
     */
    public function testExecute()
    {
        $companyId = 1;
        $companyEmail = 'company1@example.com';
        $this->company->expects($this->atLeastOnce())->method('getCompanyEmail')->willReturn($companyEmail);
        $this->emailValidator->expects($this->once())->method('isValid')->with($companyEmail)->willReturn(true);
        $this->company->expects($this->once())->method('getId')->willReturn($companyId);
        $this->initialCompany->expects($this->once())->method('getCompanyEmail')->willReturn('company2@example.com');
        $collection = $this->getMockBuilder(\Magento\Company\Model\ResourceModel\Company\Collection::class)
            ->disableOriginalConstructor()->getMock();
        $this->companyCollectionFactory->expects($this->once())->method('create')->willReturn($collection);
        $collection->expects($this->once())->method('addFieldToFilter')->with(
            \Magento\Company\Api\Data\CompanyInterface::COMPANY_EMAIL,
            $companyEmail
        )->willReturnSelf();
        $collection->expects($this->once())->method('load')->willReturnSelf();
        $collection->expects($this->once())->method('getSize')->willReturn(0);
        $this->exception->expects($this->never())->method('addError');
        $this->companyEmail->execute();
    }

    /**
     * Test for execute method with invalid email address.
     *
     * @return void
     */
    public function testExecuteWithInvalidEmailAddress()
    {
        $companyEmail = 'company1@example';
        $this->company->expects($this->atLeastOnce())->method('getCompanyEmail')->willReturn($companyEmail);
        $this->emailValidator->expects($this->once())->method('isValid')->with($companyEmail)->willReturn(false);
        $this->exception->expects($this->once())->method('addError')->with(
            __(
                'Invalid value of "%value" provided for the %fieldName field.',
                ['fieldName' => 'company_email', 'value' => $companyEmail]
            )
        )->willReturnSelf();
        $this->companyEmail->execute();
    }

    /**
     * Test for execute method with non-unique email address.
     *
     * @return void
     */
    public function testExecuteWithNonUniqueEmailAddress()
    {
        $companyId = 1;
        $companyEmail = 'company1@example.com';
        $this->company->expects($this->atLeastOnce())->method('getCompanyEmail')->willReturn($companyEmail);
        $this->emailValidator->expects($this->once())->method('isValid')->with($companyEmail)->willReturn(true);
        $this->company->expects($this->once())->method('getId')->willReturn($companyId);
        $this->initialCompany->expects($this->once())->method('getCompanyEmail')->willReturn('company2@example.com');
        $collection = $this->getMockBuilder(\Magento\Company\Model\ResourceModel\Company\Collection::class)
            ->disableOriginalConstructor()->getMock();
        $this->companyCollectionFactory->expects($this->once())->method('create')->willReturn($collection);
        $collection->expects($this->once())->method('addFieldToFilter')->with(
            \Magento\Company\Api\Data\CompanyInterface::COMPANY_EMAIL,
            $companyEmail
        )->willReturnSelf();
        $collection->expects($this->once())->method('load')->willReturnSelf();
        $collection->expects($this->once())->method('getSize')->willReturn(1);
        $this->exception->expects($this->once())->method('addError')->with(
            __(
                'Company with this email address already exists in the system.'
                . ' Enter a different email address to continue.'
            )
        )->willReturnSelf();
        $this->companyEmail->execute();
    }
}
