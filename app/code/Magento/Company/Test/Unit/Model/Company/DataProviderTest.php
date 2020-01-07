<?php
namespace Magento\Company\Test\Unit\Model\Company;

use Magento\Company\Model\Company\DataProvider;
use Magento\Company\Model\Company;
use Magento\Customer\Api\Data\CustomerInterface;

/**
 * Class for testing DataProvider.
 */
class DataProviderTest extends \PHPUnit\Framework\TestCase
{
    const DATA_PROVIDER_COMPANY_ID = 86;
    const DATA_PROVIDER_NAME = 'name';
    const DATA_PROVIDER_PRIMARY_FIELD = 'primary';
    const DATA_PROVIDER_REQUEST_FIELD = 'request';

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerRepository;

    /**
     * @var \Magento\Company\Model\ResourceModel\Company\CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $companyCollectionFactory;

    /**
     * @var \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $extensionAttributesJoinProcessor;

    /**
     * @var \Magento\Company\Api\Data\CompanyInterface|\PHPUnit_Framework_MockObject_MockObject
     * */
    private $company;

    /**
     * @var \Magento\Company\Model\ResourceModel\Company\Collection|\PHPUnit_Framework_MockObject_MockObject
     * */
    private $collection;

    /**
     * @var \Magento\Company\Model\Company\DataProvider
     * */
    private $dataProvider;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->customerRepository = $this->getMockBuilder(\Magento\Customer\Api\CustomerRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getExtensionAttributes'])
            ->getMockForAbstractClass();
        $this->companyCollectionFactory = $this->getMockBuilder(
            \Magento\Company\Model\ResourceModel\Company\CollectionFactory::class
        )
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->extensionAttributesJoinProcessor = $this->getMockBuilder(
            \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface::class
        )
            ->disableOriginalConstructor()
            ->getMock();
        $this->company = $this->getMockBuilder(\Magento\Company\Api\Data\CompanyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->collection = $this->getMockBuilder(\Magento\Company\Model\ResourceModel\Company\Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->companyCollectionFactory->expects($this->once())->method('create')->willReturn($this->collection);
        $this->dataProvider = new DataProvider(
            self::DATA_PROVIDER_NAME,
            self::DATA_PROVIDER_PRIMARY_FIELD,
            self::DATA_PROVIDER_REQUEST_FIELD,
            $this->companyCollectionFactory,
            $this->extensionAttributesJoinProcessor,
            $this->customerRepository,
            [],
            []
        );
    }

    /**
     * Get general data.
     *
     * @param \Magento\Company\Api\Data\CompanyInterface $company
     * @return array
     */
    protected function getGeneralData(\Magento\Company\Api\Data\CompanyInterface $company)
    {
        $name = 'Johnny Mnemonic';
        $status = 'What is love?';
        $companyEmail = 'lorem@ipsum.dolor';
        $rejectReason = 'Any reject reason.';
        $rejectedAt = '2016-07-08 17:03:43';
        $salesRepresentativeId = 373;
        $result = [
            Company::NAME => $name,
            Company::STATUS => $status,
            Company::REJECT_REASON => $rejectReason,
            Company::REJECTED_AT => $rejectedAt,
            Company::COMPANY_EMAIL => $companyEmail,
            Company::SALES_REPRESENTATIVE_ID => $salesRepresentativeId,
        ];
        $company->expects($this->once())->method('getCompanyName')->willReturn($name);
        $company->expects($this->once())->method('getStatus')->willReturn($status);
        $company->expects($this->once())->method('getRejectReason')->willReturn($rejectReason);
        $company->expects($this->once())->method('getRejectedAt')->willReturn($rejectedAt);
        $company->expects($this->once())->method('getCompanyEmail')->willReturn($companyEmail);
        $company->expects($this->once())->method('getSalesRepresentativeId')->willReturn($salesRepresentativeId);
        return $result;
    }

    /**
     * Get company information data.
     *
     * @param \Magento\Company\Api\Data\CompanyInterface $company
     * @return array
     */
    protected function getInformationData(\Magento\Company\Api\Data\CompanyInterface $company)
    {
        $legalName = 'John Doe Corp';
        $vatTaxId = 777;
        $resellerId = 555;
        $comment = 'Lorem ipsum dolor';
        $result = [
            Company::LEGAL_NAME => $legalName,
            Company::VAT_TAX_ID => $vatTaxId,
            Company::RESELLER_ID => $resellerId,
            Company::COMMENT => $comment,
        ];
        $company->expects($this->once())->method('getLegalName')->willReturn($legalName);
        $company->expects($this->once())->method('getVatTaxId')->willReturn($vatTaxId);
        $company->expects($this->once())->method('getResellerId')->willReturn($resellerId);
        $company->expects($this->once())->method('getComment')->willReturn($comment);
        return $result;
    }

    /**
     * Get legal address data.
     *
     * @param \Magento\Company\Api\Data\CompanyInterface $company
     * @return array
     */
    protected function getAddressData(\Magento\Company\Api\Data\CompanyInterface $company)
    {
        $street = 'Tank st.111';
        $city = 'Down Uryupinsk';
        $countryId = 42;
        $region = 'Uryupinsk';
        $regionId = 13;
        $postCode = '1234567/';
        $telephone = '555-1234';
        $result = [
            Company::STREET => $street,
            Company::CITY => $city,
            Company::COUNTRY_ID => $countryId,
            Company::REGION => $region,
            Company::REGION_ID => $regionId,
            Company::POSTCODE => $postCode,
            Company::TELEPHONE => $telephone,
        ];
        $company->expects($this->once())->method('getStreet')->willReturn($street);
        $company->expects($this->once())->method('getCity')->willReturn($city);
        $company->expects($this->once())->method('getCountryId')->willReturn($countryId);
        $company->expects($this->once())->method('getRegion')->willReturn($region);
        $company->expects($this->once())->method('getRegionId')->willReturn($regionId);
        $company->expects($this->once())->method('getPostcode')->willReturn($postCode);
        $company->expects($this->once())->method('getTelephone')->willReturn($telephone);
        return $result;
    }

    /**
     * Get company admin data.
     *
     * @param \Magento\Company\Api\Data\CompanyInterface $company
     * @return array
     */
    protected function getCompanyAdminData(\Magento\Company\Api\Data\CompanyInterface $company): array
    {
        $userId = 4;
        $jobTitle = 'CTO';
        $prefix = 'Mr';
        $firstName = 'John';
        $middleName = 'Lost';
        $lastName = 'Doe';
        $suffix = 'Endangerous';
        $email = 'john@lost.doe';
        $gender = 'Male';
        $websiteId = '2';
        $result = [
            Company::JOB_TITLE => $jobTitle,
            Company::PREFIX => $prefix,
            Company::FIRSTNAME => $firstName,
            Company::MIDDLENAME => $middleName,
            Company::LASTNAME => $lastName,
            Company::SUFFIX => $suffix,
            Company::EMAIL => $email,
            Company::GENDER => $gender,
            CustomerInterface::WEBSITE_ID => $websiteId,
        ];
        $company->expects($this->exactly(1))->method('getSuperUserId')->willReturn($userId);

        $customer = $this->getMockBuilder(\Magento\Customer\Api\Data\CustomerInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getExtensionAttributes'])
            ->getMockForAbstractClass();
        $extensionAttributes = $this->getMockBuilder(\Magento\Customer\Api\Data\CustomerExtensionInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCompanyAttributes'])
            ->getMockForAbstractClass();
        $companyAttributes = $this->getMockBuilder(\Magento\Company\Api\Data\CompanyCustomerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $customer->expects($this->once())->method('getExtensionAttributes')->willReturn($extensionAttributes);
        $extensionAttributes->expects($this->once())->method('getCompanyAttributes')->willReturn($companyAttributes);
        $companyAttributes->expects($this->once())->method('getJobTitle')->willReturn($jobTitle);
        $customer->expects($this->once())->method('getPrefix')->willReturn($prefix);
        $customer->expects($this->once())->method('getFirstname')->willReturn($firstName);
        $customer->expects($this->once())->method('getMiddlename')->willReturn($middleName);
        $customer->expects($this->once())->method('getLastname')->willReturn($lastName);
        $customer->expects($this->once())->method('getSuffix')->willReturn($suffix);
        $customer->expects($this->once())->method('getEmail')->willReturn($email);
        $customer->expects($this->once())->method('getGender')->willReturn($gender);
        $customer->expects($this->once())
            ->method('getWebsiteId')
            ->willReturn($websiteId);
        $this->customerRepository->expects($this->once())
            ->method('getById')->with($userId)->willReturn($customer);

        return $result;
    }

    /**
     * Get advanced settings data.
     *
     * @param \Magento\Company\Api\Data\CompanyInterface $company
     * @return array
     */
    protected function getSettingsData(\Magento\Company\Api\Data\CompanyInterface $company)
    {
        $customerGroupId = 2;
        $result = [
            Company::CUSTOMER_GROUP_ID => $customerGroupId,
        ];
        $company->expects($this->atLeastOnce())->method('getCustomerGroupId')->willReturn($customerGroupId);
        return $result;
    }

    /**
     * Test for getCompanyResultData method.
     *
     * @return void
     */
    public function testGetCompanyResultData()
    {
        $expected = [
            DataProvider::DATA_SCOPE_GENERAL => $this->getGeneralData($this->company),
            DataProvider::DATA_SCOPE_INFORMATION => $this->getInformationData($this->company),
            DataProvider::DATA_SCOPE_ADDRESS => $this->getAddressData($this->company),
            DataProvider::DATA_SCOPE_COMPANY_ADMIN => $this->getCompanyAdminData($this->company),
            DataProvider::DATA_SCOPE_SETTINGS => $this->getSettingsData($this->company),
            'id' => self::DATA_PROVIDER_COMPANY_ID,
        ];

        $this->company->expects($this->once())->method('getId')->willReturn(self::DATA_PROVIDER_COMPANY_ID);
        $result = $this->dataProvider->getCompanyResultData($this->company);
        $this->assertEquals($expected, $result);
    }

    /**
     * Test getData method.
     *
     * @return void
     */
    public function testGetData()
    {
        $expected = [
            self::DATA_PROVIDER_COMPANY_ID => [
                DataProvider::DATA_SCOPE_GENERAL => $this->getGeneralData($this->company),
                DataProvider::DATA_SCOPE_INFORMATION => $this->getInformationData($this->company),
                DataProvider::DATA_SCOPE_ADDRESS => $this->getAddressData($this->company),
                DataProvider::DATA_SCOPE_COMPANY_ADMIN => $this->getCompanyAdminData($this->company),
                DataProvider::DATA_SCOPE_SETTINGS => $this->getSettingsData($this->company),
                'id' => self::DATA_PROVIDER_COMPANY_ID,
            ]
        ];
        $this->extensionAttributesJoinProcessor->expects($this->once())->method('process')->with($this->collection);
        $this->collection->expects($this->once())->method('getItems')->willReturn([$this->company]);
        $this->company->expects($this->atLeastOnce())->method('getId')->willReturn(self::DATA_PROVIDER_COMPANY_ID);

        $this->assertEquals($expected, $this->dataProvider->getData());
    }
}
