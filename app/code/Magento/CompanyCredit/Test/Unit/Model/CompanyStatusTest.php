<?php

namespace Magento\CompanyCredit\Test\Unit\Model;

/**
 * Class CompanyStatusTest.
 */
class CompanyStatusTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Company\Api\CompanyRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $companyRepository;

    /**
     * @var \Magento\CompanyCredit\Model\CompanyStatus
     */
    private $companyStatus;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->companyRepository = $this->createMock(
            \Magento\Company\Api\CompanyRepositoryInterface::class
        );

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->companyStatus = $objectManager->getObject(
            \Magento\CompanyCredit\Model\CompanyStatus::class,
            [
                'companyRepository' => $this->companyRepository,
            ]
        );
    }

    /**
     * Test for method isRefundAvailable.
     *
     * @return void
     */
    public function testIsRefundAvailable()
    {
        $companyId = 1;
        $company = $this->createMock(\Magento\Company\Api\Data\CompanyInterface::class);
        $this->companyRepository->expects($this->once())->method('get')->with($companyId)->willReturn($company);
        $company->expects($this->once())
            ->method('getStatus')->willReturn(\Magento\Company\Api\Data\CompanyInterface::STATUS_APPROVED);
        $this->assertTrue($this->companyStatus->isRefundAvailable($companyId));
    }

    /**
     * Test for method isRefundAvailable with rejected company.
     *
     * @return void
     */
    public function testIsRefundAvailableWithRejectedCompany()
    {
        $companyId = 1;
        $company = $this->createMock(\Magento\Company\Api\Data\CompanyInterface::class);
        $this->companyRepository->expects($this->once())->method('get')->with($companyId)->willReturn($company);
        $company->expects($this->once())
            ->method('getStatus')->willReturn(\Magento\Company\Api\Data\CompanyInterface::STATUS_REJECTED);
        $this->assertFalse($this->companyStatus->isRefundAvailable($companyId));
    }

    /**
     * Test for method isRefundAvailable without company.
     *
     * @return void
     */
    public function testIsRefundAvailableWithoutCompany()
    {
        $companyId = 1;
        $this->companyRepository->expects($this->once())->method('get')->with($companyId)
            ->willThrowException(new \Magento\Framework\Exception\NoSuchEntityException());
        $this->assertFalse($this->companyStatus->isRefundAvailable($companyId));
    }

    /**
     * Test for method isRevertAvailable.
     *
     * @return void
     */
    public function testIsRevertAvailable()
    {
        $companyId = 1;
        $company = $this->createMock(\Magento\Company\Api\Data\CompanyInterface::class);
        $this->companyRepository->expects($this->once())->method('get')->with($companyId)->willReturn($company);
        $company->expects($this->once())
            ->method('getStatus')->willReturn(\Magento\Company\Api\Data\CompanyInterface::STATUS_APPROVED);
        $this->assertTrue($this->companyStatus->isRevertAvailable($companyId));
    }
}
