<?php

namespace Magento\Company\Test\Unit\Block\Company\Register;

use Magento\Framework\DataObject;

/**
 * Unit test for Magento\Company\Block\Company\Register\Profile class.
 */
class ProfileTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var  \Magento\Company\Api\CompanyManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $companyManagement;

    /**
     * @var \Magento\Company\Model\CountryInformationProvider|\PHPUnit_Framework_MockObject_MockObject
     */
    private $countryInformationProvider;

    /**
     * @var \Magento\Customer\Api\CustomerMetadataInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerMetadata;

    /**
     * @var \Magento\Company\Model\Create\Session|\PHPUnit_Framework_MockObject_MockObject
     */
    private $companyCreateSession;

    /**
     * @var \Magento\Company\Block\Company\Register\Profile
     */
    private $profile;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->companyManagement = $this->getMockBuilder(\Magento\Company\Api\CompanyManagementInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->countryInformationProvider = $this->getMockBuilder(
            \Magento\Company\Model\CountryInformationProvider::class
        )
            ->disableOriginalConstructor()
            ->getMock();
        $this->customerMetadata = $this->getMockBuilder(\Magento\Customer\Api\CustomerMetadataInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->companyCreateSession = $this->getMockBuilder(\Magento\Company\Model\Create\Session::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCustomerId'])
            ->getMock();

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->profile = $objectManagerHelper->getObject(
            \Magento\Company\Block\Company\Register\Profile::class,
            [
                'companyManagement' => $this->companyManagement,
                'countryInformationProvider' => $this->countryInformationProvider,
                'customerMetadata' => $this->customerMetadata,
                'companyCreateSession' => $this->companyCreateSession,
            ]
        );
    }

    /**
     * Test for method getCompanyInformation.
     *
     * @return void
     */
    public function testGetCompanyInformation()
    {
        $company = $this->getMockBuilder(\Magento\Company\Api\Data\CompanyInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $customerId = 1;
        $this->companyCreateSession->expects($this->once())->method('getCustomerId')->willReturn($customerId);
        $this->companyManagement->expects($this->once())
            ->method('getByCustomerId')
            ->with($customerId)
            ->willReturn($company);
        $company->expects($this->once())->method('getCompanyName')->willReturn('Company Name');
        $company->expects($this->once())->method('getLegalName')->willReturn('Company Legal Name');
        $company->expects($this->once())->method('getCompanyEmail')->willReturn('mail@company.me');
        $company->expects($this->once())->method('getVatTaxId')->willReturn(420);
        $company->expects($this->once())->method('getResellerId')->willReturn(37);
        $companyInformation = [
            $this->createField(
                __('Company Name:'),
                'Company Name'
            ),
            $this->createField(
                __('Company Legal Name:'),
                'Company Legal Name'
            ),
            $this->createField(
                __('Company Email:'),
                'mail@company.me'
            ),
            $this->createField(
                __('VAT/TAX ID:'),
                420
            ),
            $this->createField(
                __('Re-seller ID:'),
                37
            ),
        ];

        $this->assertEquals($companyInformation, $this->profile->getCompanyInformation());
    }

    /**
     * Test for method getAddressInformation.
     *
     * @return void
     */
    public function testGetAddressInformation()
    {
        $company = $this->getMockBuilder(\Magento\Company\Api\Data\CompanyInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $customerId = 1;
        $this->companyCreateSession->expects($this->once())->method('getCustomerId')->willReturn($customerId);
        $this->companyManagement->expects($this->once())
            ->method('getByCustomerId')
            ->with($customerId)
            ->willReturn($company);
        $countryId = 375;
        $regionId = 1;
        $region = null;
        $regionName = 'Region';
        $countryName = 'BY';
        $this->countryInformationProvider->expects($this->once())
            ->method('getCountryNameByCode')->with($countryId)->willReturn($countryName);
        $this->countryInformationProvider->expects($this->once())
            ->method('getActualRegionName')
            ->with($countryId, $regionId, $region)
            ->willReturn($regionName);
        $company->expects($this->once())->method('getStreet')->willReturn(['Street', 'Address']);
        $company->expects($this->once())->method('getCity')->willReturn('City');
        $company->expects($this->atLeastOnce())->method('getCountryId')->willReturn($countryId);
        $company->expects($this->once())->method('getRegionId')->willReturn($regionId);
        $company->expects($this->once())->method('getRegion')->willReturn($region);
        $company->expects($this->once())->method('getPostcode')->willReturn('420420');
        $company->expects($this->once())->method('getTelephone')->willReturn('1234567');
        $addressInformation = [
            $this->createField(
                __('Street Address:'),
                'Street Address'
            ),
            $this->createField(
                __('City:'),
                'City'
            ),
            $this->createField(
                __('Country:'),
                $countryName
            ),
            $this->createField(
                __('State/Province:'),
                $regionName
            ),
            $this->createField(
                __('ZIP/Postal Code:'),
                '420420'
            ),
            $this->createField(
                __('Phone Number:'),
                '1234567'
            )
        ];

        $this->assertEquals($addressInformation, $this->profile->getAddressInformation());
    }

    /**
     * Test for method getAdminInformation.
     *
     * @return void
     */
    public function testGetAdminInformation()
    {
        $company = $this->getMockBuilder(\Magento\Company\Api\Data\CompanyInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $customerId = 1;
        $this->companyCreateSession->expects($this->once())->method('getCustomerId')->willReturn($customerId);
        $this->companyManagement->expects($this->once())
            ->method('getByCustomerId')
            ->with($customerId)
            ->willReturn($company);
        $admin = $this->getMockBuilder(\Magento\Customer\Api\Data\CustomerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $companyId = 1;
        $company->expects($this->once())->method('getId')->willReturn($companyId);
        $this->companyManagement->expects($this->once())->method('getAdminByCompanyId')
            ->with($companyId)->willReturn($admin);
        $companyAttributes = $this->getMockBuilder(\Magento\Company\Api\Data\CompanyCustomerInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCompanyAttributes'])
            ->getMockForAbstractClass();
        $admin->expects($this->once())->method('getExtensionAttributes')->willReturn($companyAttributes);
        $companyAttributes->expects($this->once())->method('getCompanyAttributes')->willReturn($companyAttributes);
        $companyAttributes->expects($this->once())->method('getJobTitle')->willReturn('Job Title');
        $admin->expects($this->once())->method('getEmail')->willReturn('mail@company.me');
        $admin->expects($this->once())->method('getPrefix')->willReturn('Prefix');
        $admin->expects($this->once())->method('getFirstname')->willReturn('First Name');
        $admin->expects($this->once())->method('getMiddlename')->willReturn('Middle Name');
        $admin->expects($this->once())->method('getLastname')->willReturn('Last Name');
        $admin->expects($this->once())->method('getSuffix')->willReturn('Suffix');
        $attributeMetadata = $this->getMockBuilder(\Magento\Customer\Api\Data\AttributeMetadataInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $gender = 'gender';
        $this->customerMetadata->expects($this->once())->method('getAttributeMetadata')
            ->with($gender)->willReturn($attributeMetadata);
        $attributeMetadata->expects($this->once())->method('getOptions')->willReturn([]);
        $adminInformation = [
            $this->createField(
                __('Job Title:'),
                'Job Title'
            ),
            $this->createField(
                __('Email Address:'),
                'mail@company.me'
            ),
            $this->createField(
                __('Prefix:'),
                'Prefix'
            ),
            $this->createField(
                __('First Name:'),
                'First Name'
            ),
            $this->createField(
                __('Middle Name/Initial:'),
                'Middle Name'
            ),
            $this->createField(
                __('Last Name:'),
                'Last Name'
            ),
            $this->createField(
                __('Suffix:'),
                'Suffix'
            ),
            $this->createField(
                __('Gender:'),
                null
            ),
        ];

        $this->assertEquals($adminInformation, $this->profile->getAdminInformation());
    }

    /**
     * Create field object.
     *
     * @param string $label
     * @param string|null $value
     * @return DataObject
     */
    private function createField($label, $value)
    {
        return new DataObject([
            'label' => $label,
            'value' => $value
        ]);
    }
}
