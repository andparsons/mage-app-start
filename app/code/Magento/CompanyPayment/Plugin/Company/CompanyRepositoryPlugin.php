<?php

namespace Magento\CompanyPayment\Plugin\Company;

/**
 * Plugin for adding company extension attributes.
 */
class CompanyRepositoryPlugin
{
    /**
     * @var \Magento\CompanyPayment\Model\ResourceModel\CompanyPaymentMethod
     */
    private $companyPaymentMethodResource;

    /**
     * @var \Magento\CompanyPayment\Model\CompanyPaymentMethodFactory
     */
    private $companyPaymentMethodFactory;

    /**
     * @var \Magento\Company\Api\Data\CompanyExtensionFactory
     */
    private $companyExtensionFactory;

    /**
     * CompanyPaymentMethods constructor.
     *
     * @param \Magento\CompanyPayment\Model\ResourceModel\CompanyPaymentMethod $companyPaymentMethodResource
     * @param \Magento\CompanyPayment\Model\CompanyPaymentMethodFactory $companyPaymentMethodFactory
     * @param \Magento\Company\Api\Data\CompanyExtensionFactory $companyExtensionFactory
     */
    public function __construct(
        \Magento\CompanyPayment\Model\ResourceModel\CompanyPaymentMethod $companyPaymentMethodResource,
        \Magento\CompanyPayment\Model\CompanyPaymentMethodFactory $companyPaymentMethodFactory,
        \Magento\Company\Api\Data\CompanyExtensionFactory $companyExtensionFactory
    ) {
        $this->companyPaymentMethodResource = $companyPaymentMethodResource;
        $this->companyPaymentMethodFactory = $companyPaymentMethodFactory;
        $this->companyExtensionFactory = $companyExtensionFactory;
    }

    /**
     * After get company.
     *
     * @param \Magento\Company\Api\CompanyRepositoryInterface $subject
     * @param \Magento\Company\Api\Data\CompanyInterface $company
     * @return \Magento\Company\Api\Data\CompanyInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGet(
        \Magento\Company\Api\CompanyRepositoryInterface $subject,
        \Magento\Company\Api\Data\CompanyInterface $company
    ) {
        /** @var \Magento\CompanyPayment\Model\CompanyPaymentMethod $availablePaymentMethod */
        $availablePaymentMethod = $this->companyPaymentMethodFactory->create()->load($company->getId());

        if ($availablePaymentMethod->getId()) {
            /** @var \Magento\Company\Api\Data\CompanyExtensionInterface $companyExtension */
            $companyExtension = $company->getExtensionAttributes();
            if ($companyExtension === null) {
                $companyExtension = $this->companyExtensionFactory->create();
            }
            $companyExtension->setApplicablePaymentMethod($availablePaymentMethod->getApplicablePaymentMethod());
            $companyExtension->setAvailablePaymentMethods($availablePaymentMethod->getAvailablePaymentMethods());
            $companyExtension->setUseConfigSettings($availablePaymentMethod->getUseConfigSettings());
            $company->setExtensionAttributes($companyExtension);
        }

        return $company;
    }
}
