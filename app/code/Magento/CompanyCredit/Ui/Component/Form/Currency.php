<?php
namespace Magento\CompanyCredit\Ui\Component\Form;

use Magento\Ui\Component\Form\Field;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;

/**
 * Class for credit currency field.
 */
class Currency extends Field
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    /**
     * @var \Magento\CompanyCredit\Api\CreditLimitManagementInterface
     */
    private $creditLimitManagement;

    /**
     * @var \Magento\Framework\Locale\CurrencyInterface
     */
    private $localeCurrency;

    /**
     * Currency constructor.
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\CompanyCredit\Api\CreditLimitManagementInterface $creditLimitManagement
     * @param \Magento\Framework\Locale\CurrencyInterface $localeCurrency
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\CompanyCredit\Api\CreditLimitManagementInterface $creditLimitManagement,
        \Magento\Framework\Locale\CurrencyInterface $localeCurrency,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->storeManager = $storeManager;
        $this->request = $request;
        $this->creditLimitManagement = $creditLimitManagement;
        $this->localeCurrency = $localeCurrency;
    }

    /**
     * Prepare component configuration.
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function prepare()
    {
        parent::prepare();
        $config = $this->getData('config');
        $config['value'] = $this->storeManager->getWebsite()->getBaseCurrencyCode();

        $companyId = $this->request->getParam('id');

        if ($companyId) {
            $companyCreditCurrencyCode = $this->getCompanyCreditCurrencyCode($companyId);
            $configOptions = (!empty($config['options'])) ? $config['options'] : [];

            if (!$this->isOptionsContainCurrencyCode($configOptions, $companyCreditCurrencyCode)) {
                $currencyData = $this->getCurrencyData($companyCreditCurrencyCode);

                if ($currencyData) {
                    $config['options'][] = $currencyData;
                }
            }
        }

        $this->setData('config', $config);
    }

    /**
     * Get company credit currency code. For adding currency to options if is not among allowed currencies.
     *
     * @param int $companyId
     * @return string
     */
    private function getCompanyCreditCurrencyCode($companyId)
    {
        $companyCreditCurrencyCode = '';
        $creditLimit = $this->creditLimitManagement->getCreditByCompanyId($companyId);

        if ($creditLimit) {
            $companyCreditCurrencyCode = ($creditLimit->getCurrencyCode()) ?: '';
        }

        return $companyCreditCurrencyCode;
    }

    /**
     * Get currency data by currency code.
     *
     * @param string $currencyCode
     * @return array
     */
    private function getCurrencyData($currencyCode)
    {
        $currencyData = [];

        if ($currencyCode) {
            $currencyName = $this->localeCurrency->getCurrency($currencyCode)->getName();

            if ($currencyName) {
                $currencyData = [
                    'value' => $currencyCode,
                    'label' => $currencyName
                ];
            }
        }

        return $currencyData;
    }

    /**
     * Is config currency contain currency code.
     *
     * @param array $configOptions
     * @param string $currencyCode
     * @return bool
     */
    private function isOptionsContainCurrencyCode(array $configOptions, $currencyCode)
    {
        foreach ($configOptions as $option) {
            if (isset($option['value']) && $option['value'] == $currencyCode) {
                return true;
            }
        }

        return false;
    }
}
