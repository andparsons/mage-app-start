<?php
namespace Magento\Company\Block\Company\Register;

use Magento\Framework\DataObject;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Company\Api\Data\CompanyInterface;

/**
 * Block for profile on company register page.
 *
 * @api
 * @since 100.0.0
 */
class Profile extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Company\Api\CompanyManagementInterface
     */
    private $companyManagement;

    /**
     * @var \Magento\Company\Model\CountryInformationProvider
     */
    private $countryInformationProvider;

    /**
     * @var \Magento\Customer\Api\CustomerMetadataInterface
     */
    protected $customerMetadata;

    /**
     * @var \Magento\Company\Model\Create\Session
     */
    private $companyCreateSession;

    /**
     * @var \Magento\Company\Api\Data\CompanyInterface
     */
    private $company;

    /**
     * @var \Magento\Customer\Api\Data\CustomerInterface
     */
    private $companyAdmin;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Company\Api\CompanyManagementInterface $companyManagement
     * @param \Magento\Company\Model\CountryInformationProvider $countryInformationProvider
     * @param \Magento\Customer\Api\CustomerMetadataInterface $customerMetadata
     * @param \Magento\Company\Model\Create\Session $companyCreateSession
     * @param array $data [optional]
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Company\Api\CompanyManagementInterface $companyManagement,
        \Magento\Company\Model\CountryInformationProvider $countryInformationProvider,
        \Magento\Customer\Api\CustomerMetadataInterface $customerMetadata,
        \Magento\Company\Model\Create\Session $companyCreateSession,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->companyManagement = $companyManagement;
        $this->countryInformationProvider = $countryInformationProvider;
        $this->customerMetadata = $customerMetadata;
        $this->companyCreateSession = $companyCreateSession;
    }

    /**
     * Filter empty fields.
     *
     * @param DataObject[] $fields
     * @return DataObject[]
     */
    public function filterEmptyFields(array $fields)
    {
        return array_filter($fields, function ($field) {
            return !empty($field->getValue());
        });
    }

    /**
     * Get company information.
     *
     * @return DataObject[]
     */
    public function getCompanyInformation()
    {
        $company = $this->getCompany();

        $companyInformation = [
            $this->createField(
                __('Company Name:'),
                $company->getCompanyName()
            ),
            $this->createField(
                __('Company Legal Name:'),
                $company->getLegalName()
            ),
            $this->createField(
                __('Company Email:'),
                $company->getCompanyEmail()
            ),
            $this->createField(
                __('VAT/TAX ID:'),
                $company->getVatTaxId()
            ),
            $this->createField(
                __('Re-seller ID:'),
                $company->getResellerId()
            ),
        ];

        return $companyInformation;
    }

    /**
     * Get address information.
     *
     * @return DataObject[]
     */
    public function getAddressInformation()
    {
        $company = $this->getCompany();

        $addressInformation = [
            $this->createField(
                __('Street Address:'),
                $this->getCompanyStreetLabel($company)
            ),
            $this->createField(
                __('City:'),
                $company->getCity()
            ),
            $this->createField(
                __('Country:'),
                $this->countryInformationProvider->getCountryNameByCode($company->getCountryId())
            ),
            $this->createField(
                __('State/Province:'),
                $this->countryInformationProvider->getActualRegionName(
                    $company->getCountryId(),
                    $company->getRegionId(),
                    $company->getRegion()
                )
            ),
            $this->createField(
                __('ZIP/Postal Code:'),
                $company->getPostcode()
            ),
            $this->createField(
                __('Phone Number:'),
                $company->getTelephone()
            )
        ];

        return $addressInformation;
    }

    /**
     * Get admin information.
     *
     * @return DataObject[]
     */
    public function getAdminInformation()
    {
        $companyAdmin = $this->getCompanyAdmin();

        $adminInformation = [
            $this->createField(
                __('Job Title:'),
                $this->getCustomerJobTitle($companyAdmin)
            ),
            $this->createField(
                __('Email Address:'),
                $companyAdmin->getEmail()
            ),
            $this->createField(
                __('Prefix:'),
                $companyAdmin->getPrefix()
            ),
            $this->createField(
                __('First Name:'),
                $companyAdmin->getFirstname()
            ),
            $this->createField(
                __('Middle Name/Initial:'),
                $companyAdmin->getMiddlename()
            ),
            $this->createField(
                __('Last Name:'),
                $companyAdmin->getLastname()
            ),
            $this->createField(
                __('Suffix:'),
                $companyAdmin->getSuffix()
            ),
            $this->createField(
                __('Gender:'),
                $this->getCustomerGender($companyAdmin)
            ),
        ];

        return $adminInformation;
    }

    /**
     * Create field object.
     *
     * @param string|\Magento\Framework\Phrase $label
     * @param string $value
     * @return DataObject
     */
    private function createField($label, $value)
    {
        return new DataObject([
            'label' => $label,
            'value' => $value
        ]);
    }

    /**
     * Get company.
     *
     * @return CompanyInterface
     */
    private function getCompany()
    {
        if ($this->company !== null) {
            return $this->company;
        }

        $customerId = $this->companyCreateSession->getCustomerId();
        if ($customerId) {
            $this->company = $this->companyManagement->getByCustomerId($customerId);
        }

        return $this->company;
    }

    /**
     * Get company admin.
     *
     * @return CustomerInterface
     */
    private function getCompanyAdmin()
    {
        if ($this->companyAdmin === null) {
            $company = $this->getCompany();
            $this->companyAdmin = $this->companyManagement->getAdminByCompanyId($company->getId());
        }

        return $this->companyAdmin;
    }

    /**
     * Get customer job title.
     *
     * @param CustomerInterface $customer
     * @return null|string
     */
    private function getCustomerJobTitle(CustomerInterface $customer)
    {
        $jobTitle = '';
        $extensionAttributes = $customer->getExtensionAttributes()->getCompanyAttributes();
        if ($extensionAttributes) {
            $jobTitle = $extensionAttributes->getJobTitle();
        }

        return $jobTitle;
    }

    /**
     * Get company street label.
     *
     * @param CompanyInterface $company
     * @return string
     */
    private function getCompanyStreetLabel(CompanyInterface $company)
    {
        $streetLabel = '';
        $streetData = $company->getStreet();
        $streetLabel .= (!empty($streetData[0])) ? $streetData[0] : '';
        $streetLabel .= (!empty($streetData[1])) ? ' ' . $streetData[1] : '';

        return $streetLabel;
    }

    /**
     * Get customer gender.
     *
     * @param CustomerInterface $customer
     * @return \Magento\Customer\Api\Data\OptionInterface|null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getCustomerGender(CustomerInterface $customer)
    {
        try {
            $attribute = $this->customerMetadata->getAttributeMetadata(CustomerInterface::GENDER);
            $options = $attribute->getOptions();
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            $options = [];
        }

        return isset($options[$customer->getGender()]) ? $options[$customer->getGender()]->getLabel() : null;
    }
}
