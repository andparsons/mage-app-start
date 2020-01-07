<?php
namespace Magento\NegotiableQuote\Block\Order\Info;

/**
 * Class CreationInfo
 *
 * @api
 * @since 100.0.0
 */
class CreationInfo extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var \Magento\Customer\Api\CustomerNameGenerationInterface
     */
    private $customerViewHelper;

    /**
     * @var string
     */
    private $orderId = 'order_id';

    /**
     * CreationInfo constructor.
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Magento\Customer\Api\CustomerNameGenerationInterface $customerViewHelper
     * @param array $data [optional]
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Customer\Api\CustomerNameGenerationInterface $customerViewHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->orderRepository = $orderRepository;
        $this->customerRepository = $customerRepository;
        $this->customerViewHelper = $customerViewHelper;
    }

    /**
     * Get creation info
     *
     * @return string
     */
    public function getCreationInfo()
    {
        $creationInfo = '';
        $orderId = (int)$this->getRequest()->getParam($this->orderId);

        if ($orderId) {
            $order = $this->orderRepository->get($orderId);

            if ($order && $order->getEntityId()) {
                $creationInfo = $this->getOrderCreatedAtInfo($order);
                $customerName = $this->getOrderCustomerName($order);

                if ($customerName) {
                    $creationInfo .= ' (' . $customerName . ')';
                }
            }
        }

        return $creationInfo;
    }

    /**
     * Get order customer name
     *
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @return string
     */
    private function getOrderCustomerName(\Magento\Sales\Api\Data\OrderInterface $order)
    {
        $customerName = '';
        $customerId = $order->getCustomerId();

        if ($customerId) {
            $customer = $this->customerRepository->getById($customerId);
            $customerName = $this->customerViewHelper->getCustomerName($customer);
        }

        return $customerName;
    }

    /**
     * Get order created at info
     *
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @return string
     */
    private function getOrderCreatedAtInfo(\Magento\Sales\Api\Data\OrderInterface $order)
    {
        return $this->formatDate($order->getCreatedAt(), \IntlDateFormatter::LONG);
    }
}
