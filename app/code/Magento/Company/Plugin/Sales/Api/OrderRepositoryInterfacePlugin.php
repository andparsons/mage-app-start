<?php
namespace Magento\Company\Plugin\Sales\Api;

/**
 * Saving and adding to order company order extension attributes.
 */
class OrderRepositoryInterfacePlugin
{
    /**
     * @var \Magento\Company\Api\Data\CompanyOrderInterfaceFactory
     */
    private $companyOrderFactory;

    /**
     * @var \Magento\Sales\Api\Data\OrderExtensionFactory
     */
    private $orderExtensionAttributesFactory;

    /**
     * @var \Magento\Company\Model\ResourceModel\Order
     */
    private $companyOrderResource;

    /**
     * @param \Magento\Company\Api\Data\CompanyOrderInterfaceFactory $companyOrderFactory
     * @param \Magento\Sales\Api\Data\OrderExtensionFactory $orderExtensionAttributesFactory
     * @param \Magento\Company\Model\ResourceModel\Order $companyOrderResource
     */
    public function __construct(
        \Magento\Company\Api\Data\CompanyOrderInterfaceFactory $companyOrderFactory,
        \Magento\Sales\Api\Data\OrderExtensionFactory $orderExtensionAttributesFactory,
        \Magento\Company\Model\ResourceModel\Order $companyOrderResource
    ) {
        $this->companyOrderFactory = $companyOrderFactory;
        $this->orderExtensionAttributesFactory = $orderExtensionAttributesFactory;
        $this->companyOrderResource = $companyOrderResource;
    }

    /**
     * Adding company extension attributes to order after get order.
     *
     * @param \Magento\Sales\Api\OrderRepositoryInterface $subject
     * @param \Magento\Sales\Api\Data\OrderInterface $result
     * @return \Magento\Sales\Api\Data\OrderInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGet(
        \Magento\Sales\Api\OrderRepositoryInterface $subject,
        \Magento\Sales\Api\Data\OrderInterface $result
    ) {
        /** @var \Magento\Company\Api\Data\CompanyOrderInterface $companyOrder */
        $companyOrder = $this->companyOrderFactory->create();
        $this->companyOrderResource->load($companyOrder, $result->getId(), 'order_id');

        if ($companyOrder->getId()) {
            /** @var \Magento\Company\Api\Data\CompanyOrderInterface $companyOrderExtensionAttributes */
            $companyOrderExtensionAttributes = $this->companyOrderFactory->create();
            $companyOrderExtensionAttributes->setCompanyId($companyOrder->getCompanyId());
            $companyOrderExtensionAttributes->setCompanyName($companyOrder->getCompanyName());

            if (!$result->getExtensionAttributes()) {
                $orderExtensionAttributes = $this->orderExtensionAttributesFactory->create();
                $result->setExtensionAttributes($orderExtensionAttributes);
            }

            $result->getExtensionAttributes()->setCompanyOrderAttributes($companyOrderExtensionAttributes);
        }

        return $result;
    }
}
