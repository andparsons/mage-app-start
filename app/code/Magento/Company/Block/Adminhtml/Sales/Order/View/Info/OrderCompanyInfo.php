<?php
namespace Magento\Company\Block\Adminhtml\Sales\Order\View\Info;

/**
 * Adminhtml order company information block.
 *
 * @api
 * @since 100.0.0
 */
class OrderCompanyInfo extends \Magento\Backend\Block\Template
{
    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var \Magento\Sales\Api\Data\OrderInterface
     */
    protected $order;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param array $data [optional]
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->orderRepository = $orderRepository;
    }

    /**
     * Get order from request.
     *
     * @return \Magento\Sales\Api\Data\OrderInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function getOrder()
    {
        if (!$this->order) {
            $orderId = $this->getRequest()->getParam('order_id');
            $this->order = $this->orderRepository->get($orderId);
        }

        return $this->order;
    }

    /**
     * Determines can block be displayed or not.
     *
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function canShow()
    {
        return $this->getCompanyId() !== null;
    }

    /**
     * Get company name.
     *
     * @return string|null
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCompanyName()
    {
        $companyName = null;
        $order = $this->getOrder();
        if ($order->getExtensionAttributes() !== null
            && $order->getExtensionAttributes()->getCompanyOrderAttributes() !== null
        ) {
            $companyName = $order->getExtensionAttributes()->getCompanyOrderAttributes()->getCompanyName();
        }

        return $companyName;
    }

    /**
     * Get company edit URL.
     *
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCompanyUrl()
    {
        return $this->getUrl(
            'company/index/edit',
            ['_secure' => true, 'id' => $this->getCompanyId()]
        );
    }

    /**
     * Get order company id.
     *
     * @return int|null
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getCompanyId()
    {
        $companyId = null;
        $order = $this->getOrder();
        if ($order->getExtensionAttributes() !== null
            && $order->getExtensionAttributes()->getCompanyOrderAttributes() !== null
        ) {
            $companyId = $order->getExtensionAttributes()->getCompanyOrderAttributes()->getCompanyId();
        }

        return $companyId;
    }
}
