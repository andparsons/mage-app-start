<?php

namespace Magento\NegotiableQuote\Plugin\Company\Controller\Adminhtml\Index;

use Magento\Company\Api\Data\CompanyInterface;
use Magento\Company\Model\Company\DataProvider;
use Magento\NegotiableQuote\Helper\Company as CompanyHelper;

/**
 * Class SavePlugin.
 */
class SavePlugin
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    /**
     * @var CompanyHelper
     */
    protected $companyHelper;

    /**
     * @param \Magento\Framework\App\RequestInterface $request
     * @param CompanyHelper $companyHelper
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        CompanyHelper $companyHelper
    ) {
        $this->request = $request;
        $this->companyHelper = $companyHelper;
    }

    /**
     * Around Save set company request data.
     *
     * @param \Magento\Company\Controller\Adminhtml\Index\Save $subject
     * @param CompanyInterface $company
     * @return CompanyInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterSetCompanyRequestData(
        \Magento\Company\Controller\Adminhtml\Index\Save $subject,
        CompanyInterface $company
    ) {
        $params = $this->request->getParams();

        if ($company && isset($params[DataProvider::DATA_SCOPE_SETTINGS]['is_quote_enabled'])) {
            $quoteConfig = $this->companyHelper->getQuoteConfig($company);
            $quoteConfig->setIsQuoteEnabled(filter_var(
                $params[DataProvider::DATA_SCOPE_SETTINGS]['is_quote_enabled'],
                FILTER_VALIDATE_BOOLEAN
            ));
        }

        return $company;
    }
}
