<?php

namespace Magento\Company\Test\Unit\Block\Company;

/**
 * Class CompanyProfileTest.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CompanyProfileTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Authorization\Model\UserContextInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $userContext;

    /**
     * @var \Magento\Company\Api\CompanyManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $companyManagement;

    /**
     * @var \Magento\Company\Model\CountryInformationProvider|\PHPUnit_Framework_MockObject_MockObject
     */
    private $countryInformationProvider;

    /**
     * @var \Magento\User\Model\UserFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $userFactory;

    /**
     * @var \Magento\Framework\Message\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $messageManager;

    /**
     * @var \Magento\Company\Model\CompanyAdminPermission|\PHPUnit_Framework_MockObject_MockObject
     */
    private $companyAdminPermission;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerRepository;

    /**
     * @var \Magento\Customer\Api\CustomerNameGenerationInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerViewHelper;

    /**
     * @var \Magento\Company\Block\Company\CompanyProfile|\PHPUnit_Framework_MockObject_MockObject
     */
    private $companyProfile;

    /**
     * @var \Magento\Company\Api\Data\CompanyInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $company;

    /**
     * @var \Magento\Customer\Api\Data\CustomerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $companyAdmin;

    /**
     * @var \Magento\User\Model\User|\PHPUnit_Framework_MockObject_MockObject
     */
    private $salesRepresentative;

    /**
     * @var \Magento\Company\Api\AuthorizationInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $authorization;

    /**
     * Set up
     */
    protected function setUp()
    {
        $this->userContext = $this->createMock(\Magento\Authorization\Model\UserContextInterface::class);
        $this->companyManagement = $this->createMock(\Magento\Company\Api\CompanyManagementInterface::class);
        $this->countryInformationProvider = $this->createMock(\Magento\Company\Model\CountryInformationProvider::class);
        $this->userFactory = $this->createPartialMock(\Magento\User\Model\UserFactory::class, ['create']);
        $this->messageManager = $this->createMock(\Magento\Framework\Message\ManagerInterface::class);
        $this->companyAdminPermission = $this->createMock(\Magento\Company\Model\CompanyAdminPermission::class);
        $this->customerRepository = $this->createMock(\Magento\Customer\Api\CustomerRepositoryInterface::class);
        $this->customerViewHelper = $this->createMock(\Magento\Customer\Api\CustomerNameGenerationInterface::class);
        $this->company = $this->createMock(\Magento\Company\Api\Data\CompanyInterface::class);
        $this->company->expects($this->any())->method('getId')->willReturn(1);
        $this->companyAdmin = $this->createMock(\Magento\Customer\Api\Data\CustomerInterface::class);
        $this->salesRepresentative = $this->createMock(\Magento\User\Model\User::class);
        $this->authorization = $this->createMock(\Magento\Company\Api\AuthorizationInterface::class);
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->companyProfile = $objectManager->getObject(
            \Magento\Company\Block\Company\CompanyProfile::class,
            [
                'userContext' => $this->userContext,
                'companyManagement' => $this->companyManagement,
                'countryInformationProvider' => $this->countryInformationProvider,
                'userFactory' => $this->userFactory,
                'messageManager' => $this->messageManager,
                'companyAdminPermission' => $this->companyAdminPermission,
                'customerRepository' => $this->customerRepository,
                'customerViewHelper' => $this->customerViewHelper,
                'authorization' => $this->authorization,
                'data' => [],
            ]
        );
    }

    /**
     * Test getCountriesList
     *
     * @param array $countriesList
     * @dataProvider dataProviderGetCountriesList
     */
    public function testGetCountriesList(array $countriesList)
    {
        $this->countryInformationProvider->expects($this->any())->method('getCountriesList')
            ->willReturn($countriesList);

        $this->assertEquals($countriesList, $this->countryInformationProvider->getCountriesList());
    }

    /**
     * Test getFormMessages
     *
     * @param array $formMessages
     * @dataProvider dataProviderGetFormMessages
     */
    public function testGetFormMessages(array $formMessages)
    {
        $messageCollection = $this->createMock(\Magento\Framework\Message\Collection::class);
        $firstMessage = $this->createMock(\Magento\Framework\Message\MessageInterface::class);
        $firstMessage->expects($this->any())->method('getText')->willReturn('Error Message 1');
        $secondMessage = $this->createMock(\Magento\Framework\Message\MessageInterface::class);
        $secondMessage->expects($this->any())->method('getText')->willReturn('Error Message 2');
        $iterator = new \ArrayIterator([$firstMessage, $secondMessage]);
        $messageCollection->expects($this->any())->method('getCount')->willReturn(2);
        $messageCollection->expects($this->any())->method('getItems')->will($this->returnValue($iterator));
        $this->messageManager->expects($this->any())->method('getMessages')->willReturn($messageCollection);

        $this->assertEquals($formMessages, $this->companyProfile->getFormMessages());
    }

    /**
     * Test isEditLinkDisplayed
     *
     * @param bool $isEditLinkDisplayed
     * @dataProvider dataProviderIsEditLinkDisplayed
     */
    public function testIsEditLinkDisplayed($isEditLinkDisplayed)
    {
        $this->authorization->expects($this->atLeastOnce())->method('isAllowed')->willReturn($isEditLinkDisplayed);

        $this->assertEquals($isEditLinkDisplayed, $this->companyProfile->isEditLinkDisplayed());
    }

    /**
     * Test getCustomerCompany
     */
    public function testGetCustomerCompany()
    {
        $this->userContext->expects($this->any())->method('getUserId')->willReturn(1);
        $this->companyManagement->expects($this->any())->method('getByCustomerId')->willReturn($this->company);

        $this->assertEquals($this->company, $this->companyProfile->getCustomerCompany());
    }

    /**
     * Test getCompanyStreetLabel
     *
     * @param array $streetData
     * @param string $streetLabel
     * @dataProvider dataProviderGetCompanyStreetLabel
     */
    public function testGetCompanyStreetLabel(array $streetData, $streetLabel)
    {
        $this->company->expects($this->any())->method('getStreet')->willReturn($streetData);

        $this->assertEquals($streetLabel, $this->companyProfile->getCompanyStreetLabel($this->company));
    }

    /**
     * Test isCompanyAddressDisplayed
     *
     * @param string|null $countryId
     * @param bool $companyHasAddresses
     * @dataProvider dataProviderIsCompanyAddressDisplayed
     */
    public function testIsCompanyAddressDisplayed($countryId, $companyHasAddresses)
    {
        $this->company->expects($this->any())->method('getCountryId')->willReturn($countryId);

        $this->assertEquals($companyHasAddresses, $this->companyProfile->isCompanyAddressDisplayed($this->company));
    }

    /**
     * Test getCompanyAddressString
     *
     * @param string $countryId
     * @param string $city
     * @param string $regionId
     * @param string $region
     * @param string $postcode
     * @param string $regionName
     * @param string $addressString
     * @dataProvider dataProviderGetCompanyAddressString
     */
    public function testGetCompanyAddressString(
        $countryId,
        $city,
        $regionId,
        $region,
        $postcode,
        $regionName,
        $addressString
    ) {
        $this->company->expects($this->atLeastOnce())->method('getCountryId')->willReturn($countryId);
        $this->company->expects($this->atLeastOnce())->method('getCity')->willReturn($city);
        $this->company->expects($this->atLeastOnce())->method('getRegionId')->willReturn($regionId);
        $this->company->expects($this->atLeastOnce())->method('getRegion')->willReturn($region);
        $this->company->expects($this->atLeastOnce())->method('getPostcode')->willReturn($postcode);

        $this->countryInformationProvider->expects($this->atLeastOnce())
            ->method('getActualRegionName')
            ->with($countryId, $regionId, $region)
            ->willReturn($regionName);

        $this->assertEquals($addressString, $this->companyProfile->getCompanyAddressString($this->company));
    }

    /**
     * Test getCompanyCountryLabel
     */
    public function testGetCompanyCountryLabel()
    {
        $this->company->expects($this->any())->method('getCountryId')->willReturn(1);
        $countryName = 'United States';
        $this->countryInformationProvider->expects($this->any())->method('getCountryNameByCode')
            ->willReturn($countryName);

        $this->assertEquals($countryName, $this->companyProfile->getCompanyCountryLabel($this->company));
    }

    /**
     * Test getCompanyAdminName
     *
     * @param array $companyAdminData
     * @param string $name
     * @dataProvider dataProviderGetCompanyAdminName
     */
    public function testGetCompanyAdminName(array $companyAdminData, $name)
    {
        $this->company->expects($this->any())->method('getId')->willReturn(1);
        $this->companyManagement->expects($this->any())->method('getAdminByCompanyId')->willReturn($this->companyAdmin);
        $this->companyAdmin->expects($this->any())->method('getId')->willReturn($companyAdminData['entity_id']);
        $this->customerViewHelper->expects($this->any())->method('getCustomerName')->willReturn($name);

        $this->assertEquals($name, $this->companyProfile->getCompanyAdminName($this->company));
    }

    /**
     * @param array $companyAdminData
     * @param string $name
     * @dataProvider dataProviderGetCompanyAdminNameWithEmptyValue
     */
    public function testGetCompanyAdminNameWithEmptyValue(array $companyAdminData, $name)
    {
        $this->company->expects($this->any())->method('getId')->willReturn(1);
        $this->companyManagement->expects($this->any())->method('getAdminByCompanyId')->willReturn($this->companyAdmin);
        $this->companyAdmin->expects($this->any())->method('getId')->willReturn($companyAdminData['entity_id']);
        $this->customerRepository->expects($this->any())->method('getById')->willReturn(null);
        $this->customerViewHelper->expects($this->any())->method('getCustomerName')->willReturn($name);

        $this->assertEquals($name, $this->companyProfile->getCompanyAdminName($this->company));
    }

    /**
     * Test getCompanyJobTitle
     *
     * @param array $companyAdminData
     * @param string $jobTitle
     * @dataProvider dataProviderGetCompanyAdminJobTitle
     */
    public function testGetCompanyJobTitle(array $companyAdminData, $jobTitle)
    {
        $this->company->expects($this->any())->method('getId')->willReturn(1);
        $this->companyManagement->expects($this->any())->method('getAdminByCompanyId')->willReturn($this->companyAdmin);
        $this->companyAdmin->expects($this->any())->method('getId')->willReturn($companyAdminData['entity_id']);
        $this->customerRepository->expects($this->any())->method('getById')->willReturn($this->companyAdmin);
        $companyExtensionAttributes = $this->createMock(\Magento\Company\Model\Customer::class);
        $companyExtensionAttributes->expects($this->any())->method('getJobTitle')->willReturn($jobTitle);
        $extensionAttributes = $this->createPartialMock(
            \Magento\Customer\Api\Data\CustomerExtension::class,
            ['getCompanyAttributes']
        );
        $extensionAttributes->expects($this->any())->method('getCompanyAttributes')
            ->willReturn($companyExtensionAttributes);
        $this->companyAdmin->expects($this->any())->method('getExtensionAttributes')->willReturn($extensionAttributes);

        $this->assertEquals($jobTitle, $this->companyProfile->getCompanyAdminJobTitle($this->company));
    }

    /**
     * Test getCompanyJobTitle with empty value
     *
     * @param array $companyAdminData
     * @param string $jobTitle
     * @dataProvider dataProviderGetCompanyAdminJobTitleWithEmptyValue
     */
    public function testGetCompanyJobTitleWithEmptyValue(array $companyAdminData, $jobTitle)
    {
        $this->company->expects($this->any())->method('getId')->willReturn(1);
        $this->companyManagement->expects($this->any())->method('getAdminByCompanyId')->willReturn($this->companyAdmin);
        $this->companyAdmin->expects($this->any())->method('getId')->willReturn($companyAdminData['entity_id']);
        $this->customerRepository->expects($this->any())->method('getById')->willReturn(null);

        $this->assertEquals($jobTitle, $this->companyProfile->getCompanyAdminJobTitle($this->company));
    }

    /**
     * Test getCompanyJobTitle with empty extension attributes
     *
     * @param array $companyAdminData
     * @param string $jobTitle
     * @dataProvider dataProviderGetCompanyAdminJobTitleWithEmptyValue
     */
    public function testGetGetCompanyJobTitleWithEmptyExtensionAttributes(array $companyAdminData, $jobTitle)
    {
        $this->company->expects($this->any())->method('getId')->willReturn(1);
        $this->companyManagement->expects($this->any())->method('getAdminByCompanyId')->willReturn($this->companyAdmin);
        $this->companyAdmin->expects($this->any())->method('getId')->willReturn($companyAdminData['entity_id']);
        $this->customerRepository->expects($this->any())->method('getById')->willReturn($this->companyAdmin);
        $extensionAttributes = $this->createPartialMock(
            \Magento\Customer\Api\Data\CustomerExtension::class,
            ['getCompanyAttributes']
        );
        $extensionAttributes->expects($this->any())->method('getCompanyAttributes')->willReturn(null);
        $this->companyAdmin->expects($this->any())->method('getExtensionAttributes')->willReturn($extensionAttributes);

        $this->assertEquals($jobTitle, $this->companyProfile->getCompanyAdminJobTitle($this->company));
    }

    /**
     * Test getCompanyAdminEmail
     *
     * @param array $companyAdminData
     * @param string $email
     * @dataProvider dataProviderGetCompanyAdminEmail
     */
    public function testGetCompanyAdminEmail(array $companyAdminData, $email)
    {
        $this->company->expects($this->any())->method('getId')->willReturn(1);
        $this->companyManagement->expects($this->any())->method('getAdminByCompanyId')->willReturn($this->companyAdmin);
        $this->companyAdmin->expects($this->any())->method('getId')->willReturn($companyAdminData['entity_id']);
        $this->companyAdmin->expects($this->any())->method('getEmail')->willReturn($email);
        $this->customerRepository->expects($this->any())->method('getById')->willReturn($this->companyAdmin);

        $this->assertEquals($email, $this->companyProfile->getCompanyAdminEmail($this->company));
    }

    /**
     * Test getCompanyAdminEmail with empty value
     *
     * @param array $companyAdminData
     * @param string $email
     * @dataProvider dataProviderGetCompanyAdminEmailWithEmptyValue
     */
    public function testGetCompanyAdminEmailWithEmptyValue(array $companyAdminData, $email)
    {
        $this->company->expects($this->any())->method('getId')->willReturn(1);
        $this->companyManagement->expects($this->any())->method('getAdminByCompanyId')->willReturn($this->companyAdmin);
        $this->companyAdmin->expects($this->any())->method('getId')->willReturn($companyAdminData['entity_id']);
        $this->companyAdmin->expects($this->any())->method('getEmail')->willReturn($email);
        $this->customerRepository->expects($this->any())->method('getById')->willReturn(null);

        $this->assertEquals($email, $this->companyProfile->getCompanyAdminEmail($this->company));
    }

    /**
     * Test getSalesRepresentativeName
     *
     * @param int $salesRepresentativeId
     * @param string $name
     * @dataProvider dataProviderGetSalesRepresentativeName
     */
    public function testGetSalesRepresentativeName($salesRepresentativeId, $name)
    {
        $this->company->expects($this->any())->method('getSalesRepresentativeId')->willReturn($salesRepresentativeId);
        $this->salesRepresentative->expects($this->any())->method('getId')->willReturn($salesRepresentativeId);
        $this->salesRepresentative->expects($this->any())->method('load')->willReturnSelf();
        $this->salesRepresentative->expects($this->any())->method('getName')->willReturn($name);
        $this->userFactory->expects($this->any())->method('create')->willReturn($this->salesRepresentative);

        $this->assertEquals($name, $this->companyProfile->getSalesRepresentativeName($this->company));
    }

    /**
     * Test getSalesRepresentativeName with empty value
     *
     * @param null $salesRepresentativeId
     * @param string $name
     * @dataProvider dataProviderGetSalesRepresentativeNameWithEmptyValue
     */
    public function testGetSalesRepresentativeNameWithEmptyValue($salesRepresentativeId, $name)
    {
        $this->company->expects($this->any())->method('getSalesRepresentativeId')->willReturn($salesRepresentativeId);
        $this->salesRepresentative->expects($this->any())->method('getId')->willReturn($salesRepresentativeId);
        $this->salesRepresentative->expects($this->any())->method('load')->willReturn(null);
        $this->salesRepresentative->expects($this->any())->method('getName')->willReturn($name);
        $this->userFactory->expects($this->any())->method('create')->willReturn($this->salesRepresentative);

        $this->assertEquals($name, $this->companyProfile->getSalesRepresentativeName($this->company));
    }

    /**
     * Test getSalesRepresentativeEmail
     *
     * @param int $salesRepresentativeId
     * @param string $email
     * @dataProvider dataProviderGetSalesRepresentativeEmail
     */
    public function testGetSalesRepresentativeEmail($salesRepresentativeId, $email)
    {
        $this->company->expects($this->any())->method('getSalesRepresentativeId')->willReturn($salesRepresentativeId);
        $this->salesRepresentative->expects($this->any())->method('getId')->willReturn($salesRepresentativeId);
        $this->salesRepresentative->expects($this->any())->method('load')->willReturnSelf();
        $this->salesRepresentative->expects($this->any())->method('getEmail')->willReturn($email);
        $this->userFactory->expects($this->any())->method('create')->willReturn($this->salesRepresentative);

        $this->assertEquals($email, $this->companyProfile->getSalesRepresentativeEmail($this->company));
    }

    /**
     * Test getSalesRepresentativeEmail with empty value
     *
     * @param null $salesRepresentativeId
     * @param string $email
     * @dataProvider dataProviderGetSalesRepresentativeEmailWithEmptyValue
     */
    public function testGetSalesRepresentativeEmailWithEmptyValue($salesRepresentativeId, $email)
    {
        $this->company->expects($this->any())->method('getSalesRepresentativeId')->willReturn($salesRepresentativeId);
        $this->salesRepresentative->expects($this->any())->method('getId')->willReturn($salesRepresentativeId);
        $this->salesRepresentative->expects($this->any())->method('load')->willReturn(null);
        $this->salesRepresentative->expects($this->any())->method('getEmail')->willReturn($email);
        $this->userFactory->expects($this->any())->method('create')->willReturn($this->salesRepresentative);

        $this->assertEquals($email, $this->companyProfile->getSalesRepresentativeEmail($this->company));
    }

    /**
     * Data provider getCountriesList
     *
     * @return array
     */
    public function dataProviderGetCountriesList()
    {
        return [
            [
                ['United States', 'United Kingdom', 'France']
            ]
        ];
    }

    /**
     * Data provider getFormMessages
     *
     * @return array
     */
    public function dataProviderGetFormMessages()
    {
        return [
            [
                ['Error Message 1', 'Error Message 2']
            ]
        ];
    }

    /**
     * Data provider isEditLinkDisplayed
     *
     * @return array
     */
    public function dataProviderIsEditLinkDisplayed()
    {
        return [
            [true],
            [false]
        ];
    }

    /**
     * Data provider getCompanyAddressStreetLabel
     *
     * @return array
     */
    public function dataProviderGetCompanyStreetLabel()
    {
        return [
            [['Street 1', null], 'Street 1'],
            [['Street 1', 'Street 2'], 'Street 1 Street 2']
        ];
    }

    /**
     * Data provider isCompanyAddressDisplayed
     *
     * @return array
     */
    public function dataProviderIsCompanyAddressDisplayed()
    {
        return [
            ['US', true],
            [null, false]
        ];
    }

    /**
     * Data provider getCompanyAddressString
     *
     * @return array
     */
    public function dataProviderGetCompanyAddressString()
    {
        return [
            [1, 'City', 1, null, '12323', 'California', 'City, California, 12323'],
            [2, 'City', null, null, '12323', null, 'City, 12323'],
            [2, 'City', null, null, null, 'California', 'City, California'],
            [1, 'City', null, null, null, null, 'City'],
        ];
    }

    /**
     * Data provider getCompanyAdminName
     *
     * @return array
     */
    public function dataProviderGetCompanyAdminName()
    {
        return [
            [['entity_id' => 1], 'Admin name']
        ];
    }

    /**
     * Data provider getCompanyAdminNameWithEmptyValue
     *
     * @return array
     */
    public function dataProviderGetCompanyAdminNameWithEmptyValue()
    {
        return [
            [['entity_id' => null], '']
        ];
    }

    /**
     * Data provider getCompanyAdminJobTitle
     *
     * @return array
     */
    public function dataProviderGetCompanyAdminJobTitle()
    {
        return [
            [['entity_id' => 1], 'Admin name']
        ];
    }

    /**
     * Data provider getCompanyAdminJobTitleWithEmptyValue
     *
     * @return array
     */
    public function dataProviderGetCompanyAdminJobTitleWithEmptyValue()
    {
        return [
            [['entity_id' => null], '']
        ];
    }

    /**
     * Data provider getCompanyAdminEmail
     *
     * @return array
     */
    public function dataProviderGetCompanyAdminEmail()
    {
        return [
            [['entity_id' => 1], 'admin@example.com']
        ];
    }

    /**
     * Data provider getCompanyAdminEmailWithEmptyValue
     *
     * @return array
     */
    public function dataProviderGetCompanyAdminEmailWithEmptyValue()
    {
        return [
            [['entity_id' => null], '']
        ];
    }

    /**
     * Data provider getSalesRepresentativeName
     *
     * @return array
     */
    public function dataProviderGetSalesRepresentativeName()
    {
        return [
            [1, 'Name']
        ];
    }

    /**
     * Data provider getSalesRepresentativeNameWithEmptyValue
     *
     * @return array
     */
    public function dataProviderGetSalesRepresentativeNameWithEmptyValue()
    {
        return [
            [null, '']
        ];
    }

    /**
     * Data provider getSalesRepresentativeEmail
     *
     * @return array
     */
    public function dataProviderGetSalesRepresentativeEmail()
    {
        return [
            [1, 'sales_representative@example.com']
        ];
    }

    /**
     * Data provider getSalesRepresentativeEmailWithEmptyValue
     *
     * @return array
     */
    public function dataProviderGetSalesRepresentativeEmailWithEmptyValue()
    {
        return [
            [null, '']
        ];
    }
}
