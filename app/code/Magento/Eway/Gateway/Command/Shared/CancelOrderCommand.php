<?php
namespace Magento\Eway\Gateway\Command\Shared;

use Magento\Sales\Model\Order;
use Magento\Checkout\Model\Session;
use Magento\Sales\Model\Order\Payment;
use Magento\Payment\Gateway\CommandInterface;
use Magento\Sales\Api\OrderManagementInterface;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Command\ResultInterface;
use Magento\Payment\Gateway\Command\Result\BoolResultFactory;

/**
 * Class CancelOrderCommand
 *
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 * @deprecated 100.3.3 Starting from Magento 2.3.3 eWay payment method core integration is deprecated
 *      in favor of official payment integration available on the marketplace
 */
class CancelOrderCommand implements CommandInterface
{
    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * @var OrderManagementInterface
     */
    private $orderManagement;

    /**
     * @var BoolResultFactory
     */
    private $resultFactory;

    /**
     * @param OrderManagementInterface $orderManagement
     * @param Session $checkoutSession
     * @param BoolResultFactory $resultFactory
     */
    public function __construct(
        OrderManagementInterface $orderManagement,
        Session $checkoutSession,
        BoolResultFactory $resultFactory
    ) {
        $this->orderManagement = $orderManagement;
        $this->checkoutSession = $checkoutSession;
        $this->resultFactory = $resultFactory;
    }

    /**
     * Executes command basing on business object
     *
     * @param array $commandSubject
     * @return ResultInterface
     * @throws \InvalidArgumentException
     * @throws \LogicException
     */
    public function execute(array $commandSubject)
    {
        $paymentDO = SubjectReader::readPayment($commandSubject);

        $this->orderManagement->cancel($paymentDO->getOrder()->getId());

        return $this->resultFactory->create(['result' => $this->checkoutSession->restoreQuote()]);
    }
}
