<?php

namespace Magento\Company\Test\Unit\Model\SaveValidator;

/**
 * Unit test for company id validator.
 */
class CompanyIdTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Company\Api\Data\CompanyInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $company;

    /**
     * @var \Magento\Company\Api\Data\CompanyInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $initialCompany;

    /**
     * @var \Magento\Company\Model\SaveValidator\CompanyId
     */
    private $companyId;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->company = $this->getMockBuilder(\Magento\Company\Api\Data\CompanyInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->initialCompany = $this->getMockBuilder(\Magento\Company\Api\Data\CompanyInterface::class)
            ->disableOriginalConstructor()->getMock();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->companyId = $objectManager->getObject(
            \Magento\Company\Model\SaveValidator\CompanyId::class,
            [
                'company' => $this->company,
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
        $this->company->expects($this->once())->method('getId')->willReturn($companyId);
        $this->initialCompany->expects($this->once())->method('getId')->willReturn($companyId);
        $this->companyId->execute();
    }

    /**
     * Test for execute method with exception.
     *
     * @return void
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage No such entity with companyId = 1
     */
    public function testExecuteWithException()
    {
        $this->company->expects($this->atLeastOnce())->method('getId')->willReturn(1);
        $this->initialCompany->expects($this->once())->method('getId')->willReturn(null);
        $this->companyId->execute();
    }
}
