<?php

namespace Magento\CompanyCredit\Test\Unit\Plugin\Company\Model\Customer;

/**
 * Unit test for Magento\CompanyCredit\Plugin\Company\Model\Customer\CompanyPlugin class.
 */
class CompanyPluginTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\CompanyCredit\Api\CreditLimitRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $creditLimitRepository;

    /**
     * @var \Magento\CompanyCredit\Api\CreditLimitManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $creditLimitManagement;

    /**
     * @var \Magento\Store\Api\WebsiteRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $websiteRepository;

    /**
     * @var \Magento\CompanyCredit\Api\Data\CreditLimitInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $creditLimitFactory;

    /**
     * @var \Magento\CompanyCredit\Plugin\Company\Model\Customer\CompanyPlugin
     */
    private $companyPlugin;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->creditLimitRepository = $this
            ->getMockBuilder(\Magento\CompanyCredit\Api\CreditLimitRepositoryInterface::class)
            ->disableOriginalConstructor()->getMockForAbstractClass();
        $this->creditLimitManagement = $this
            ->getMockBuilder(\Magento\CompanyCredit\Api\CreditLimitManagementInterface::class)
            ->disableOriginalConstructor()->getMockForAbstractClass();
        $this->websiteRepository = $this->getMockBuilder(\Magento\Store\Api\WebsiteRepositoryInterface::class)
            ->disableOriginalConstructor()->getMockForAbstractClass();
        $this->creditLimitFactory = $this
            ->getMockBuilder(\Magento\CompanyCredit\Api\Data\CreditLimitInterfaceFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()->getMock();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->companyPlugin = $objectManager->getObject(
            \Magento\CompanyCredit\Plugin\Company\Model\Customer\CompanyPlugin::class,
            [
                'creditLimitRepository' => $this->creditLimitRepository,
                'creditLimitManagement' => $this->creditLimitManagement,
                'websiteRepository' => $this->websiteRepository,
                'creditLimitFactory' => $this->creditLimitFactory,
            ]
        );
    }

    /**
     * Test afterCreateCompany method.
     *
     * @return void
     */
    public function testAfterCreateCompany()
    {
        $companyId = 1;
        $baseCurrencyCode = 'USD';
        $subject = $this->getMockBuilder(\Magento\Company\Model\Customer\Company::class)
            ->disableOriginalConstructor()
            ->getMock();
        $company = $this->getMockBuilder(\Magento\Company\Api\Data\CompanyInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $company->expects($this->once())->method('getId')->willReturn($companyId);
        $creditLimit = $this->getMockBuilder(\Magento\CompanyCredit\Api\Data\CreditLimitInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $website = $this->getMockBuilder(\Magento\Store\Model\Website::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->creditLimitManagement->expects($this->once())
            ->method('getCreditByCompanyId')->with($companyId)->willReturn($creditLimit);
        $this->websiteRepository->expects($this->once())->method('getDefault')->willReturn($website);
        $website->expects($this->once())->method('getBaseCurrencyCode')->willReturn($baseCurrencyCode);
        $creditLimit->expects($this->once())->method('setCurrencyCode')->with($baseCurrencyCode)->willReturnSelf();
        $this->creditLimitRepository->expects($this->once())
            ->method('save')->with($creditLimit)->willReturn($creditLimit);
        $this->assertEquals($company, $this->companyPlugin->afterCreateCompany($subject, $company));
    }

    /**
     * Test afterCreateCompany method with exception.
     *
     * @return void
     */
    public function testAfterCreateCompanyWithException()
    {
        $companyId = 1;
        $baseCurrencyCode = 'USD';
        $exception = new \Magento\Framework\Exception\NoSuchEntityException();
        $subject = $this->getMockBuilder(\Magento\Company\Model\Customer\Company::class)
            ->disableOriginalConstructor()
            ->getMock();
        $company = $this->getMockBuilder(\Magento\Company\Api\Data\CompanyInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $creditLimit = $this->getMockBuilder(\Magento\CompanyCredit\Api\Data\CreditLimitInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $website = $this->getMockBuilder(\Magento\Store\Model\Website::class)
            ->disableOriginalConstructor()
            ->getMock();
        $company->expects($this->atLeastOnce())->method('getId')->willReturn($companyId);
        $this->creditLimitManagement->expects($this->once())
            ->method('getCreditByCompanyId')->with($companyId)->willThrowException($exception);
        $this->creditLimitFactory->expects($this->once())->method('create')->willReturn($creditLimit);
        $creditLimit->expects($this->once())->method('setCompanyId')->with($companyId)->willReturnSelf();
        $this->websiteRepository->expects($this->once())->method('getDefault')->willReturn($website);
        $website->expects($this->once())->method('getBaseCurrencyCode')->willReturn($baseCurrencyCode);
        $creditLimit->expects($this->once())->method('setCurrencyCode')->with($baseCurrencyCode)->willReturnSelf();
        $this->creditLimitRepository->expects($this->once())
            ->method('save')->with($creditLimit)->willReturn($creditLimit);

        $this->assertEquals($company, $this->companyPlugin->afterCreateCompany($subject, $company));
    }
}
