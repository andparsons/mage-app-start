<?php

namespace Magento\Company\Test\Unit\Block\Company;

/**
 * Class CompanyInfoTest
 */
class CompanyInfoTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultJsonFactory;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $request;

    /**
     * @var \Magento\Company\Api\Data\CompanyCustomerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerAttributes;

    /**
     * @var \Magento\Company\Api\Data\CompanyCustomerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $companyAttributes;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerRepository;

    /**
     * @var \Magento\Company\Api\CompanyRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $companyRepository;

    /**
     * @var \Magento\Customer\Model\Data\Customer|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customer;

    /**
     * @var \Magento\Authorization\Model\UserContextInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerContext;

    /**
     * @var \Magento\Company\Block\Company\CompanyInfo
     */
    protected $companyInfo;

    /**
     * Set up
     */
    protected function setUp()
    {
        $this->customerRepository = $this->createMock(\Magento\Customer\Api\CustomerRepositoryInterface::class);
        $this->companyRepository = $this->createMock(\Magento\Company\Api\CompanyRepositoryInterface::class);
        $this->customer = $this->createMock(\Magento\Customer\Model\Data\Customer::class);
        $this->customerContext = $this->createMock(\Magento\Authorization\Model\UserContextInterface::class);
        $this->request = $this->createMock(\Magento\Framework\App\RequestInterface::class);
        $this->companyAttributes = $this->createMock(\Magento\Company\Api\Data\CompanyCustomerInterface::class);

        $customerExtension = $this->getMockForAbstractClass(
            \Magento\Customer\Api\Data\CustomerExtensionInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['setCompanyAttributes', 'getCompanyAttributes']
        );

        $this->customerRepository->expects($this->any())
            ->method('getById')
            ->willReturn($this->customer);

        $this->customerContext->expects($this->any())->method('getUserId')->willReturn(1);

        $this->customer->expects($this->any())
            ->method('getExtensionAttributes')
            ->willReturn($customerExtension);
        $customerExtension->expects($this->any())
            ->method('getCompanyAttributes')
            ->willReturn($this->companyAttributes);

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->companyInfo = $objectManagerHelper->getObject(
            \Magento\Company\Block\Company\CompanyInfo::class,
            [
                'customerRepository' => $this->customerRepository,
                'companyRepository' => $this->companyRepository,
                'customerContext' => $this->customerContext,
                'data' => []
            ]
        );
    }

    /**
     * @param string $jobTitle
     * @dataProvider dataProviderGetJobTitle
     */
    public function testGetJobTitle($jobTitle)
    {
        $this->companyAttributes->expects($this->any())
            ->method('getJobTitle')
            ->willReturn($jobTitle);
        $this->assertEquals($jobTitle, $this->companyInfo->getJobTitle());
    }

    /**
     * @param bool $isCompanyExist
     * @param string|null $companyName
     * @dataProvider dataProviderGetCompanyName
     */
    public function testGetCompanyName($isCompanyExist, $companyName = null)
    {
        $company = $this->createMock(\Magento\Company\Api\Data\CompanyInterface::class);

        $company->expects($this->any())
            ->method('getCompanyName')
            ->willReturn('Company 1');

        $this->companyRepository->expects($this->any())
            ->method('get')
            ->willReturn($isCompanyExist ? $company : null);

        $this->assertEquals($isCompanyExist ? $companyName : '', $this->companyInfo->getCompanyName());
    }

    /**
     * @return array
     */
    public function dataProviderGetCompanyName()
    {
        return [
            [true, 'Company 1'],
            [false],
        ];
    }

    /**
     * @return array
     */
    public function dataProviderGetJobTitle()
    {
        return [
            ['My Job']
        ];
    }
}
