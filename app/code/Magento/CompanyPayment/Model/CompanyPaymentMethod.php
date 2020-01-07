<?php

namespace Magento\CompanyPayment\Model;

/**
 * Class CompanyPaymentMethod.
 */
class CompanyPaymentMethod extends \Magento\Framework\Model\AbstractModel
{
    /**
     * {@inheritdoc}
     */
    protected $_idFieldName = 'company_id';

    /**
     * Applicable payment method type field name.
     *
     * @var string
     */
    private $applicablePaymentMethod = 'applicable_payment_method';

    /**
     * Available payment methods list field name.
     *
     * @var string
     */
    private $availablePaymentMethods = 'available_payment_methods';

    /**
     * Use config value field name.
     *
     * @var string
     */
    private $useConfigSettings = 'use_config_settings';

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init(\Magento\CompanyPayment\Model\ResourceModel\CompanyPaymentMethod::class);
    }

    /**
     * Set company ID.
     *
     * @param int $companyId
     * @return $this
     */
    public function setCompanyId($companyId)
    {
        return $this->setData($this->_idFieldName, $companyId);
    }

    /**
     * Get company ID.
     *
     * @return int
     */
    public function getCompanyId()
    {
        return $this->getData($this->_idFieldName);
    }

    /**
     * Set applicable payment method.
     *
     * @param int $paymentMethodType
     * @return $this
     */
    public function setApplicablePaymentMethod($paymentMethodType)
    {
        return $this->setData($this->applicablePaymentMethod, $paymentMethodType);
    }

    /**
     * Get applicable payment method.
     *
     * @return int
     */
    public function getApplicablePaymentMethod()
    {
        return $this->getData($this->applicablePaymentMethod);
    }

    /**
     * Set payment methods list.
     *
     * @param string $availablePaymentMethods
     * @return $this
     */
    public function setAvailablePaymentMethods($availablePaymentMethods)
    {
        return $this->setData($this->availablePaymentMethods, $availablePaymentMethods);
    }

    /**
     * Get payment methods list.
     *
     * @return string
     */
    public function getAvailablePaymentMethods()
    {
        return $this->getData($this->availablePaymentMethods);
    }

    /**
     * Set use config settings.
     *
     * @param bool $useConfigSettings
     * @return $this
     */
    public function setUseConfigSettings($useConfigSettings)
    {
        return $this->setData($this->useConfigSettings, $useConfigSettings);
    }

    /**
     * Get use config settings.
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getUseConfigValue()
    {
        return $this->getData($this->useConfigSettings);
    }

    /**
     * {@inheritdoc}
     */
    protected function _afterLoad()
    {
        parent::_afterLoad();
        if (!$this->getCompanyId()) {
            $this->isObjectNew(true);
        }
        return $this;
    }
}
