<?php
namespace Magento\Company\Block\Adminhtml\Sales\Order\View\Info\Invoice;

/**
 * Adminhtml invoice company information block.
 *
 * @api
 * @since 100.0.0
 */
class OrderCompanyInfo extends \Magento\Company\Block\Adminhtml\Sales\Order\View\Info\OrderCompanyInfo
{
    /**
     * @var \Magento\Sales\Api\InvoiceRepositoryInterface
     */
    private $invoiceRepository;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Magento\Sales\Api\InvoiceRepositoryInterface $invoiceRepository
     * @param array $data [optional]
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Sales\Api\InvoiceRepositoryInterface $invoiceRepository,
        array $data = []
    ) {
        parent::__construct($context, $orderRepository, $data);
        $this->invoiceRepository = $invoiceRepository;
    }

    /**
     * @inheritdoc
     */
    protected function getOrder()
    {
        if (!$this->order) {
            $invoiceId = $this->getRequest()->getParam('invoice_id');
            $invoice = $this->invoiceRepository->get($invoiceId);
            $orderId = $invoice->getOrderId();
            $this->order = $this->orderRepository->get($orderId);
        }

        return $this->order;
    }
}
