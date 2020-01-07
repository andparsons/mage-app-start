<?php
namespace Magento\Company\Block\Adminhtml\Sales\Order\View\Info\Shipment;

/**
 * Adminhtml shipment company information block.
 *
 * @api
 * @since 100.0.0
 */
class OrderCompanyInfo extends \Magento\Company\Block\Adminhtml\Sales\Order\View\Info\OrderCompanyInfo
{
    /**
     * @var \Magento\Sales\Api\ShipmentRepositoryInterface
     */
    private $shipmentRepository;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Magento\Sales\Api\ShipmentRepositoryInterface $shipmentRepository
     * @param array $data [optional]
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Sales\Api\ShipmentRepositoryInterface $shipmentRepository,
        array $data = []
    ) {
        parent::__construct($context, $orderRepository, $data);
        $this->shipmentRepository = $shipmentRepository;
    }

    /**
     * @inheritdoc
     */
    protected function getOrder()
    {
        if (!$this->order) {
            $shipmentId = $this->getRequest()->getParam('shipment_id');
            $shipment = $this->shipmentRepository->get($shipmentId);
            $orderId = $shipment->getOrderId();
            $this->order = $this->orderRepository->get($orderId);
        }

        return $this->order;
    }
}
