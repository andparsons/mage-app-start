<?php

namespace Magento\NegotiableQuote\Test\Unit\Model\Company;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Unit test for Magento\NegotiableQuote\Model\Company\DetailsProvider class.
 */
class DetailsProviderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var \Magento\NegotiableQuote\Model\Company\DetailsProvider|\PHPUnit_Framework_MockObject_MockObject
     */
    private $detailsProvider;

    /**
     * @var \Magento\Company\Api\CompanyManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $companyManagement;

    /**
     * @var \Magento\Company\Api\CompanyRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $companyRepository;

    /**
     * @var \Magento\Customer\Api\CustomerNameGenerationInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerNameGenerator;

    /**
     * @var \Magento\NegotiableQuote\Model\Purged\Provider|\PHPUnit_Framework_MockObject_MockObject
     */
    private $provider;

    /**
     * @var \Magento\Quote\Api\Data\CartInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quote;

    /**
     * @var \Magento\Company\Api\Data\CompanyInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $company;

    /**
     * @var \Magento\Customer\Api\Data\CustomerInterface|\PHPUnit_Framework_MockObject_MockObject
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
        $this->companyRepository = $this->getMockBuilder(\Magento\Company\Api\CompanyRepositoryInterface::class)
            ->setMethods(['get'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->customerNameGenerator = $this
            ->getMockBuilder(\Magento\Customer\Api\CustomerNameGenerationInterface::class)
            ->setMethods(['getCustomerName'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->provider = $this->getMockBuilder(\Magento\NegotiableQuote\Model\Purged\Provider::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getSalesRepresentativeId',
                'getSalesRepresentativeName',
                'getCompanyId',
                'getCustomerName',
                'getCompanyName',
                'getCompanyEmail'
            ])
            ->getMock();
        $this->quote = $this->getMockBuilder(\Magento\Quote\Api\Data\CartInterface::class)
            ->setMethods([
                'getCustomer',
                'getId'
            ])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->company = null;
        $this->customer = $this->getMockBuilder(\Magento\Customer\Api\Data\CustomerInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId', '__toArray'])
            ->getMockForAbstractClass();

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->detailsProvider = $this->objectManagerHelper->getObject(
            \Magento\NegotiableQuote\Model\Company\DetailsProvider::class,
            [
                'company' => $this->company,
                'companyManagement' => $this->companyManagement,
                'companyRepository' => $this->companyRepository,
                'customerNameGenerator' => $this->customerNameGenerator,
                'provider' => $this->provider,
                'quote' => $this->quote
            ]
        );
    }

    /**
     * Test getCompany method.
     *
     * @param int|null $customerId
     * @param array $calls
     * @dataProvider getCompanyDataProvider
     * @return void
     */
    public function testGetCompany($customerId, array $calls)
    {
        $company = $this->getCompanyMock();

        $this->customer->expects($this->exactly($calls['customerGetId']))->method('getId')->willReturn($customerId);

        $this->companyManagement->expects($this->exactly($calls['getByCustomerId']))
            ->method('getByCustomerId')->willReturn($company);

        $this->quote->expects($this->once())->method('getCustomer')->willReturn($this->customer);
        $this->quote->expects($this->exactly($calls['quoteGetId']))->method('getId')->willReturn(14);

        $this->provider->expects($this->exactly($calls['getCompanyId']))->method('getCompanyId')->willReturn(23);

        $this->companyRepository->expects($this->exactly($calls['get']))->method('get')->with(23)->willReturn($company);

        $this->assertEquals($company, $this->detailsProvider->getCompany());
    }

    /**
     * Return Company mock.
     *
     * @return \Magento\Company\Api\Data\CompanyInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getCompanyMock()
    {
        return $this->getMockBuilder(\Magento\Company\Api\Data\CompanyInterface::class)
            ->setMethods([
                'getId',
                'getSalesRepresentativeId',
                'getCompanyName'
            ])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
    }

    /**
     * Data provider for getCompany method.
     *
     * @return array
     */
    public function getCompanyDataProvider()
    {
        return [
            [
                16,
                [
                    'customerGetId' => 2,
                    'getByCustomerId' => 1,
                    'quoteGetId' => 0,
                    'getCompanyId' => 0,
                    'get' => 0
                ]
            ],
            [
                null,
                [
                    'customerGetId' => 1,
                    'getByCustomerId' => 0,
                    'quoteGetId' => 1,
                    'getCompanyId' => 1,
                    'get' => 1
                ]

            ]
        ];
    }

    /**
     * Test getCompany method with Exception.
     *
     * @return void
     */
    public function testGetCompanyWithException()
    {
        $exception = new \Exception();
        $this->customer->expects($this->once())->method('getId')->willThrowException($exception);

        $this->quote->expects($this->once())->method('getCustomer')->willReturn($this->customer);

        $this->assertNull($this->detailsProvider->getCompany());
    }

    /**
     * Test getSalesRepresentativeName method.
     *
     * @param int|null $customerId
     * @param int|null $companySalesRepId
     * @param int|null $providerSalesRepId
     * @param string $companyMngSalesRepName
     * @param string $providerSalesRepName
     * @param int $expects
     * @param $calls array
     * @dataProvider getSalesRepresentativeNameDataProvider
     * @return void
     */
    public function testGetSalesRepresentativeName(
        $customerId,
        $companySalesRepId,
        $providerSalesRepId,
        $companyMngSalesRepName,
        $providerSalesRepName,
        $expects,
        array $calls
    ) {
        $this->customer->expects($this->once())->method('getId')->willReturn($customerId);

        $quoteId = 436;
        $this->quote->expects($this->exactly($calls['quoteGetId']))->method('getId')->willReturn($quoteId);
        $this->quote->expects($this->once())->method('getCustomer')->willReturn($this->customer);

        $this->provider->expects($this->exactly($calls['providerGetSalesRepresentativeId']))
            ->method('getSalesRepresentativeId')->with($quoteId)->willReturn($providerSalesRepId);
        $this->provider->expects($this->exactly($calls['getSalesRepresentativeName']))
            ->method('getSalesRepresentativeName')->with($quoteId)->willReturn($providerSalesRepName);

        $company = $this->getCompanyMock();
        $company->expects($this->exactly($calls['companyGetSalesRepresentativeId']))
            ->method('getSalesRepresentativeId')->willReturn($companySalesRepId);

        $this->companyManagement->expects($this->exactly($calls['getByCustomerId']))->method('getByCustomerId')
            ->with($customerId)->willReturn($company);
        $this->companyManagement->expects($this->once())->method('getSalesRepresentative')
            ->willReturn($companyMngSalesRepName);

        $this->assertEquals($expects, $this->detailsProvider->getSalesRepresentativeName());
    }

    /**
     * Data provider for getSalesRepresentativeName method.
     *
     * @return array
     */
    public function getSalesRepresentativeNameDataProvider()
    {
        return [
            [
                44, 13, null, 'Company Mng Test Name', null, 'Company Mng Test Name',
                [
                    'getByCustomerId' => 2,
                    'providerGetSalesRepresentativeId' => 0,
                    'companyGetSalesRepresentativeId' => 2,
                    'quoteGetId' => 0,
                    'getSalesRepresentativeName' => 0
                ]
            ],
            [
                44, null, 15, 'Company Mng Test Name', null, 'Company Mng Test Name',
                [
                    'getByCustomerId' => 2,
                    'providerGetSalesRepresentativeId' => 1,
                    'companyGetSalesRepresentativeId' => 1,
                    'quoteGetId' => 1,
                    'getSalesRepresentativeName' => 0
                ]
            ],
            [
                null, null, 15, 'Company Mng Test Name', null, 'Company Mng Test Name',
                [
                    'getByCustomerId' => 0,
                    'providerGetSalesRepresentativeId' => 1,
                    'companyGetSalesRepresentativeId' => 0,
                    'quoteGetId' => 1,
                    'getSalesRepresentativeName' => 0
                ]
            ],
            [
                null, null, 15, null, 'Provider Test Name', 'Provider Test Name',
                [
                    'getByCustomerId' => 0,
                    'providerGetSalesRepresentativeId' => 1,
                    'companyGetSalesRepresentativeId' => 0,
                    'quoteGetId' => 2,
                    'getSalesRepresentativeName' => 1
                ]
            ]
        ];
    }

    /**
     * Test getSalesRepresentativeName method with Exception.
     *
     * @return void
     */
    public function testGetSalesRepresentativeNameWithException()
    {
        $exception = new \Magento\Framework\Exception\NoSuchEntityException();
        $quoteId = 32;
        $this->quote->expects($this->once())->method('getCustomer')->willThrowException($exception);
        $this->quote->expects($this->once())->method('getId')->willReturn($quoteId);

        $salesRepresentativeName = 'Test Name';
        $this->provider->expects($this->once())->method('getSalesRepresentativeName')->with($quoteId)
            ->willReturn($salesRepresentativeName);

        $this->assertEquals($salesRepresentativeName, $this->detailsProvider->getSalesRepresentativeName());
    }

    /**
     * Test getSalesRepresentativeId method.
     *
     * @param int|null $customerId
     * @param int|null $companySalesRepId
     * @param int|null $providerSalesRepId
     * @param array $calls
     * @param int $expected
     * @dataProvider getSalesRepresentativeIdDataProvider
     * @return void
     */
    public function testGetSalesRepresentativeId(
        $customerId,
        $companySalesRepId,
        $providerSalesRepId,
        $expected,
        array $calls
    ) {
        $this->customer->expects($this->once())->method('getId')->willReturn($customerId);

        $quoteId = 43;
        $this->quote->expects($this->exactly($calls['quoteGetId']))->method('getId')->willReturn($quoteId);
        $this->quote->expects($this->once())->method('getCustomer')->willReturn($this->customer);

        $this->provider->expects($this->exactly($calls['providerGetSalesRepresentativeId']))
            ->method('getSalesRepresentativeId')->with($quoteId)->willReturn($providerSalesRepId);

        $company = $this->getCompanyMock();
        $company->expects($this->exactly($calls['companyGetSalesRepresentativeId']))
            ->method('getSalesRepresentativeId')->willReturn($companySalesRepId);

        $this->companyManagement->expects($this->exactly($calls['getByCustomerId']))->method('getByCustomerId')
            ->with($customerId)->willReturn($company);

        $this->assertEquals($expected, $this->detailsProvider->getSalesRepresentativeId());
    }

    /**
     * Data provider for getSalesRepresentativeId method.
     *
     * @return array
     */
    public function getSalesRepresentativeIdDataProvider()
    {
        return [
            [
                12, 16, null, 16,
                [
                    'quoteGetId' => 0,
                    'getByCustomerId' => 2,
                    'companyGetSalesRepresentativeId' => 2,
                    'providerGetSalesRepresentativeId' => 0,
                ]
            ],
            [
                12, null, 23, 23,
                [
                    'quoteGetId' => 1,
                    'getByCustomerId' => 2,
                    'companyGetSalesRepresentativeId' => 1,
                    'providerGetSalesRepresentativeId' => 1,
                ]
            ],
            [
                null, null, 24, 24,
                [
                    'quoteGetId' => 1,
                    'getByCustomerId' => 0,
                    'companyGetSalesRepresentativeId' => 0,
                    'providerGetSalesRepresentativeId' => 1,
                ]
            ],
        ];
    }

    /**
     * Test getSalesRepresentativeId method with Exception.
     *
     * @return void
     */
    public function testGetSalesRepresentativeIdWithException()
    {
        $exception = new \Magento\Framework\Exception\NoSuchEntityException();
        $quoteId = 312;
        $this->quote->expects($this->once())->method('getCustomer')->willThrowException($exception);
        $this->quote->expects($this->once())->method('getId')->willReturn($quoteId);

        $salesRepresentativeId = 33;
        $this->provider->expects($this->once())
            ->method('getSalesRepresentativeId')->with($quoteId)->willReturn($salesRepresentativeId);

        $this->assertEquals($salesRepresentativeId, $this->detailsProvider->getSalesRepresentativeId());
    }

    /**
     * Test existsSalesRepresentative method.
     *
     * @return void
     */
    public function testExistsSalesRepresentative()
    {
        $salesRepresentativeId = 154;

        $this->customer->expects($this->once())->method('getId')->willReturn(null);

        $quoteId = 43;
        $this->quote->expects($this->once())->method('getId')->willReturn($quoteId);
        $this->quote->expects($this->once())->method('getCustomer')->willReturn($this->customer);

        $this->provider->expects($this->once())->method('getSalesRepresentativeId')
            ->with($quoteId)->willReturn($salesRepresentativeId);

        $this->companyManagement->expects($this->once())->method('getSalesRepresentative')
            ->with($salesRepresentativeId)->willReturn('Test Representative');

        $this->assertTrue($this->detailsProvider->existsSalesRepresentative());
    }

    /**
     * Test getQuoteOwnerName method.
     *
     * @param int|null $customerId
     * @param array $calls
     * @dataProvider getQuoteOwnerNameDataProvider
     * @return void
     */
    public function testGetQuoteOwnerName($customerId, array $calls)
    {
        $customerName = 'Test Customer';
        $this->customer->expects($this->once())->method('getId')->willReturn($customerId);

        $quoteId = 2;
        $this->quote->expects($this->exactly($calls['quoteGetId']))->method('getId')->willReturn($quoteId);
        $this->quote->expects($this->exactly($calls['getCustomer']))
            ->method('getCustomer')->willReturn($this->customer);

        $this->customerNameGenerator->expects($this->exactly($calls['generatorGetCustomerName']))
            ->method('getCustomerName')->with($this->customer)->willReturn($customerName);

        $this->provider->expects($this->exactly($calls['providerGetCustomerName']))
            ->method('getCustomerName')->with($quoteId)->willReturn($customerName);

        $this->assertEquals($customerName, $this->detailsProvider->getQuoteOwnerName());
    }

    /**
     * Data provider for getQuoteOwnerName method.
     *
     * @return array
     */
    public function getQuoteOwnerNameDataProvider()
    {
        return [
            [
                12,
                [
                    'getCustomer' => 3,
                    'quoteGetId' => 0,
                    'generatorGetCustomerName' => 1,
                    'providerGetCustomerName' => 0
                ]
            ],
            [
                null,
                [
                    'getCustomer' => 2,
                    'quoteGetId' => 1,
                    'generatorGetCustomerName' => 0,
                    'providerGetCustomerName' => 1
                ]
            ]
        ];
    }

    /**
     * Test getCompanyAdminEmail method.
     *
     * @param array|null $companyAdminData
     * @param string $adminEmail
     * @param array $calls
     * @dataProvider getCompanyAdminEmailDataProvider
     * @return void
     */
    public function testGetCompanyAdminEmail($companyAdminData, $adminEmail, array $calls)
    {
        $quoteId = 32;

        $companyId = 5;
        $company = $this->getCompanyMock();
        $company->expects($this->exactly($calls['companyGetId']))->method('getId')->willReturn($companyId);
        $this->setUpCompany($company, $quoteId, $calls);

        $this->companyManagement->expects($this->exactly($calls['getAdminByCompanyId']))
            ->method('getAdminByCompanyId')->with($companyId)->willReturn($this->customer);
        $this->customer->expects($this->exactly($calls['getAdminByCompanyId']))
            ->method('__toArray')
            ->willReturn($companyAdminData);

        $this->provider->expects($this->exactly($calls['getCompanyEmail']))->method('getCompanyEmail')->with($quoteId)
            ->willReturn($adminEmail);

        $this->assertEquals($adminEmail, $this->detailsProvider->getCompanyAdminEmail());
    }

    /**
     * Data provider for getCompanyAdminEmail method.
     *
     * @return array
     */
    public function getCompanyAdminEmailDataProvider()
    {
        $adminEmail = 'admin@test.com';
        $companyAdminData = [
            'email' => $adminEmail
        ];
        return [
            [
                $companyAdminData, $adminEmail,
                [
                    'quoteGetId' => 0,
                    'companyGetId' => 3,
                    'getAdminByCompanyId' => 3,
                    'getCompanyEmail' => 0
                ]
            ],
            [
                null, $adminEmail,
                [
                    'quoteGetId' => 1,
                    'companyGetId' => 1,
                    'getAdminByCompanyId' => 1,
                    'getCompanyEmail' => 1
                ]
            ]
        ];
    }

    /**
     * Test getCompanyName method.
     *
     * @param \Magento\Company\Api\Data\CompanyInterface|null $company
     * @param string $companyName
     * @param array $calls
     * @dataProvider getCompanyNameDataProvider
     * @return void
     */
    public function testGetCompanyName($company, $companyName, array $calls)
    {
        $quoteId = 13;

        $this->setUpCompany($company, $quoteId, $calls);

        $this->provider->expects($this->exactly($calls['providerGetCompanyName']))
            ->method('getCompanyName')->with($quoteId)->willReturn($companyName);

        $this->assertEquals($companyName, $this->detailsProvider->getCompanyName());
    }

    /**
     * Set up Company.
     *
     * @param \Magento\Company\Api\Data\CompanyInterface|null $company
     * @param int $quoteId
     * @param array $calls
     * @return void
     */
    private function setUpCompany($company, $quoteId, array $calls)
    {
        $customerId = 23;
        $this->customer->expects($this->exactly(2))->method('getId')->willReturn($customerId);

        $this->quote->expects($this->once())->method('getCustomer')->willReturn($this->customer);
        $this->quote->expects($this->exactly($calls['quoteGetId']))->method('getId')->willReturn($quoteId);

        $this->companyManagement->expects($this->exactly(1))
            ->method('getByCustomerId')->with($customerId)->willReturn($company);
    }

    /**
     * Data provider for getCompanyName method.
     *
     * @return array
     */
    public function getCompanyNameDataProvider()
    {
        $companyName = 'Company Name';
        $company = $this->getCompanyMock();
        $company->expects($this->exactly(2))->method('getCompanyName')->willReturn($companyName);
        return [
            [
                $company, $companyName,
                [
                    'quoteGetId' => 0,
                    'providerGetCompanyName' => 0
                ]
            ],
            [
                null, $companyName,
                [
                    'quoteGetId' => 1,
                    'providerGetCompanyName' => 1
                ]
            ]
        ];
    }

    /**
     * Test getCompanyAdmin method.
     *
     * @param \Magento\Company\Api\Data\CompanyInterface|null $company
     * @param array $calls
     * @dataProvider getCompanyAdminDataProvider
     * @return void
     */
    public function testGetCompanyAdmin($company, array $calls)
    {
        $companyAdminData = [1,2,3];
        $quoteOwnerId = 15;
        $quoteId = 17;

        $this->setUpCompany($company, $quoteId, $calls);

        $this->provider->expects($this->exactly($calls['getCompanyId']))
            ->method('getCompanyId')->with($quoteId)->willReturn($quoteOwnerId);

        $this->companyManagement->expects($this->once())->method('getAdminByCompanyId')->with($quoteOwnerId)
            ->willReturn($this->customer);
        $this->customer->expects($this->once())->method('__toArray')->willReturn($companyAdminData);

        $this->assertEquals($companyAdminData, $this->detailsProvider->getCompanyAdmin());
    }

    /**
     * Data provider for getCompanyAdmin method.
     *
     * @return array
     */
    public function getCompanyAdminDataProvider()
    {
        $company = $this->getCompanyMock();
        $company->expects($this->once())->method('getId')->willReturn(15);
        return [
            [
                $company,
                [
                    'quoteGetId' => 0,
                    'getCompanyId' => 0
                ]
            ],
            [
                null,
                [
                    'quoteGetId' => 1,
                    'getCompanyId' => 1
                ]
            ]
        ];
    }
}
