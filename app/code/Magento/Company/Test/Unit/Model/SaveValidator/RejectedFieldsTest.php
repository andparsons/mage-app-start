<?php

namespace Magento\Company\Test\Unit\Model\SaveValidator;

/**
 * Unit test for rejected fields validator.
 */
class RejectedFieldsTest extends \PHPUnit\Framework\TestCase
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
     * @var \Magento\Company\Api\Data\CompanyInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $initialCompany;

    /**
     * @var \Magento\Company\Model\SaveValidator\RejectedFields
     */
    private $rejectedFields;

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
        $this->initialCompany = $this->getMockBuilder(\Magento\Company\Api\Data\CompanyInterface::class)
            ->disableOriginalConstructor()->getMock();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->rejectedFields = $objectManager->getObject(
            \Magento\Company\Model\SaveValidator\RejectedFields::class,
            [
                'company' => $this->company,
                'exception' => $this->exception,
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
        $rejectedAt = '2017-03-17 17:32:58';
        $rejectReason = 'Lorem ipsum dolor sit amet';
        $this->company->expects($this->once())->method('getRejectedAt')->willReturn($rejectedAt);
        $this->initialCompany->expects($this->once())->method('getRejectedAt')->willReturn($rejectedAt);
        $this->company->expects($this->once())->method('getRejectReason')->willReturn($rejectReason);
        $this->initialCompany->expects($this->once())->method('getRejectReason')->willReturn($rejectReason);
        $this->exception->expects($this->never())->method('addError');
        $this->rejectedFields->execute();
    }

    /**
     * Test for execute method with error.
     *
     * @return void
     */
    public function testExecuteWithError()
    {
        $rejectedAt = '2017-03-17 17:32:58';
        $rejectReason = 'Lorem ipsum dolor sit amet';
        $this->company->expects($this->once())->method('getRejectedAt')->willReturn($rejectedAt);
        $this->initialCompany->expects($this->once())->method('getRejectedAt')->willReturn($rejectedAt);
        $this->company->expects($this->once())->method('getRejectReason')->willReturn($rejectReason);
        $this->initialCompany->expects($this->once())->method('getRejectReason')->willReturn('Some reject reason');
        $this->company->expects($this->once())
            ->method('getStatus')->willReturn(\Magento\Company\Api\Data\CompanyInterface::STATUS_REJECTED);
        $this->initialCompany->expects($this->once())
            ->method('getStatus')->willReturn(\Magento\Company\Api\Data\CompanyInterface::STATUS_REJECTED);
        $this->exception->expects($this->once())->method('addError')->with(
            __(
                'Invalid attribute value. Rejected date&time and Rejected Reason can be changed only'
                . ' when a company status is changed to Rejected.'
            )
        )->willReturnSelf();
        $this->rejectedFields->execute();
    }
}
