<?php
namespace Magento\Company\Block\Adminhtml\Sales\Order\View\Info\Creditmemo;

/**
 * Adminhtml creditmemo company information block.
 *
 * @api
 * @since 100.0.0
 */
class OrderCompanyInfo extends \Magento\Company\Block\Adminhtml\Sales\Order\View\Info\OrderCompanyInfo
{
    /**
     * @var \Magento\Sales\Api\CreditmemoRepositoryInterface
     */
    private $creditmemoRepository;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Magento\Sales\Api\CreditmemoRepositoryInterface $creditmemoRepository
     * @param array $data [optional]
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Sales\Api\CreditmemoRepositoryInterface $creditmemoRepository,
        array $data = []
    ) {
        parent::__construct($context, $orderRepository, $data);
        $this->creditmemoRepository = $creditmemoRepository;
    }

    /**
     * @inheritdoc
     */
    protected function getOrder()
    {
        if (!$this->order) {
            $creditmemoId = $this->getRequest()->getParam('creditmemo_id');
            $creditmemo = $this->creditmemoRepository->get($creditmemoId);
            $orderId = $creditmemo->getOrderId();
            $this->order = $this->orderRepository->get($orderId);
        }

        return $this->order;
    }
}
