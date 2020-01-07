<?php
namespace Magento\Company\Block\Company\Account;

use Magento\Store\Model\ScopeInterface;

/**
 * Class Create
 *
 * @api
 * @since 100.0.0
 */
class Create extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Company\Model\CountryInformationProvider
     */
    private $countryInformationProvider;

    /**
     * @var \Magento\Customer\Helper\Address
     */
    private $addressHelper;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Company\Model\CountryInformationProvider $countryInformationProvider
     * @param \Magento\Customer\Helper\Address $addressHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Company\Model\CountryInformationProvider $countryInformationProvider,
        \Magento\Customer\Helper\Address $addressHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->countryInformationProvider = $countryInformationProvider;
        $this->addressHelper = $addressHelper;
    }

    /**
     * Get config
     *
     * @param string $path
     * @return string|null
     */
    public function getConfig($path)
    {
        return $this->_scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * Retrieve form posting url
     *
     * @return string
     */
    public function getPostActionUrl()
    {
        return $this->getUrl('*/account/createPost');
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
     * Retrieve form data
     *
     * @return mixed
     */
    public function getFormData()
    {
        $data = $this->getData('form_data');
        if ($data === null) {
            $data = new \Magento\Framework\DataObject();
            $this->setData('form_data', $data);
        }
        return $data;
    }

    /**
     * Get default country id
     *
     * @return string
     */
    public function getDefaultCountryId()
    {
        return $this->_scopeConfig->getValue(
            'general/country/default',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get attribute validation class
     *
     * @param string $attributeCode
     * @return string
     */
    public function getAttributeValidationClass($attributeCode)
    {
        return $this->addressHelper->getAttributeValidationClass($attributeCode);
    }
}
