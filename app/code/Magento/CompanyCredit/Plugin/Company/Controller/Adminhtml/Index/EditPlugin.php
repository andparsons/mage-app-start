<?php

namespace Magento\CompanyCredit\Plugin\Company\Controller\Adminhtml\Index;

/**
 * Class adds notice message if company credit currency is not among websites' base currencies.
 */
class EditPlugin
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    /**
     * @var \Magento\CompanyCredit\Api\CreditLimitManagementInterface
     */
    private $creditLimitManagement;

    /**
     * @var \Magento\CompanyCredit\Model\WebsiteCurrency
     */
    private $websiteCurrency;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    private $messageManager;

    /**
     * @var \Magento\CompanyCredit\Api\Data\CreditLimitInterfaceFactory
     */
    private $creditLimitFactory;

    /**
     * EditPlugin constructor.
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\CompanyCredit\Api\CreditLimitManagementInterface $creditLimitManagement
     * @param \Magento\CompanyCredit\Model\WebsiteCurrency $websiteCurrency
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\CompanyCredit\Api\Data\CreditLimitInterfaceFactory $creditLimitFactory
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Magento\CompanyCredit\Api\CreditLimitManagementInterface $creditLimitManagement,
        \Magento\CompanyCredit\Model\WebsiteCurrency $websiteCurrency,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\CompanyCredit\Api\Data\CreditLimitInterfaceFactory $creditLimitFactory
    ) {
        $this->request = $request;
        $this->creditLimitManagement = $creditLimitManagement;
        $this->websiteCurrency = $websiteCurrency;
        $this->messageManager = $messageManager;
        $this->creditLimitFactory = $creditLimitFactory;
    }

    /**
     * Before execute.
     *
     * @param \Magento\Company\Controller\Adminhtml\Index\Edit $subject
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeExecute(\Magento\Company\Controller\Adminhtml\Index\Edit $subject)
    {
        $companyId = $this->request->getParam('id');

        if ($companyId) {
            /** @var \Magento\CompanyCredit\Api\Data\CreditLimitInterface $creditLimit */
            try {
                $creditLimit = $this->creditLimitManagement->getCreditByCompanyId($companyId);
            } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                $creditLimit = $this->creditLimitFactory->create();
                $creditLimit->setCompanyId($companyId);
            }
            $creditCurrencyCode = $creditLimit->getCurrencyCode();

            if ($creditCurrencyCode && !$this->websiteCurrency->isCreditCurrencyEnabled($creditCurrencyCode)) {
                $this->messageManager->addNoticeMessage(
                    __(
                        'The selected credit currency is not valid. 
                        Customers will not be able to place orders until you update the credit currency.'
                    )
                );
            }
        }

        return [];
    }
}
