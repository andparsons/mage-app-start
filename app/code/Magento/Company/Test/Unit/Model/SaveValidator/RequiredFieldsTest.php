<?php

namespace Magento\Company\Test\Unit\Model\SaveValidator;

use \Magento\Company\Api\Data\CompanyInterface;

/**
 * Unit test for required fields validator.
 */
class RequiredFieldsTest extends \PHPUnit\Framework\TestCase
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
     * @var \Magento\Company\Model\SaveValidator\RequiredFields
     */
    private $requiredFields;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->company = $this->getMockBuilder(\Magento\Company\Api\Data\CompanyInterface::class)
            ->setMethods(['getData'])
            ->disableOriginalConstructor()->getMockForAbstractClass();
        $this->exception = $this->getMockBuilder(\Magento\Framework\Exception\InputException::class)
            ->disableOriginalConstructor()->getMock();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->requiredFields = $objectManager->getObject(
            \Magento\Company\Model\SaveValidator\RequiredFields::class,
            [
                'company' => $this->company,
                'exception' => $this->exception,
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
        $this->company->expects($this->exactly(9))->method('getData')->withConsecutive(
            [CompanyInterface::NAME],
            [CompanyInterface::COMPANY_EMAIL],
            [CompanyInterface::STREET],
            [CompanyInterface::CITY],
            [CompanyInterface::POSTCODE],
            [CompanyInterface::TELEPHONE],
            [CompanyInterface::COUNTRY_ID],
            [CompanyInterface::SUPER_USER_ID],
            [CompanyInterface::CUSTOMER_GROUP_ID]
        )->willReturn('some value');
        $this->exception->expects($this->never())->method('addError');
        $this->requiredFields->execute();
    }

    /**
     * Test for execute with errors.
     *
     * @return void
     */
    public function testExecuteWithErrors()
    {
        $this->company->expects($this->exactly(9))->method('getData')->withConsecutive(
            [CompanyInterface::NAME],
            [CompanyInterface::COMPANY_EMAIL],
            [CompanyInterface::STREET],
            [CompanyInterface::CITY],
            [CompanyInterface::POSTCODE],
            [CompanyInterface::TELEPHONE],
            [CompanyInterface::COUNTRY_ID],
            [CompanyInterface::SUPER_USER_ID],
            [CompanyInterface::CUSTOMER_GROUP_ID]
        )->willReturn(null);
        $this->exception->expects($this->exactly(9))->method('addError')->withConsecutive(
            __(
                '"%fieldName" is required. Enter and try again."%fieldName" is required. Enter and try again.',
                ['fieldName' => CompanyInterface::NAME]
            ),
            __('"%fieldName" is required. Enter and try again.', ['fieldName' => CompanyInterface::COMPANY_EMAIL]),
            __('"%fieldName" is required. Enter and try again.', ['fieldName' => CompanyInterface::STREET]),
            __('"%fieldName" is required. Enter and try again.', ['fieldName' => CompanyInterface::CITY]),
            __('"%fieldName" is required. Enter and try again.', ['fieldName' => CompanyInterface::POSTCODE]),
            __('"%fieldName" is required. Enter and try again.', ['fieldName' => CompanyInterface::TELEPHONE]),
            __('"%fieldName" is required. Enter and try again.', ['fieldName' => CompanyInterface::COUNTRY_ID]),
            __('"%fieldName" is required. Enter and try again.', ['fieldName' => CompanyInterface::SUPER_USER_ID]),
            __('"%fieldName" is required. Enter and try again.', ['fieldName' => CompanyInterface::CUSTOMER_GROUP_ID])
        )->willReturnSelf();
        $this->requiredFields->execute();
    }
}
