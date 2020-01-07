<?php
namespace Magento\Company\Block\Company;

/**
 * Class CompanyProfile
 *
 * @api
 * @since 100.0.0
 */
class CompanyProfile extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Authorization\Model\UserContextInterface
     */
    private $userContext;

    /**
     * @var \Magento\Company\Api\CompanyManagementInterface
     */
    private $companyManagement;

    /**
     * @var \Magento\Company\Model\CountryInformationProvider
     */
    private $countryInformationProvider;

    /**
     * @var \Magento\User\Model\UserFactory
     */
    private $userFactory;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    private $messageManager;

    /**
     * @var \Magento\Company\Api\Data\CompanyInterface
     */
    private $company = null;

    /**
     * @var \Magento\Customer\Api\CustomerNameGenerationInterface
     */
    private $customerViewHelper;

    /**
     * @var \Magento\Customer\Api\Data\CustomerInterface
     */
    private $companyAdmin;

    /**
     * @var \Magento\User\Api\Data\UserInterface
     */
    private $salesRepresentative;

    /**
     * @var \Magento\Company\Api\AuthorizationInterface
     */
    private $authorization;

    /**
     * CompanyProfile constructor.
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Authorization\Model\UserContextInterface $userContext
     * @param \Magento\Company\Api\CompanyManagementInterface $companyManagement
     * @param \Magento\Company\Model\CountryInformationProvider $countryInformationProvider
     * @param \Magento\User\Model\UserFactory $userFactory
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Customer\Api\CustomerNameGenerationInterface $customerViewHelper
     * @param \Magento\Company\Api\AuthorizationInterface $authorization
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Authorization\Model\UserContextInterface $userContext,
        \Magento\Company\Api\CompanyManagementInterface $companyManagement,
        \Magento\Company\Model\CountryInformationProvider $countryInformationProvider,
        \Magento\User\Model\UserFactory $userFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Customer\Api\CustomerNameGenerationInterface $customerViewHelper,
        \Magento\Company\Api\AuthorizationInterface $authorization,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->userContext = $userContext;
        $this->companyManagement = $companyManagement;
        $this->countryInformationProvider = $countryInformationProvider;
        $this->userFactory = $userFactory;
        $this->messageManager = $messageManager;
        $this->customerViewHelper = $customerViewHelper;
        $this->authorization = $authorization;
    }

    /**
     * Checks if account view is allowed.
     *
     * @return bool
     */
    public function isViewAccountAllowed()
    {
        return $this->authorization->isAllowed('Magento_Company::view_account');
    }

    /**
     * Checks if account edit is allowed.
     *
     * @return bool
     */
    public function isEditAccountAllowed()
    {
        return $this->authorization->isAllowed('Magento_Company::edit_account');
    }

    /**
     * Checks if address view is allowed.
     *
     * @return bool
     */
    public function isViewAddressAllowed()
    {
        return $this->authorization->isAllowed('Magento_Company::view_address');
    }

    /**
     * Checks if address edit is allowed.
     *
     * @return bool
     */
    public function isEditAddressAllowed()
    {
        return $this->authorization->isAllowed('Magento_Company::edit_address');
    }

    /**
     * Checks if contacts view is allowed.
     *
     * @return bool
     */
    public function isViewContactsAllowed()
    {
        return $this->authorization->isAllowed('Magento_Company::contacts');
    }

    /**
     * Get countries list
     *
     * @return array
     */
    public function getCountriesList()
    {
        return $this->countryInformationProvider->getCountriesList();
    }

    /**
     * Get form messages
     *
     * @return array
     */
    public function getFormMessages()
    {
        $messagesList = [];
        $messagesCollection = $this->messageManager->getMessages(true);

        if ($messagesCollection && $messagesCollection->getCount()) {
            $messages = $messagesCollection->getItems();
            foreach ($messages as $message) {
                $messagesList[] = $message->getText();
            }
        }

        return $messagesList;
    }

    /**
     * Is edit link displayed
     *
     * @return bool
     */
    public function isEditLinkDisplayed()
    {
        return $this->authorization->isAllowed('Magento_Company::edit_account')
            || $this->authorization->isAllowed('Magento_Company::edit_address');
    }

    /**
     * Get current customer's company
     *
     * @return \Magento\Company\Api\Data\CompanyInterface
     */
    public function getCustomerCompany()
    {
        if ($this->company !== null) {
            return $this->company;
        }

        $customerId = $this->userContext->getUserId();

        if ($customerId) {
            $this->company = $this->companyManagement->getByCustomerId($customerId);
        }

        return $this->company;
    }

    /**
     * Gets company street label
     *
     * @param \Magento\Company\Api\Data\CompanyInterface $company
     * @return string
     */
    public function getCompanyStreetLabel(\Magento\Company\Api\Data\CompanyInterface $company)
    {
        $streetLabel = '';
        $streetData = $company->getStreet();
        $streetLabel .= (!empty($streetData[0])) ? $streetData[0] : '';
        $streetLabel .= (!empty($streetData[1])) ? ' ' . $streetData[1] : '';

        return $streetLabel;
    }

    /**
     * Is company address displayed
     *
     * @param \Magento\Company\Api\Data\CompanyInterface $company
     * @return bool
     */
    public function isCompanyAddressDisplayed(\Magento\Company\Api\Data\CompanyInterface $company)
    {
        return $company->getCountryId() ? true : false;
    }

    /**
     * Get company address string
     *
     * @param \Magento\Company\Api\Data\CompanyInterface $company
     * @return string
     */
    public function getCompanyAddressString(\Magento\Company\Api\Data\CompanyInterface $company)
    {
        $addressParts = [];

        $addressParts[] = $company->getCity();
        $addressParts[] = $this->countryInformationProvider->getActualRegionName(
            $company->getCountryId(),
            $company->getRegionId(),
            $company->getRegion()
        );
        $addressParts[] = $company->getPostcode();

        return implode(', ', array_filter($addressParts));
    }

    /**
     * Get company country label
     *
     * @param \Magento\Company\Api\Data\CompanyInterface $company
     * @return string
     */
    public function getCompanyCountryLabel(\Magento\Company\Api\Data\CompanyInterface $company)
    {
        return $this->countryInformationProvider->getCountryNameByCode($company->getCountryId());
    }

    /**
     * Get company admin name
     *
     * @param \Magento\Company\Api\Data\CompanyInterface $company
     * @return string
     */
    public function getCompanyAdminName(\Magento\Company\Api\Data\CompanyInterface $company)
    {
        $companyAdmin = $this->getCompanyAdmin($company);

        return ($companyAdmin && $companyAdmin->getId())
            ? $this->customerViewHelper->getCustomerName($companyAdmin) : '';
    }

    /**
     * Get company admin job title
     *
     * @param \Magento\Company\Api\Data\CompanyInterface $company
     * @return string
     */
    public function getCompanyAdminJobTitle(\Magento\Company\Api\Data\CompanyInterface $company)
    {
        $jobTitle = '';
        $companyAdmin = $this->getCompanyAdmin($company);

        if ($companyAdmin && $companyAdmin->getId()) {
            $extensionAttributes = $companyAdmin->getExtensionAttributes()->getCompanyAttributes();

            if ($extensionAttributes) {
                $jobTitle = $extensionAttributes->getJobTitle();
            }
        }

        return $jobTitle;
    }

    /**
     * Get company admin email
     *
     * @param \Magento\Company\Api\Data\CompanyInterface $company
     * @return string
     */
    public function getCompanyAdminEmail(\Magento\Company\Api\Data\CompanyInterface $company)
    {
        $companyAdmin = $this->getCompanyAdmin($company);

        return ($companyAdmin && $companyAdmin->getId()) ? $companyAdmin->getEmail() : '';
    }

    /**
     * Get sales representative name
     *
     * @param \Magento\Company\Api\Data\CompanyInterface $company
     * @return string
     */
    public function getSalesRepresentativeName(\Magento\Company\Api\Data\CompanyInterface $company)
    {
        $salesRepresentative = $this->getSalesRepresentative($company);

        return ($salesRepresentative && $salesRepresentative->getId()) ? $salesRepresentative->getName() : '';
    }

    /**
     * Get sales representative email
     *
     * @param \Magento\Company\Api\Data\CompanyInterface $company
     * @return string
     */
    public function getSalesRepresentativeEmail(\Magento\Company\Api\Data\CompanyInterface $company)
    {
        $salesRepresentative = $this->getSalesRepresentative($company);

        return ($salesRepresentative && $salesRepresentative->getId()) ? $salesRepresentative->getEmail() : '';
    }

    /**
     * Get company admin
     *
     * @param \Magento\Company\Api\Data\CompanyInterface $company
     * @return \Magento\Customer\Api\Data\CustomerInterface
     */
    protected function getCompanyAdmin(\Magento\Company\Api\Data\CompanyInterface $company)
    {
        if ($this->companyAdmin === null) {
            $this->companyAdmin = $this->companyManagement->getAdminByCompanyId($company->getId());
        }

        return $this->companyAdmin;
    }

    /**
     * Get company sales representative
     *
     * @param \Magento\Company\Api\Data\CompanyInterface $company
     * @return \Magento\User\Model\User
     */
    private function getSalesRepresentative(\Magento\Company\Api\Data\CompanyInterface $company)
    {
        if ($this->salesRepresentative !== null) {
            return $this->salesRepresentative;
        }

        $salesRepresentativeId = $company->getSalesRepresentativeId();
        if ($salesRepresentativeId) {
            $this->salesRepresentative = $this->userFactory->create()->load($salesRepresentativeId);
        }

        return $this->salesRepresentative;
    }
}
