<?php

namespace Magento\Company\Test\Unit\Model\SaveValidator;

/**
 * Unit test for company status validator.
 */
class CompanyStatusTest extends \PHPUnit\Framework\TestCase
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
     * @var \Magento\Company\Model\SaveValidator\CompanyStatus
     */
    private $companyStatus;

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

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->companyStatus = $objectManager->getObject(
            \Magento\Company\Model\SaveValidator\CompanyStatus::class,
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
        $this->company->expects($this->once())
            ->method('getStatus')->willReturn(\Magento\Company\Api\Data\CompanyInterface::STATUS_APPROVED);
        $this->exception->expects($this->never())->method('addError');
        $this->companyStatus->execute();
    }

    /**
     * Test for execute method with invalid status.
     *
     * @return void
     */
    public function testExecuteWithInvalidStatus()
    {
        $status = -1;
        $this->company->expects($this->atLeastOnce())->method('getStatus')->willReturn($status);
        $this->exception->expects($this->once())->method('addError')->with(
            __(
                'Invalid value of "%value" provided for the %fieldName field.',
                ['fieldName' => 'status', 'value' => $status]
            )
        )->willReturnSelf();
        $this->companyStatus->execute();
    }
}
