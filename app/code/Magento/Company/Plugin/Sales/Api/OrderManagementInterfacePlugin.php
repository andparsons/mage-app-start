<?php
namespace Magento\Company\Plugin\Sales\Api;

/**
 * Set company extension attributes to order before placing it.
 */
class OrderManagementInterfacePlugin
{
    /**
     * @var \Magento\Company\Api\CompanyManagementInterface
     */
    private $companyManagement;

    /**
     * @var \Magento\Company\Api\Data\CompanyOrderInterfaceFactory
     */
    private $companyOrderFactory;

    /**
     * @var \Magento\Sales\Api\Data\OrderExtensionFactory
     */
    private $orderExtensionAttributesFactory;

    /**
     * @param \Magento\Company\Api\CompanyManagementInterface $companyManagement
     * @param \Magento\Company\Api\Data\CompanyOrderInterfaceFactory $companyOrderFactory
     * @param \Magento\Sales\Api\Data\OrderExtensionFactory $orderExtensionAttributesFactory
     */
    public function __construct(
        \Magento\Company\Api\CompanyManagementInterface $companyManagement,
        \Magento\Company\Api\Data\CompanyOrderInterfaceFactory $companyOrderFactory,
        \Magento\Sales\Api\Data\OrderExtensionFactory $orderExtensionAttributesFactory
    ) {
        $this->companyManagement = $companyManagement;
        $this->companyOrderFactory = $companyOrderFactory;
        $this->orderExtensionAttributesFactory = $orderExtensionAttributesFactory;
    }

    /**
     * Set company extension attributes to order before placing it.
     * That attributes will be saved later together with order.
     *
     * @param \Magento\Sales\Api\OrderManagementInterface $orderManagement
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @return array
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforePlace(
        \Magento\Sales\Api\OrderManagementInterface $orderManagement,
        \Magento\Sales\Api\Data\OrderInterface $order
    ) {
        $customerId = $order->getCustomerId();
        if (!empty($customerId)) {
            $company = $this->companyManagement->getByCustomerId($customerId);
            if ($company) {
                /** @var \Magento\Company\Api\Data\CompanyOrderInterface $companyOrder */
                $companyOrderExtensionAttributes = $this->companyOrderFactory->create();
                $companyOrderExtensionAttributes->setCompanyId($company->getId());
                $companyOrderExtensionAttributes->setCompanyName($company->getCompanyName());

                $orderExtension = $order->getExtensionAttributes();
                if (!$orderExtension) {
                    $orderExtensionAttributes = $this->orderExtensionAttributesFactory->create();
                    $order->setExtensionAttributes($orderExtensionAttributes);
                }

                $order->getExtensionAttributes()->setCompanyOrderAttributes($companyOrderExtensionAttributes);
            }
        }

        return [$order];
    }

    /**
     * Save company order extension attributes after order place.
     *
     * @param \Magento\Sales\Api\OrderManagementInterface $subject
     * @param \Magento\Sales\Api\Data\OrderInterface $result
     * @return \Magento\Sales\Api\Data\OrderInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterPlace(
        \Magento\Sales\Api\OrderManagementInterface $subject,
        \Magento\Sales\Api\Data\OrderInterface $result
    ) {
        if ($result->getExtensionAttributes() !== null
            && $result->getExtensionAttributes()->getCompanyOrderAttributes() !== null
        ) {
            $companyAttributes = $result->getExtensionAttributes()->getCompanyOrderAttributes();
            $companyAttributes->setOrderId($result->getEntityId());
            $companyAttributes->save();
        }

        return $result;
    }
}
