<?php

namespace Magento\Company\Test\Unit\Plugin\Customer\Model\Customer;

use Magento\Customer\Model\Customer\DataProviderWithDefaultAddresses as CustomerDataProvider;
use Magento\Company\Plugin\Customer\Model\Customer\DataProviderPlugin;

/**
 * @deprecated tested file is not used
 * Class for test DataProviderPlugin.
 */
class DataProviderPluginTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerRepository;

    /**
     * @var \Magento\Company\Api\CompanyRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $companyRepository;

    /**
     * @var CustomerDataProvider|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerDataProvider;

    /**
     * @var DataProviderPlugin|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerDataProviderPlugin;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->customerRepository = $this->createMock(
            \Magento\Customer\Api\CustomerRepositoryInterface::class
        );

        $this->companyRepository = $this->createMock(
            \Magento\Company\Api\CompanyRepositoryInterface::class
        );

        $this->customerDataProvider = $this->createMock(CustomerDataProvider::class);

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->customerDataProviderPlugin = $objectManagerHelper->getObject(
            DataProviderPlugin::class,
            [
                'customerRepository' => $this->customerRepository,
                'companyRepository' => $this->companyRepository
            ]
        );
    }

    /**
     * Test for method AfterGetData.
     *
     * @param array|null $data
     * @param array|null $companyData
     * @param array|null $expectedResult
     * @dataProvider dataProviderAfterGetData
     * @return void
     */
    public function testAfterGetData($data, $companyData, $expectedResult)
    {
        $customer = $this->getMockBuilder(\Magento\Customer\Model\Data\Customer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $company = $this->getMockBuilder(\Magento\Company\Api\Data\CompanyInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCompanyName', 'getId'])
            ->getMockForAbstractClass();

        $companyAttributes = $this->getMockBuilder(\Magento\Company\Api\Data\CompanyCustomerInterface::class)
            ->setMethods(['getIsSuperUser', 'getCompanyId', 'getStatus'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $customerExtension = $this->getMockBuilder(\Magento\Customer\Api\Data\CustomerExtensionInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['setCompanyAttributes', 'getCompanyAttributes'])
            ->getMockForAbstractClass();

        $customer->expects($this->any())
            ->method('getExtensionAttributes')
            ->willReturn($customerExtension);
        $customerExtension->expects($this->any())
            ->method('getCompanyAttributes')
            ->willReturn($companyAttributes);

        $companyAttributes->expects($this->any())
            ->method('getIsSuperUser')
            ->willReturn($companyData['is_super_user']);
        $companyAttributes->expects($this->any())
            ->method('getCompanyId')
            ->willReturn($companyData['company_id']);
        $companyAttributes->expects($this->any())
            ->method('getStatus')
            ->willReturn($companyData['status']);

        $this->companyRepository->expects($this->any())
            ->method('get')
            ->willReturn($company);

        $company->expects($this->any())
            ->method('getCompanyName')
            ->willReturn($companyData['company_name']);

        $company->expects($this->any())
            ->method('getId')
            ->willReturn($companyData['company_id']);

        $this->customerRepository->expects($this->any())
            ->method('getById')
            ->willReturn($customer);

        $resultData = $this->customerDataProviderPlugin->afterGetData($this->customerDataProvider, $data);

        $this->assertEquals($expectedResult, $resultData);
    }

    /**
     * Data provider for method testAfterGetData.
     *
     * @return array
     */
    public function dataProviderAfterGetData()
    {
        return [
            [
                [
                    7 => [
                        'customer' => []
                    ]
                ],
                [
                    'status' => 0,
                    'is_super_user' => false,
                    'company_id' => 1,
                    'company_name' => 'Company 1'
                ],
                [
                    7 => [
                        'customer' => [
                            'extension_attributes' => [
                                'company_attributes' => [
                                    'is_super_user' => 1,
                                    'status' => 0,
                                    'company_id' => 1,
                                    'company_name' => 'Company 1'
                                ]
                            ]
                        ]
                    ]
                ],
            ],
            [
                [
                    7 => [
                        'customer' => []
                    ]
                ],
                [
                    'status' => 0,
                    'is_super_user' => false,
                    'company_id' => null,
                    'company_name' => null
                ],
                [
                    7 => [
                        'customer' => [
                            'extension_attributes' => [
                                'company_attributes' => [
                                    'status' => '0',
                                    'is_super_user' => 1,
                                    'company_id' => null,
                                    'company_name' => null
                                ]
                            ]
                        ]
                    ]
                ],
            ],
            [
                null,
                [
                    'status' => null,
                    'is_super_user' => null,
                    'company_id' => null,
                    'company_name' => null
                ],
                null
            ],
        ];
    }
}
